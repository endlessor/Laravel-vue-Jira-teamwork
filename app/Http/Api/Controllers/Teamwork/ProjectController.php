<?php

namespace App\Http\Api\Controllers\Teamwork;

use App\Http\Api\Controllers\Base\ResourceController;
use App\Http\Api\ResourceDefinitions\Teamwork\AppResourceDefinition;
use App\Http\Api\ResourceDefinitions\Teamwork\TeamworkProjectResourceDefinition;
use App\Teamwork\App;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Factories\EntityFactory;
use CatLab\Charon\Library\ResourceDefinitionLibrary;
use CatLab\Charon\Models\ResourceResponse;
use GuzzleHttp\Exception\ClientException;

/**
 * Class TeamworkProjectController
 * @package App\Http\Api\Controllers\Teamwork
 */
class ProjectController extends ResourceController
{
    /**
     * TeamworkController constructor.
     */
    public function __construct()
    {
        parent::__construct(TeamworkProjectResourceDefinition::class);
    }

    /**
     * @param $routes
     */
    public static function setRoutes($routes)
    {
        $routes->group(
            function(RouteCollection $routes) {

                $routes->get('teamwork/projects', 'Teamwork\ProjectController@getProjects')
                    ->summary('Get all teamwork projects you have access to')
                    ->returns(TeamworkProjectResourceDefinition::class)->many();
            }
        )->tag('teamwork');
    }

    /**
     * Get all apps.
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     */
    public function getProjects()
    {
        $apps = $this->getTenant()->apps;

        $out = [];
        foreach ($apps as $app) {
            /** @var App $app */
            $projects = $app->getProjects();
            foreach ($projects as $project) {
                $out[] = $project;
            }
        }

        $context = $this->getContext(Action::INDEX);

        $resources = $this->toResources($out, $context);
        return new ResourceResponse($resources, $context);
    }
}