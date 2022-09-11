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

    public static function deleteUnused()
    {
        $count = 0;
        foreach (Player::all() as $player) {
            if ($player->tournaments()->count() === 0) {
                if ($player->teams()->count() === 0) {
                   $player->delete();
                   $count++;
                };
            }
        }

        Log::info("Player::deleteUnused: {$count} Spieler wurden gelöscht.");
        return $count;
    }

}
