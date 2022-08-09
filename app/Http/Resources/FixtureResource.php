<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
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
            'team1' => $this->team1->__toString(),
            'team2' => $this->team2->__toString(),
            'round' => $this->round,
            'tableNumber' =>  $this->table_number,
            'score' => $this->score,
            'scoreGames' => $this->team1_won . ':' . $this->team2_won,
            'scorePoints' => $this->team1_points . ':' . $this->team2_points,
            'games' => $this->score ? explode(' ', $this->score) : array(),

            'editable' => auth()->check(),
        ];
    }
}
