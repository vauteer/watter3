<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start' => $this->start->format('d:m:Y'),
            'rounds' => $this->rounds,
            'games' =>  $this->games,
            'winpoints' => $this->winpoints,
            'published' => $this->published,
            'finished' => $this->finished,
            'creator' => $this->creator->name,

            'editable' => Auth::user()?->admin,
        ];
    }
}
