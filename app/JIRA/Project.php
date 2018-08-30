<?php

namespace App\JIRA;

use App\Exceptions\IssueLocked;
use App\Helpers\LoggingTrait;
use App\JIRA\Tenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 * @package App\JIRA
 */
class Project extends Model
{
    use LoggingTrait;

    protected $table = 'jira_projects';

    /**
     * @param Tenant $tenant
     * @param $projectData
     * @return Project
     */
    public static function syncFromData(Tenant $tenant, $projectData)
    {
        $project = $tenant->projects()
            ->where('jira_id', $projectData['id'])
            ->first();

        if (!$project) {
            $project = new Project();
            $project->jira_id = $projectData['id'];
            $project->tenant()->associate($tenant);
        } else {
            $project->setRelation('tenant', $tenant);
        }

        $project->name = (string) $projectData['name'];
        $project->key = (string) $projectData['key'];
        $project->type = (string) $projectData['projectTypeKey'];

        $project->save();

        return $project;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teamworkProjects()
    {
        return $this->hasMany(
            \App\JiraTeamworkLink::class,
            'jira_project_id'
        );
    }

    /**
     * @return Issue[]
     */
    public function getIssues()
    {
        return $this->tenant->getIssues($this);
    }

    /**
     * @param $issueId
     * @return Issue|null
     */
    public function getIssue($issueId)
    {
        $issues = $this->getIssues();
        foreach ($issues as $issue) {
            if ($issue->getId() == $issueId) {
                return $issue;
            }
        }
        return null;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        /** @var Tenant $tenant */
        $tenant = $this->tenant;
        return $tenant->fields;
    }

    /**
     * @param Issue $issue
     * @throws \Exception
     */
    public function syncIssue(Issue $issue)
    {
        if (count($this->teamworkProjects) === 0) {
            $this->info('Project ' . $this->id . ' has no external projects defined. Not syncing.');
            return;
        }

        try {
            $issue->lock();
        } catch (IssueLocked $e) {
            $this->warning('Tried to sync issue ' . $issue->getKey() . ', but it is locked. Skipping.');
            return;
        }

        $this->info('Syncing issue ' . $issue->getKey());

        try {

            foreach ($this->teamworkProjects as $link) {
                /** @var \App\JiraTeamworkLink $link */

                /** @var \App\Teamwork\Project $teamworkProject */
                $teamworkProject = $link->teamworkProject;

                $teamworkProject->syncIssue($link, $issue);
            };
        } catch (\Exception $e) {
            $issue->unlock();
            throw $e;
        }

        $issue->unlock();

        //$this->info('Done syncing issue ' . $issue->getKey());
    }

    /**
     * @param Issue $issue
     * @return string
     */
    public function getIssueUrl(Issue $issue)
    {
        return $this->tenant->baseUrl . '/browse/' . $issue->getKey();
    }

    /**
     * @return string
     */
    public function projectKey()
    {
        return $this->key;
    }

    /**
     * Return the string that will be placed in front of all logs.
     * @return string
     */
    function getLogPrefix()
    {
        return '[JIRA:' . $this->key . ']';
    }
}
