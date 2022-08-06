<?php

namespace App\Http\Controllers;

use App\Http\Resources\TournamentResource;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Rules\UniquePlayer;
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
                'start' => $tournament->start->toDateTimeLocalString(),
                'rounds' => $tournament->rounds,
                'games' => $tournament->games,
                'winpoints' => $tournament->winpoints,
                'published' => $tournament->puplished,
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
            'id' => $tournament->id,
            'players' => $tournament->singlePlayers()->get(['players.id', 'name']),
            'teams' => $tournament->teamsAsArray(),
        ]);
    }

    public function storePlayers(Request $request, Tournament $tournament): Response
    {
        $attributes = $request->validate($this->playersValidationRules($tournament));

        $this->addPlayers($tournament, $attributes);

        return $this->createPlayers($request, $tournament);
    }

    private function addPlayers(Tournament $tournament, array $attributes)
    {
        $player1 = Player::firstOrCreate(['name' => $attributes['player1']]);
        $tournament->players()->syncWithoutDetaching([$player1->id]);

        if ($attributes['player2']) {
            $player2 = Player::firstOrCreate(['name' => $attributes['player2']]);
            $tournament->players()->syncWithoutDetaching([$player2->id]);

            $team = Team::firstOrCreate([
                'player1_id' => $player1->id,
                'player2_id' => $player2->id,
            ]);

            $tournament->teams()->syncWithoutDetaching([$team->id]);
        }
    }

}
