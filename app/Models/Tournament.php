<?php

namespace App\Models;

use Carbon\Carbon;
use Fpdf\Fpdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Tournament extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'start' => 'datetime',
        'private' => 'boolean',
    ];

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)
            ->withTimestamps();
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withTimestamps();
    }

    public function fixtures(): HasMany
    {
        return $this->HasMany(Fixture::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function currentPlayerCount()
    {
        return $this->teams()->count() * 2 + $this->players()->count();
    }

    public static function test()
    {
        $tournaments = Tournament::all();
        return $tournaments->filter(function ($item, $key) {
            return !$item->finished();
        })->pluck(['id'])->toArray();
    }

    public function drawn()
    {
        return $this->fixtures()->count() > 0;
    }

    public function started()
    {
        return $this->drawn() && $this->fixtures()->whereNotNull('score')->count() > 0;
    }

    public function finished()
    {
        Fixture::where('tournament_id', $this->id)
            ->where('score', '')
            ->update(['score' => null]);

        return $this->drawn() && $this->fixtures()->whereNull('score')->count() === 0;
    }

    public function playersAsArray(): Collection
    {
        return $this->players()
            ->get(['player_id', 'name'])
            ->mapWithKeys(function ($player) {
                return [$player->player_id => [
                    'name' => $player->name,
                ]
                ];
            });
    }

    public function teamsAsArray()
    {
        return $this->teams()
            ->get(['team_id', 'player1_id', 'player2_id'])
            ->map(function ($team) {
                return [
                    'id' => $team->team_id,
                    'player1' => $team->player1->name,
                    'player2' => $team->player2->name,

                ];
            });
    }

    public function draw()
    {
        $teamsCount = $this->teams()->count();
        if ($teamsCount % 2 !== 0)
            return;

        $tableCount = $teamsCount / 2;
        $this->fixtures()->delete();

        $randomTeams = $this->teams()->inRandomOrder()->pluck('teams.id')->toArray();
        list($home, $away) = array_chunk($randomTeams, $tableCount);

        for ($round = 0; $round < $this->rounds; $round++) {
            for ($i = 0; $i < $tableCount; $i++) {
                $this->fixtures()->create([
                    'team1_id' => $home[$i],
                    'team2_id' => $away[$i],
                    'round' => $round + 1,
                    'table_number' => $i + 1,
                ]);
            }

            array_unshift($away, array_pop($away)); // rotate right/clockwise
            // $away[] = array_shift($away); //rotate left/counterclockwise
        }
    }

    public function standings(?int $tillRound = null)
    {
        if ($tillRound === null)
            $tillRound = $this->rounds;

        $standings = [];
        $teams = $this->teams;
        if (count($teams) === 0)
            return $standings;

        foreach ($teams as $team) {
            $standings[$team->id] = [
                'id' => $team->id,
                'player1' => $team->player1->name,
                'player2' => $team->player2->name,
                'won' => 0,
                'lost' => 0,
                'pointsWon' => 0,
                'pointsLost' => 0,
            ];
        }

        $fixtures = $this->fixtures()->where('round', '<=', $tillRound)->get();

        foreach ($fixtures as $fixture) {
            $standings[$fixture->team1_id]['won'] += $fixture->team1_won;
            $standings[$fixture->team1_id]['lost'] += $fixture->team2_won;
            $standings[$fixture->team1_id]['pointsWon'] += $fixture->team1_points;
            $standings[$fixture->team1_id]['pointsLost'] += $fixture->team2_points;

            $standings[$fixture->team2_id]['won'] += $fixture->team2_won;
            $standings[$fixture->team2_id]['lost'] += $fixture->team1_won;
            $standings[$fixture->team2_id]['pointsWon'] += $fixture->team2_points;
            $standings[$fixture->team2_id]['pointsLost'] += $fixture->team1_points;
        }

        foreach ($standings as $id => $ranking) {
            $games[$id] = $ranking['won'];
            $pointsDifference[$id] = $ranking['pointsWon'] - $ranking['pointsLost'];
            $pointsWon[$id] = $ranking['pointsWon'];
        }

        array_multisort($games, SORT_DESC, $pointsDifference, SORT_DESC, $pointsWon, SORT_DESC, $standings);

        return $standings;
    }

    public function tableLists($round)
    {
        $gamesPerRound = $this->games;

        $fixtures = $this->fixtures()->where('round', $round)
            ->orderBy('table_number')
            ->get();

        $pdf = new FPDF('L', 'mm', 'A4');

        foreach ($fixtures as $fixture) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(120, 10, 'Runde ' . $fixture->round . ' - Tisch ' . $fixture->table_number, 0, 0);
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(60, 7, 'Schreiber:');
            $pdf->Line(40, 27, 120, 27);
            $pdf->Ln(10);
            $pdf->Cell(60, 7, mb_convert_encoding($fixture->team1->player1->name, 'ISO-8859-1', 'UTF-8'));
            $pdf->Cell(60, 7, mb_convert_encoding($fixture->team2->player1->name, 'ISO-8859-1', 'UTF-8'));
            $pdf->Ln(7);
            $pdf->Cell(60, 7, mb_convert_encoding($fixture->team1->player2->name, 'ISO-8859-1', 'UTF-8'));
            $pdf->Cell(60, 7, mb_convert_encoding($fixture->team2->player2->name, 'ISO-8859-1', 'UTF-8'));

//            $pdf->Line(0, 20, 120, 20);
            $pdf->Line(60, 30, 60, 50 + ($gamesPerRound * 20));
            $pdf->Line(0, 50, 120, 50);

            for ($i = 1; $i <= $gamesPerRound; $i++) {
                $y = 50 + ($i * 20);
                $pdf->Line(0, $y, 120, $y);
            }

            $pdf->Ln(15 + (20 * $gamesPerRound));
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(60, 6, 'LS-Watter 3');
            $pdf->Cell(60, 6, 'http://watter.it-ruler.de');
            $pdf->Ln(6);
            $pdf->Cell(120, 6, chr(169) . ' 2016 Gerald Lindner');
        }

        return $pdf;
    }

    public function getScoreRegex()
    {
        return '^(' . Fixture::SCORE_REGEX . ' ){' . $this->games - 1 . '}' . Fixture::SCORE_REGEX . '$';
    }

    public function modifiableBy(User|null $user)
    {
        return $user !== null && ($user->admin || $user->id === $this->created_by);
    }

    public function canShow(User|null $user)
    {
        if ($user === null) {
            return $this->start < Carbon::now();
        } else {
            return $this->admin || ($this->start < Carbon::now() || $this->created_by === $user->id);
        }
    }

    public function scopeVisibleTo($query, User|null $user)
    {
        if ($user?->admin)
            return;

        $query->where('start', '<', Carbon::now())
            ->where('private', false);

        if ($user !== null)
            $query->orWhere('created_by', $user->id);
    }


    public function scopeCreatedBy($query, int $userId)
    {
        $query->where('created_by', $userId);
    }

    public function scopePlayedBy($query, int $playerId)
    {
        $query->whereIn('id', DB::table('tournaments')
            ->join('team_tournament', 'tournaments.id', '=',
                'team_tournament.tournament_id')
            ->join('teams', 'teams.id', '=', 'team_tournament.team_id')
            ->where('teams.player1_id', $playerId)
            ->orWhere('teams.player2_id', $playerId)
            ->pluck('tournaments.id'));
    }

}
