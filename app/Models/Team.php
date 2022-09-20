<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Team extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['pivot'];

    public function player1(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class)
            ->withTimestamps();
    }

    public function __toString()
    {
        return $this->player1->name . '/' . $this->player2->name;
    }

    public function scopeUnused($query)
    {
        $where = "id not in (SELECT team1_id FROM fixtures " . "
            UNION SELECT team2_id FROM fixtures " .
            "UNION SELECT team_id FROM team_tournament)";

        $query->whereRaw($where);
    }

}
