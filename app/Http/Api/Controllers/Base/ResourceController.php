<?php

namespace App\Http\Api\Controllers\Base;

use App\JIRA\Tenant;
use CatLab\Charon\Library\ResourceDefinitionLibrary;
use CatLab\Charon\Processors\PaginationProcessor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Class ResourceController
 * @package App\Http\Api\Controllers\Base
 */
class ResourceController
{
    use \CatLab\Charon\Laravel\Controllers\ResourceController;
    use AuthorizesRequests;

    /**
     * AbstractResourceController constructor.
     * @param string $resourceDefinitionClass
     */
    public function __construct($resourceDefinitionClass)
    {
        $this->setResourceDefinition(ResourceDefinitionLibrary::make($resourceDefinitionClass));
    }

    /**
     * @return Tenant
     */
    public function getTenant(): Tenant
    {
        return Tenant::getAuthenticatedTenant();
    }

    /**
     * @param $message
     * @param int $statusCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getErrorResponse($message, $statusCode = 400)
    {
        return $this->toResponse([
            'error' => [
                'message' => $message
            ]
        ])->setStatusCode($statusCode);
    }
}