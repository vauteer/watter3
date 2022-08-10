<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Player extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['pivot'];

    public function teams()
    {
        return Team::where('player1_id', $this->id)->orWhere('player2_id', $this->id)->get();
    }

}
