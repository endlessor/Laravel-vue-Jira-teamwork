<?php

namespace App\Http\Controllers;

use App\JIRA\Project;
use App\JiraTeamworkLink;
use App\Teamwork\App;
use Illuminate\Http\JsonResponse;

/**
 * Class SyncController
 * @package App\Http\Controllers
 */
class SyncController extends Controller
{
    /**
     * @param $teamworkAppId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function syncTeamworkApp($teamworkAppId)
    {
        $app = App::find($teamworkAppId);

        $outputData = [];
        $outputData['projects'] = [];

        foreach ($app->getProjects() as $project) {

            $projectData = [
                'name' => $project->name,
                'links' => []
            ];

            /** @var JiraTeamworkLink $links */
            $links = $project->jiraProjects;

            foreach ($links as $link) {
                $linkData = [
                    'id' => $link->id,
                    'tickets' => []
                ];

                /** @var Project $jiraProject */
                $jiraProject = $link->jiraProject;
                if ($jiraProject) {
                    foreach ($jiraProject->getIssues() as $issue) {
                        $linkData['tickets'][] = [
                            'id' => $issue->getId(),
                            'summary' => $issue->getSummary(),
                            'action' => action('SyncController@syncIssue', [ $link->id, $issue->getId() ])
                        ];
                    }
                }

                $projectData['links'][] = $linkData;
            }
            $outputData['projects'][] = $projectData;
        }


        return view(
            'syncstatus/status',
            [
                'syncdata' => $outputData
            ]
        );
    }

    /**
     * @param $linkId
     * @param $issueId
     * @return JsonResponse
     */
    public function syncIssue($linkId, $issueId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);

        /** @var Project $jiraProject */
        $jiraProject = $link->jiraProject;

        /** @var \App\Teamwork\Project $teamworkProject */
        $teamworkProject = $link->teamworkProject;

        $issue = $jiraProject->getIssue($issueId);
        if (!$issue) {
            return new JsonResponse([
                'error' => [
                    'message' => 'Issue not found: ' . $issueId
                ]
            ], 404);
        }

        $teamworkProject->syncIssue($link, $issue);

        return new JsonResponse([
            'success' => true
        ], 200);
    }
}