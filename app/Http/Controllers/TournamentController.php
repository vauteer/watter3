<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Http\Resources\FixtureResource;
use App\Http\Resources\TournamentResource;
use App\Models\Fixture;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Rules\Score;
use App\Rules\UniquePlayer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class TournamentController extends Controller
{
    protected const URL_KEY = 'lastTournamentsUrl';

    public function validationRules(): array
    {
        return  [
            'name' => 'required|string|max:100',
            'start' => 'required|date',
            'rounds' => 'int|min:2|max:9',
            'games' => 'int|min:2|max:9',
            'winpoints' => 'int|min:11|max:21',
            'published' => 'boolean',
        ];
    }

    public function playersValidationRules($tournament): array
    {
        return  [
            'player1' => ['required', 'string', 'max:100', new UniquePlayer($tournament->id)],
            'player2' => ['nullable', 'string', 'max:100', new UniquePlayer($tournament->id)]
        ];
    }

    private function applyFilters(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Tournament::query();
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            if (preg_match('/^createdBy_(\d+)$/', $filter, $match)) {
                $query->createdBy($match[1]);
            }
            else if (preg_match('/^playedBy_(\d+)$/', $filter, $match)) {
                $query->playedBy($match[1]);
            }
        }
        return $query;
    }

    public function index(Request $request):Response
    {
        $request->session()->put(self::URL_KEY, url()->full());

        return inertia('Tournaments/Index', [
            'tournaments' => TournamentResource::collection($this->applyFilters($request)
                ->whereIn('id', Tournament::visibleIds(auth()->user()))
                ->when($request->input('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('start', 'desc')
                ->paginate(10)
                ->withQueryString()
            ),

            'filters' => $request->only(['search']),

            'canCreate' => Auth::check(),
        ]);
    }

    public function show(Request $request, Tournament $tournament):Response
    {
        $round = ($request->has('round')) ? intval($request->input('round')) : $tournament->rounds;

        return inertia('Tournaments/Show', [
            'tournament' => $tournament,
            'currentRound' => $round,
            'standings' => $tournament->standings(),
            'fixtures' => FixtureResource::collection($tournament->fixtures()
                ->where('round', $round)
                ->orderBy('table_number')
                ->get()),

            'canCreate' => Auth::check(),
            'canEdit' => $tournament->modifiableBy(auth()->user()) &&
                ($tournament->start > Carbon::yesterday() || !$tournament->finished()),
        ]);
    }

    private function editOptions(): array
    {
        return [
            'origin' => session(self::URL_KEY),
        ];
    }

    public function create(Request $request):Response
    {
        return inertia('Tournaments/Edit', $this->editOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        $tournament = Tournament::create(array_merge($attributes, [
            'created_by' => auth()->id(),
        ]));

        Team::deleteUnused();
        Player::deleteUnused();

        return redirect(session(self::URL_KEY))
            ->with('success', "{$tournament->name} wurde hinzugefügt.");
    }

    public function edit(Request $request, Tournament $tournament): Response
    {
        return inertia('Tournaments/Edit', array_merge($this->editOptions(), [
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
//                'start' => substr($tournament->start->toDateTimeLocalString(), 0, 19),
                'start' => $tournament->start->format('Y-m-d\TH:i'),
                'rounds' => $tournament->rounds,
                'games' => $tournament->games,
                'winpoints' => $tournament->winpoints,
                'private' => $tournament->private,
                'drawn' => $tournament->drawn(),
            ],
        ]));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        $tournament->update($attributes);

        return redirect(session(self::URL_KEY))
            ->with('success', "{$tournament->name} wurde geändert.");
    }

    public function destroy(Request $request, Tournament $tournament): RedirectResponse
    {
        Backup::create();

        $tournament->delete();

        return redirect(session(self::URL_KEY))
            ->with('success', 'Turnier wurde gelöscht.');
    }

    public function createPlayers(Request $request, Tournament $tournament): Response
    {
        return inertia('Tournaments/Players', array_merge($this->editOptions(), [
            'tournamentId' => $tournament->id,
            'players' => $tournament->players()->get(['players.id', 'name']),
            'teams' => $tournament->teamsAsArray(),
            'playerCount' => $tournament->currentPlayerCount(),
        ]));
    }

    public function storePlayers(Request $request, Tournament $tournament): Response
    {
        $attributes = $request->validate($this->playersValidationRules($tournament));

        $this->attachPlayers($tournament, $attributes);

        return $this->createPlayers($request, $tournament);
    }

    private function attachPlayers(Tournament $tournament, array $attributes)
    {
        $player1 = Player::firstOrCreate(['name' => $attributes['player1']]);

        if ($attributes['player2']) {
            $player2 = Player::firstOrCreate(['name' => $attributes['player2']]);

            $team = Team::firstOrCreate([
                'player1_id' => $player1->id,
                'player2_id' => $player2->id,
            ]);

            $tournament->teams()->syncWithoutDetaching([$team->id]);
        } else {
            $tournament->players()->syncWithoutDetaching([$player1->id]);
        }
    }

    public function detachPlayer(Request $request, Tournament $tournament, Player $player): Response
    {
        $tournament->players()->detach([$player->id]);

        return $this->createPlayers($request, $tournament);
    }

    public function detachTeam(Request $request, Tournament $tournament, Team $team): Response
    {
        $tournament->teams()->detach([$team->id]);
        $tournament->players()->detach([$team->player1_id, $team->player2_id]);

        return $this->createPlayers($request, $tournament);
    }

    public function connectPlayers(Request $request, Tournament $tournament): Response
    {
        $players = $request->input('checkedPlayers');

        $player1_id = $players[0];
        $player2_id = $players[1];

        $tournament->players()->detach([$player1_id, $player2_id]);

        $team = Team::firstOrCreate([
            'player1_id' => $player1_id,
            'player2_id' => $player2_id,
        ]);

        $tournament->teams()->attach($team->id);

        return $this->createPlayers($request, $tournament);
    }

    public function draw(Request $request, Tournament $tournament): RedirectResponse
    {
        $tournament->draw();

        return redirect()->route('tournaments.show', [
            'tournament' => $tournament->id,
            'round' => 1
        ]);
    }

    public function editFixture(Request $request, Fixture $fixture): Response
    {
        $tournament = $fixture->tournament;
        $winpoints = $tournament->winpoints;

        return inertia('Fixtures/Edit', [
            'fixture' => [
                'id' => $fixture->id,
                'tournament_id' => $tournament->id,
                'score' => $fixture->score,
                'round' => $fixture->round,
                'team1' => $fixture->team1->__toString(),
                'team2' => $fixture->team2->__toString(),
            ],
            'placeholder' => $this->getPlaceholder($tournament),
            'scorePattern' => $tournament->getScoreRegex(),
        ]);
    }

    /**
     * @throws \Exception
     */
    private function getPlaceholder($tournament)
    {
        $winPoints = $tournament->winpoints;
        $result = 'z.B.: ';
        for ($i = 0; $i < $tournament->games; $i++) {
            $points1 = $i % 2 ? $winPoints : random_int(2, $winPoints - 1);
            $points2 = $i % 2 ? random_int(2, $winPoints - 1) : $winPoints;

            $result .= "$points1-$points2 ";
        }

        return rtrim($result);
    }
    public function updateFixture(Request $request, Fixture $fixture): RedirectResponse
    {
        $attributes = $request->validate([
            'score' => new Score($fixture),
        ]);

        $fixture->calculate($attributes['score'], true);
        //$fixture->update($attributes);

        if ($fixture->tournament->finished())
            Backup::create();

        return redirect()
            ->route('tournaments.show', ['tournament' => $fixture->tournament_id, 'round' => $fixture->round])
            ->with('success', "Ergebnis {$fixture->team1->__toString()} gegen {$fixture->team2->__toString()} wurde geändert.");
    }

    public function tableLists(Request $request, Tournament $tournament, $round)
    {
        return (new \Illuminate\Http\Response($tournament->tableLists($round)->Output(), 200))
            ->header('Content-Type', 'application/pdf');
    }
}
