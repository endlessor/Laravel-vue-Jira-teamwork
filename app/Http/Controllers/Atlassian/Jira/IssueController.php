<?php

namespace App\Http\Controllers\Atlassian\Jira;

use App\JIRA\Issue;
use App\JIRA\Tenant;
use Request;

/**
 * Class IssueController
 * @package App\Http\Controllers\Atlassian\Jira
 */
class IssueController
{
    /**
     * @var mixed
     */
    private $content;

    public function created()
    {
        $issue = $this->getIssue();
        $project = $this->getProject();

        $project->syncIssue($issue);

        return 'issue created';
    }

    public function updated()
    {
        $issue = $this->getIssue();
        $project = $this->getProject();

        $project->syncIssue($issue);

        return 'issue updated';
    }

    public function deleted()
    {
        return 'issue deleted';

        /*
        $issue = $this->getIssue();
        $project = $this->getProject();

        $project->syncIssue($issue);

        return 'issue deleted';
        */
    }

    /**
     * @return Issue
     */
    protected function getIssue()
    {
        $content = $this->getContent();
        return new Issue($content['issue']);
    }

    /**
     * @return \App\JIRA\Project|null
     */
    protected function getProject()
    {
        $content = $this->getContent();
        $projectId = $content['issue']['fields']['project']['id'];

        $tenant = Tenant::getAuthenticatedTenant();
        $project = $tenant->getProject($projectId);

        if (!$project) {
            \Log::error('Project not found: ' . $projectId);
            abort(400, 'Project not found.');
        }

        return $project;
    }

    /**
     * @return mixed
     */
    protected function getContent()
    {
        if (!isset($this->content)) {
            $content = Request::getContent();
            $content = json_decode($content, true);

            if (!$content) {
                \Log::error('No content found.');
                abort(400, 'No content found.');
            }

            $this->content = $content;
        }

        return $this->content;
    }

}