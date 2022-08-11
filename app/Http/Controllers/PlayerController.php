<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function validationRules(): array
    {
        return  [
            'name' => 'required|string|max:100',
        ];
    }

    public function index(Request $request):Response
    {
        return inertia('Players/Index', [
            'players' => PlayerResource::collection(Player::query()
                ->when($request->input('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString()
            ),

            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request):Response
    {
        return inertia('Players/Edit');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        $player = Player::create($attributes);

        return redirect()->route('players')
            ->with('success', "{$player->name} hinzugefügt");
    }

    public function edit(Request $request, Player $player): Response
    {
        return inertia('Players/Edit', [
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
            ],
        ]);
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $attributes = $request->validate($this->validationRules());

        if ($existingPlayer = Player::where('name', $attributes['name'])
            ->where('id', '<>', $player->id)
            ->first()) {

            Team::where('player1_id', $player->id)->update(['player1_id' => $existingPlayer->id]);
            Team::where('player2_id', $player->id)->update(['player2_id' => $existingPlayer->id]);

            return $this->destroy($request, $player);
        }

        $player->update($attributes);

        return redirect()->route('players')
            ->with('success', "{$player->name} wurde geändert");
    }

    public function destroy(Request $request, Player $player): RedirectResponse
    {
        try {
            $player->delete();
        } catch (\Exception $ex) {
            return redirect()->route('players')
                ->with('error', "{$player->name} konnte nicht gelöscht werden");
        }

        return redirect()->route('players')
            ->with('success', 'Spieler wurde gelöscht');
    }
}
