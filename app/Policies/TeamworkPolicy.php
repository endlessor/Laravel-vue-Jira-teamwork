<?php

namespace App\Policies;

use App\JIRA\Project;
use App\JiraTeamworkLink;
use App\Teamwork\App;
use Illuminate\Contracts\Auth\Access\Authorizable;

/**
 * Class TeamworkPolicy
 * @package App\Policies
 */
class TeamworkPolicy extends AbstractPolicy
{
    /**
     * @param Authorizable $user
     * @param App $app
     * @return bool
     */
    public function delete(Authorizable $user, App $app)
    {
        return $user->tenant->id === $app->tenant->id;
    }

    /**
     * @param Authorizable $user
     * @param App $app
     * @return bool
     */
    public function edit(Authorizable $user, App $app)
    {
        return $user->tenant->id === $app->tenant->id;
    }

    /**
     * @param Authorizable $user
     * @param App $app
     * @return bool
     */
    public function sync(Authorizable $user, App $app)
    {
        return $user->tenant->id === $app->tenant->id;
    }
}