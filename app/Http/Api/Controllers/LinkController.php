<?php

namespace App\Http\Api\Controllers;

use App\Http\Api\ResourceDefinitions\LinkResourceDefinition;
use App\Http\Api\ResourceDefinitions\Teamwork\TeamworkProjectResourceDefinition;
use App\JiraTeamworkLink;
use App\Http\Api\Controllers\Base\ResourceController;
use App\JIRA\Project;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Models\ResourceResponse;
use CatLab\Requirements\Exceptions\ResourceValidationException;
use Illuminate\Http\JsonResponse;

/**
 * Class FieldController
 * @package App\Http\Controllers\Api
 */
class LinkController extends ResourceController
{
    const RESOURCE_DEFINITION = LinkResourceDefinition::class;

    /**
     * LinkController constructor.
     */
    public function __construct()
    {
        parent::__construct(self::RESOURCE_DEFINITION);
    }

    /**
     * @param RouteCollection $routes
     */
    public static function setRoutes(RouteCollection $routes)
    {
        $routes->group(
            function(RouteCollection $routes) {

                $routes->get('projects/{projectId}/links', 'LinkController@getLinks')
                    ->summary('Get all linked teamwork projects')
                    ->returns(self::RESOURCE_DEFINITION)->many()
                    ->tag('projects')
                ;

                $routes->link('projects/{projectId}/links', 'LinkController@createLink')
                    ->summary('Link a teamwork project to this project')
                    ->parameters()->resource(LinkResourceDefinition::class)
                    ->returns(self::RESOURCE_DEFINITION)->many()
                    ->tag('projects')
                ;

                $routes->get('links/{linkId}', 'LinkController@viewLink')
                    ->summary('View a linked teamwork project')
                    ->parameters()->resource(LinkResourceDefinition::class)
                    ->returns(self::RESOURCE_DEFINITION)->one()
                ;

                $routes->put('links/{linkId}', 'LinkController@editLink')
                    ->summary('Link a teamwork project to this project')
                    ->parameters()->resource(LinkResourceDefinition::class)
                    ->returns(self::RESOURCE_DEFINITION)->many()
                ;

                $routes->delete('links/{linkId}', 'LinkController@removeLink')
                    ->summary('Remove a linked teamwork project')
                    ->parameters()->resource(LinkResourceDefinition::class)
                    ->returns(self::RESOURCE_DEFINITION)->many()
                ;

            }
        )->tag('links');
    }

    /**
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getLinks($projectId)
    {
        $project = Project::where('id', '=', $projectId)->first();

        $this->authorize('linkIndex', $project);

        $teamworkProjects = $project->teamworkProjects;

        $context = $this->getContext(Action::INDEX);
        $resources =  $this->toResources($teamworkProjects, $context);

        return new ResourceResponse($resources, $context);
    }

    /**
     * @param $projectId
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createLink($projectId)
    {
        /** @var Project $project */
        $project = Project::findOrFail($projectId);

        $this->authorize('linkCreate', $project);

        $context = $this->getContext(Action::CREATE);
        $teamworkProjects = $this->bodyIdentifiersToEntities($context, TeamworkProjectResourceDefinition::class);

        $out = [];
        foreach ($teamworkProjects as $teamworkProject) {
            $link = new JiraTeamworkLink();
            $link->jiraProject()->associate($project);
            $link->teamworkProject()->associate($teamworkProject);
            $link->teamwork_default_list = 'Backlog';

            $link->save();

            $out[] = $link;
        }

        $readContext = $this->getContext(Action::INDEX);

        $resources = $this->toResources($out, $readContext);
        return new ResourceResponse($resources, $readContext);
    }

    /**
     * @param $linkId
     * @return ResourceResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editLink($linkId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);

        $this->authorize('linkEdit', [ $link->jiraProject, $link ]);

        $context = $this->getContext(Action::EDIT);

        $resource = $this->bodyToResource($context);

        try {
            $resource->validate();
        } catch (ResourceValidationException $e) {
            return $this->getValidationErrorResponse($e);
        }

        $this->toEntity($resource, $context, $link);
        $link->save();

        $readContext = $this->getContext(Action::VIEW);

        $resources = $this->toResource($link, $readContext);
        return new ResourceResponse($resources, $readContext);
    }

    /**
     * @param $linkId
     * @return ResourceResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function viewLink($linkId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);

        $this->authorize('linkView', [ $link->jiraProject, $link ]);

        $context = $this->getContext(Action::VIEW);

        $resources = $this->toResource($link, $context);
        return new ResourceResponse($resources, $context);
    }

    /**
     * @param $linkId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function removeLink($linkId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);
        $this->authorize('linkRemove', [ $link->jiraProject, $link ]);

        $link->delete();

        return new JsonResponse([ 'success' => true ]);
    }
}