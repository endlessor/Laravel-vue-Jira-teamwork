<?php

namespace App\Http\Api\Controllers;

use App\Http\Api\ResourceDefinitions\FieldResourceDefinition;
use Auth;
use App\Http\Api\Controllers\Base\ResourceController;
use App\Http\Api\ResourceDefinitions\ProjectResourceDefinition;
use App\JIRA\Project;
use App\JIRA\Tenant;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Models\ResourceResponse;

/**
 * Class FieldController
 * @package App\Http\Controllers\Api
 */
class FieldController extends ResourceController
{
    const RESOURCE_DEFINITION = FieldResourceDefinition::class;

    /**
     * FieldController constructor.
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

                $routes->get('projects/{projectId}/fields', 'FieldController@getFields')
                    ->summary('Return all JIRA projects')
                    ->returns(self::RESOURCE_DEFINITION)->many();

            }
        )->tag('projects');
    }

    /**
     * Get all projects I have access to.
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getFields($projectId)
    {
        $project = Project::where('id', '=', $projectId)->first();

        $project->tenant->syncFields();

        $this->authorize('fieldIndex', $project);

        $fields = $project->getFields();

        $context = $this->getContext(Action::INDEX);
        $resources =  $this->toResources($fields, $context);

        return new ResourceResponse($resources, $context);
    }
}