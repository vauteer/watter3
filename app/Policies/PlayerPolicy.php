<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlayerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->admin;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Player  $player
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Player $player)
    {
        return $user->admin;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->admin;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Player  $player
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Player $player)
    {
        return $user->admin;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Player  $player
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Player $player)
    {
        return $user->admin;
    }

}
