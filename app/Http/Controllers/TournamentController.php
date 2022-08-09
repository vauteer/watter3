<?php

namespace App\Http\Controllers;

use App\Http\Resources\FixtureResource;
use App\Http\Resources\TournamentResource;
use App\Models\Fixture;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Rules\Score;
use App\Rules\UniquePlayer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class TournamentController extends Controller
{
    public function validationRules(): array
    {
        return  [
            'name' => 'required|string|max:100',
            'start' => 'required|date',
            'rounds' => 'int|min:2|max:9',
            'games' => 'int|min:2|max:9',
            'winpoints' => 'int|min:11|max:21',
            'published' => 'boolean',
            'finished' => 'boolean',
        ];
    }

    public function playersValidationRules($tournament): array
    {
        return  [
            'player1' => ['required', 'string', 'max:100', new UniquePlayer($tournament->id)],
            'player2' => ['nullable', 'string', 'max:100', new UniquePlayer($tournament->id)]
        ];
    }

    public function index(Request $request):Response
    {
        return inertia('Tournaments/Index', [
            'tournaments' => TournamentResource::collection(Tournament::query()
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
        ]);
    }

    public function create(Request $request):Response
    {
        return inertia('Tournaments/Edit');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        Tournament::create(array_merge($attributes, [
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('tournaments')
            ->with('success', 'Tournament created.');
    }

    public function edit(Request $request, Tournament $tournament): Response
    {
        return inertia('Tournaments/Edit', [
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
//                'start' => substr($tournament->start->toDateTimeLocalString(), 0, 19),
                'start' => $tournament->start->format('Y-m-d\TH:i'),
                'rounds' => $tournament->rounds,
                'games' => $tournament->games,
                'winpoints' => $tournament->winpoints,
                'published' => $tournament->published,
                'finished' => $tournament->finished,
            ],
        ]);
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        $tournament->update($attributes);

        return redirect()->route('tournaments')
            ->with('success', 'Tournament updated.');
    }

    public function destroy(Request $request, Tournament $tournament): RedirectResponse
    {
        $tournament->delete();

        return redirect()->route('tournaments')
            ->with('success', 'Tournament deleted');
    }

    public function createPlayers(Request $request, Tournament $tournament): Response
    {
        return inertia('Tournaments/Players', [
            'tournamentId' => $tournament->id,
            'players' => $tournament->players()->get(['players.id', 'name']),
            'teams' => $tournament->teamsAsArray(),
            'playerCount' => $tournament->currentPlayerCount(),
        ]);
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

    public function editFixture(Request $request, Fixture $fixture): Response
    {
        $tournament = $fixture->tournament;

        return inertia('Fixtures/Edit', [
            'fixture' => [
                'id' => $fixture->id,
                'tournament_id' => $tournament->id,
                'score' => $fixture->score,
                'round' => $fixture->round,
                'team1' => $fixture->team1->__toString(),
                'team2' => $fixture->team2->__toString(),
            ],
            'scorePattern' => $tournament->getScoreRegex(),
        ]);
    }

    public function updateFixture(Request $request, Fixture $fixture): RedirectResponse
    {
        $attributes = $request->validate([
            'score' => new Score($fixture),
        ]);

        $fixture->update($attributes);

        return redirect()->route('tournaments.show', $fixture->tournament_id)
            ->with('success', 'Tournament updated.');
    }

    public function tableLists(Request $request, Tournament $tournament, $round)
    {
        return (new Response($tournament->tableLists($round)->Output(), 200))
            ->header('Content-Type', 'application/pdf');
    }
}
