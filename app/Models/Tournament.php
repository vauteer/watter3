<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Tournament extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'start' => 'datetime',
        'published' => 'boolean',
        'finished' => 'boolean',
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
//    public function playerKeys(): array
//    {
//        $result = $this->players()
//            ->get(['players.id'])
//            ->map(function ($player) {
//                return $player->id;
//            });
//
//        return $result->toArray();
//    }

//    public function teamPlayerKeys(): array
//    {
//        $result = $this->teams()
//            ->get(['player1_id', 'player2_id'])
//            ->map(function ($team) {
//                return [ $team->player1_id, $team->player2_id];
//            });
//
//        return Arr::flatten($result);
//    }

//    public function singlePlayerKeys(): array
//    {
//        $result = array_diff($this->playerKeys(), $this->teamPlayerKeys());
//
//        return Arr::flatten($result);
//    }

//    public function singlePlayers()
//    {
//        return $this->players()->whereIn('players.id', $this->singlePlayerKeys());
//    }

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

    public function teamsAsArray(): Collection
    {
        return $this->teams()
            ->get(['team_id', 'player1_id', 'player2_id'])
            ->mapWithKeys(function ($team) {
                return [$team->team_id => [
                    'id' => $team->team_id,
                    'player1' => $team->player1->name,
                    'player2' => $team->player2->name,
                    ]
                ];
            });
    }

    public function draw()
    {
        $teamsCount = $this->teams()->count();
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
                    'table' => $i + 1,
                ]);
            }

            array_unshift($away, array_pop($away)); // rotate right/clockwise
        }
    }
}
