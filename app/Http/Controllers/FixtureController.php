<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Models\Fixture;
use App\Rules\Score;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class FixtureController extends Controller
{
    public function edit(Fixture $fixture): Response
    {
        $tournament = $fixture->tournament;
        $origin = route('tournaments.show', [
            'tournament' => $fixture->tournament_id,
            'round' => $fixture->round
        ]);

        return inertia('Fixtures/Edit', [
            'origin' => $origin,
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

    public function update(Request $request, Fixture $fixture): RedirectResponse
    {
        $attributes = $request->validate([
            'score' => new Score($fixture),
        ]);

        $fixture->calculate($attributes['score'], true);
        //$fixture->update($attributes);

        if ($fixture->tournament->finished())
            Backup::create();

        return to_route('tournaments.show', ['tournament' => $fixture->tournament_id, 'round' => $fixture->round])
            ->with('success', "Ergebnis {$fixture->team1->__toString()} gegen {$fixture->team2->__toString()} wurde geändert.");
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

}
