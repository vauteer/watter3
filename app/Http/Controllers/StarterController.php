<?php

namespace App\Http\Controllers;

use App\Http\Requests\StarterRequest;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Rules\UniquePlayer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class StarterController extends Controller
{
    private function editOptions(): array
    {
        return [
            'origin' => route('tournaments.index'),
        ];
    }

    public function create(Tournament $tournament): Response
    {
        return inertia('Tournaments/Starters', array_merge($this->editOptions(), [
            'tournamentId' => $tournament->id,
            'singles' => $tournament->players()->get(['players.id', 'name']),
            'teams' => $tournament->teamsAsArray(),
            'playerCount' => $tournament->currentPlayerCount(),
            'players' => Player::orderBy('name')->get(['id', 'name']),
        ]));
    }

    public function store(StarterRequest $request, Tournament $tournament): RedirectResponse
    {
        $attributes = $request->validated();

        $this->attachPlayers($tournament, $attributes);

        return redirect(route('tournaments.players.create', $tournament));
    }

    public function destroyPlayer(Tournament $tournament, Player $player): RedirectResponse
    {
        $tournament->players()->detach([$player->id]);

        return redirect(route('tournaments.players.create', $tournament));
    }

    public function destroyTeam(Tournament $tournament, Team $team): RedirectResponse
    {
        $tournament->teams()->detach([$team->id]);
        $tournament->players()->detach([$team->player1_id, $team->player2_id]);

        return redirect(route('tournaments.players.create', $tournament));
    }

    public function connect(Request $request, Tournament $tournament): RedirectResponse
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

        return redirect(route('tournaments.players.create', $tournament));
    }

    private function attachPlayers(Tournament $tournament, array $attributes)
    {
        $player1 = Player::firstOrCreate(['name' => $attributes['player1']['name']]);

        if ($attributes['player2']) {
            $player2 = Player::firstOrCreate(['name' => $attributes['player2']['name']]);

            $team = Team::firstOrCreate([
                'player1_id' => $player1->id,
                'player2_id' => $player2->id,
            ]);

            $tournament->teams()->syncWithoutDetaching([$team->id]);
        }
        else {
            $tournament->players()->syncWithoutDetaching([$player1->id]);
        }
    }
}
