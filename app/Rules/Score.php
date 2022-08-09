<?php

namespace App\Rules;

use App\Models\Fixture;
use App\Models\Tournament;
use Illuminate\Contracts\Validation\InvokableRule;

class Score implements InvokableRule
{
    private Fixture $fixture;
    private Tournament $tournament;

    public function __construct(Fixture $fixture)
    {
        $this->fixture = $fixture;
        $this->tournament = $fixture->tournament;
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
        if (!preg_match('/' . $this->tournament->getScoreRegex() . '/', $value)) {
            $fail("Das Ergebnis hat ein ungültiges Format oder zuwenig Spiele");
        }

        $passed = $this->fixture->calculate($value);

        if ($passed !== true)
            $fail($passed);
    }
}
