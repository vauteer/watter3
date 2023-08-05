<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Player extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['pivot'];

    // Vorsicht ! Nur solange keinem Team zugewiesen
    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class)
            ->withTimestamps();
    }

    public function playedTournaments()
    {
        return Tournament::playedBy($this->id);
    }

    public function teams()
    {
        return Team::where('player1_id', $this->id)->orWhere('player2_id', $this->id)->get();
    }

    public function isUsed(): bool
    {
        return $this->tournaments()->count() > 0 || $this->teams()->count() > 0;
    }

    public function scopeUnused($query)
    {
        $query->whereNotIn('id',
            DB::table('teams')->select('player1_id as player_id')
                ->union(DB::table('teams')->select('player2_id as player_id'))
                ->union(DB::table('player_tournament')->select('player_id'))
                ->pluck('player_id'));
    }

}
