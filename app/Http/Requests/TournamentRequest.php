<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TournamentRequest extends FormRequest
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
        return  [
            'name' => 'required|string|max:100',
            'start' => 'required|date',
            'rounds' => 'int|min:2|max:9',
            'games' => 'int|min:2|max:9',
            'winpoints' => 'int|min:11|max:21',
            'published' => 'boolean',
        ];
    }
}
