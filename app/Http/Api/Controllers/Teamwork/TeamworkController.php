<?php

namespace App\Http\Api\Controllers\Teamwork;

use App\Http\Api\Controllers\Base\ResourceController;
use App\Http\Api\ResourceDefinitions\Teamwork\AppResourceDefinition;
use App\Teamwork\App;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Factories\EntityFactory;
use CatLab\Charon\Library\ResourceDefinitionLibrary;
use CatLab\Charon\Models\ResourceResponse;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;

/**
 * Class TeamworkProjectController
 * @package App\Http\Api\Controllers\Teamwork
 */
class TeamworkController extends ResourceController
{
    /**
     * TeamworkController constructor.
     */
    public function __construct()
    {
        parent::__construct(AppResourceDefinition::class);
    }

    /**
     * @param $routes
     */
    public static function setRoutes($routes)
    {
        $routes->group(
            function(RouteCollection $routes) {

                $routes->get('teamwork/apps', 'Teamwork\TeamworkController@getApps')
                    ->summary('Get all teamwork apps connected to current user.')
                    ->returns(AppResourceDefinition::class)->many();

                $routes->post('teamwork/apps', 'Teamwork\TeamworkController@createApp')
                    ->summary('Link up a new teamwork app to current user.')
                    ->parameters()->resource(AppResourceDefinition::class)
                    ->returns()->one(AppResourceDefinition::class);

                $routes->get('teamwork/apps/{appId}/sync', 'Teamwork\TeamworkController@getSync')
                    ->summary('Get sync url')
                    ->parameters()->path('appId')->required();

                $routes->delete('teamwork/apps/{appId}', 'Teamwork\TeamworkController@deleteApp')
                    ->summary('Delete a teamwork app')
                    ->parameters()->path('appId')->required();

            }
        )->tag('teamwork');
    }

    /**
     * Get all apps.
     * @return ResourceResponse
     * @throws \CatLab\Charon\Exceptions\InvalidEntityException
     */
    public function getApps()
    {
        $apps = $this->getTenant()->apps;

        $context = $this->getContext(Action::INDEX);
        $resources =  $this->toResources($apps, $context, AppResourceDefinition::class);

        return new ResourceResponse($resources, $context);
    }

    /**
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \CatLab\Charon\Exceptions\InvalidTransformer
     */
    public function createApp()
    {
        $context = $this->getContext(Action::CREATE);

        $resource = $this->bodyToResource($context);

        /** @var App $app */
        $app = $this->resourceTransformer->toEntity(
            $resource,
            $this->resourceDefinition,
            new EntityFactory(),
            $context
        );

        $tenant = $this->getTenant();
        $app->tenant()->associate($tenant);

        if (!$app->isValidCredentials()) {
            abort(400, 'Invalid teamwork credentials.');
        }

        $app->save();

        // And return!
        $readContext = $this->getContext(Action::VIEW);
        $resources =  $this->toResource($app, $readContext, AppResourceDefinition::class);

        return new ResourceResponse($resources, $readContext);
    }

    /**
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \CatLab\Charon\Exceptions\InvalidTransformer
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editApp($appId)
    {
        /** @var App $app */
        $app = App::findOrFail($appId);
        $this->authorize('edit', $app);

        $context = $this->getContext(Action::EDIT);

        $resource = $this->bodyToResource($context);

        /** @var App $app */
        $app = $this->resourceTransformer->toEntity(
            $resource,
            $this->resourceDefinition,
            new EntityFactory(),
            $context,
            $app
        );

        if (!$app->isValidCredentials()) {
            abort(400, 'Invalid teamwork credentials.');
        }

        $app->save();

        // And return!
        $readContext = $this->getContext(Action::VIEW);
        $resources =  $this->toResource($app, $readContext, AppResourceDefinition::class);

        return new ResourceResponse($resources, $readContext);
    }

    /**
     * @param $appId
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteApp($appId)
    {
        /** @var App $app */
        $app = App::findOrFail($appId);
        $this->authorize('delete', $app);

        $app->delete();

        return new JsonResponse([ 'success' => true ]);
    }

    /**
     * @param $appId
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSync($appId)
    {
        /** @var App $app */
        $app = App::findOrFail($appId);
        $this->authorize('sync', $app);

        $url = route('syncTeamwork', [ $app->id ]);

        return new JsonResponse([
            'url' => $url,
            'iframe' => true
        ]);
    }
}