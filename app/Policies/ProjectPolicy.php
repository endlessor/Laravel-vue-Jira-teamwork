<?php

namespace App\Policies;

use App\JIRA\Project;
use App\JiraTeamworkLink;
use Illuminate\Contracts\Auth\Access\Authorizable;

/**
 * Class ProjectPolicy
 * @package App\Policies
 */
class ProjectPolicy extends AbstractPolicy
{
    /**
     * Can a user view all links
     * @param Authorizable $user
     * @param Project $project
     * @return bool
     */
    public function linkIndex(Authorizable $user, Project $project)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * Can a user create a link
     * @param Authorizable $user
     * @param Project $project
     * @return bool
     */
    public function linkCreate(Authorizable $user, Project $project)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * @param Authorizable $user
     * @param Project $project
     * @param JiraTeamworkLink $link
     * @return bool
     */
    public function linkEdit(Authorizable $user, Project $project, JiraTeamworkLink $link)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * @param Authorizable $user
     * @param Project $project
     * @param JiraTeamworkLink $link
     * @return bool
     */
    public function linkView(Authorizable $user, Project $project, JiraTeamworkLink $link)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * @param Authorizable $user
     * @param Project $project
     * @param JiraTeamworkLink $link
     * @return bool
     */
    public function linkRemove(Authorizable $user, Project $project, JiraTeamworkLink $link)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * Can view all fields
     * @param Authorizable $user
     * @param Project $project
     * @return bool
     */
    public function fieldIndex(Authorizable $user, Project $project)
    {
        return $this->isMyProject($user, $project);
    }

    /**
     * Is this my own project?
     * @param Authorizable $user
     * @param Project $project
     * @return bool
     */
    protected function isMyProject(Authorizable $user, Project $project)
    {
        return $user->tenant->id === $project->tenant->id;
    }
}