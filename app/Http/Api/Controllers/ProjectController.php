<?php

namespace App\Http\Api\Controllers;

use Auth;
use App\Http\Api\Controllers\Base\ResourceController;
use App\Http\Api\ResourceDefinitions\ProjectResourceDefinition;
use App\JIRA\Project;
use App\JIRA\Tenant;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Models\ResourceResponse;

/**
 * Class ProjectController
 * @package App\Http\Controllers\Api
 */
class ProjectController extends ResourceController
{
    const RESOURCE_DEFINITION = ProjectResourceDefinition::class;

    public function __construct()
    {
        parent::__construct(self::RESOURCE_DEFINITION);
    }

    public static function setRoutes(RouteCollection $routes)
    {
        $routes->group(
            function(RouteCollection $routes) {

                $routes->get('projects', 'ProjectController@getProjects')
                    ->summary('Return all JIRA projects')
                    ->returns(self::RESOURCE_DEFINITION)->many();

            }
        )->tag('projects');
    }

    /**
     * Get all projects I have access to.
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     */
    public function getProjects()
    {
        $tenant = $this->getTenant();
        $projects = $tenant->getProjects();

        $context = $this->getContext(Action::INDEX);
        $resources =  $this->toResources($projects, $context);

        return new ResourceResponse($resources, $context);
    }
}