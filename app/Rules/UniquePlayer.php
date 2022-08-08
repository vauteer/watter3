<?php

namespace App\Rules;

use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Contracts\Validation\InvokableRule;

class UniquePlayer implements InvokableRule
{
    private int $tournamentId;

    public function __construct($tournamentId)
    {
        $this->tournamentId = $tournamentId;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $player = Player::where('name', $value)->first();

        if ($player) {
            $tournament = Tournament::find($this->tournamentId);
            if ($tournament->players->contains($player)) {
                $fail("Der Spieler ist schon registriert");
            }
        }
    }
}
