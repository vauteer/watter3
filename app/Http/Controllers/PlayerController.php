<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request):Response
    {
        $this->setLastUrl();

        return inertia('Players/Index', [
            'players' => PlayerResource::collection(Player::query()
                ->when($request->input('search'), function($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString()
            ),

            'options' => $request->only(['search']),
        ]);
    }

    private function editOptions(): array
    {
        return [
            'origin' => $this->getLastUrl()
        ];
    }

    public function create():Response
    {
        return inertia('Players/Edit', $this->editOptions());
    }

    public function store(PlayerRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $player = Player::create($attributes);

        return redirect($this->getLastUrl())
            ->with('success', "{$player->name} hinzugefügt.");
    }

    public function edit(Player $player): Response
    {
        return inertia('Players/Edit', array_merge($this->editOptions(),[
            'player' => $player->getAttributes(),
            'deletable' => !$player->isUsed(),
        ]));
    }

    public function update(PlayerRequest $request, Player $player): RedirectResponse
    {
        $attributes = $request->validated();

        if ($existingPlayer = Player::where('name', $attributes['name'])
            ->where('id', '<>', $player->id)
            ->first()) {

            Team::where('player1_id', $player->id)->update(['player1_id' => $existingPlayer->id]);
            Team::where('player2_id', $player->id)->update(['player2_id' => $existingPlayer->id]);

            return $this->destroy($request, $player);
        }

        $player->update($attributes);

        return redirect($this->getLastUrl())
            ->with('success', "{$player->name} wurde geändert.");
    }

    public function destroy(Player $player): RedirectResponse
    {
        try {
            $player->delete();
        } catch (\Exception $ex) {
            return redirect()->route('players')
                ->with('error', "{$player->name} konnte nicht gelöscht werden.");
        }

        return redirect($this->getLastUrl())
            ->with('success', 'Spieler wurde gelöscht.');
    }

}
