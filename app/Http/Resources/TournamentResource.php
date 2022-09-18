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
            'date' => $this->start->format('d.m.Y'),
            'time' => $this->start->format('H:i'),
            'rounds' => $this->rounds,
            'games' =>  $this->games,
            'winpoints' => $this->winpoints,
            'private' => $this->private,
            'creator' => $this->creator->name,

            'modifiable' => $request->user()?->can('update', $this->resource),
            'started' => $this->started(),
        ];
    }
}
