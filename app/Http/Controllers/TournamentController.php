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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class TournamentController extends Controller
{
    private function rules(): array
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

    private function applyFilters(Request $request): Builder
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
        $this->setLastUrl();

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

            'options' => $request->only(['search']),

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
            'origin' => $this->getLastUrl(),
        ];
    }

    public function create():Response
    {
        return inertia('Tournaments/Edit', $this->editOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate($this->rules());

        $tournament = Tournament::create(array_merge($attributes, [
            'created_by' => auth()->id(),
        ]));

        Team::unused()->delete();
        Player::unused()->delete();

        return redirect($this->getLastUrl())
            ->with('success', "{$tournament->name} wurde hinzugefügt.");
    }

    public function edit(Tournament $tournament): Response
    {
        $user = auth()->user();

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
            'deletable' => $user->admin || $tournament->created_by === $user->id,
        ]));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $attributes = $request->validate($this->rules());

        $tournament->update($attributes);

        return redirect($this->getLastUrl())
            ->with('success', "{$tournament->name} wurde geändert.");
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        Backup::create();

        $tournament->delete();
        Team::unused()->delete();
        Player::unused()->delete();

        return redirect($this->getLastUrl())
            ->with('success', 'Turnier wurde gelöscht.');
    }

    public function draw(Tournament $tournament): RedirectResponse
    {
        $tournament->draw();

        return redirect()->route('tournaments.show', [
            'tournament' => $tournament->id,
            'round' => 1
        ]);
    }

    public function tableLists(Tournament $tournament, $round)
    {
        return (new \Illuminate\Http\Response($tournament->tableLists($round)->Output(), 200))
            ->header('Content-Type', 'application/pdf');
    }

}
