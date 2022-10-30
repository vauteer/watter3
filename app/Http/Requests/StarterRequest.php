<?php

namespace App\Http\Requests;

use App\Rules\UniquePlayer;
use Illuminate\Foundation\Http\FormRequest;

class StarterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $tournament = $this->route('tournament');

        return  [
            'player1' => ['required', new UniquePlayer($tournament->id)],
            'player2' => ['nullable', 'different:player1', new UniquePlayer($tournament->id)],
        ];
    }
}
