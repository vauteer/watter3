<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tournament  $tournament
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Tournament $tournament)
    {
        return true;
    }

    public function view(User $user, Tournament $tournament)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tournament  $tournament
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Tournament $tournament)
    {
        return $tournament->canEdit($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tournament  $tournament
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Tournament $tournament)
    {
        return $tournament->canEdit($user);
    }
}
