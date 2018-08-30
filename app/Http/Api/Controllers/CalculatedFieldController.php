<?php

namespace App\Http\Api\Controllers;

use App\CalculatedField;
use App\Http\Api\ResourceDefinitions\CalculatedFieldResourceDefinition;
use App\JiraTeamworkLink;
use App\Http\Api\Controllers\Base\ResourceController;
use CatLab\Charon\Collections\RouteCollection;
use CatLab\Charon\Enums\Action;
use CatLab\Charon\Factories\EntityFactory;
use CatLab\Charon\Models\ResourceResponse;
use CatLab\Requirements\Exceptions\ResourceValidationException;
use Illuminate\Http\JsonResponse;

/**
 * Class CalculatedFieldController
 * @package App\Http\Controllers\Api
 */
class CalculatedFieldController extends ResourceController
{
    const RESOURCE_DEFINITION = CalculatedFieldResourceDefinition::class;

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

                $routes->post('links/{linkId}/calculatedFields', 'CalculatedFieldController@createField')
                    ->summary('Create a new calculated field for a link')
                    ->parameters()->resource(self::RESOURCE_DEFINITION)
                    ->returns()->one(self::RESOURCE_DEFINITION, Action::VIEW);

                $routes->get('links/{linkId}/teamworkFields', 'CalculatedFieldController@teamworkFields')
                    ->summary('Get all available teamwork fields');

                $routes->delete('links/{linkId}/calculatedFields/{fieldId}', 'CalculatedFieldController@deleteField')
                    ->summary('Remove a calculated field from a link');

            }
        )->tag('links');
    }

    /**
     * @param $linkId
     * @return ResourceResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \CatLab\Charon\Exceptions\InvalidContextAction
     * @throws \CatLab\Charon\Exceptions\InvalidTransformer
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createField($linkId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);
        $this->authorize('linkEdit', [ $link->jiraProject, $link ]);

        $context = $this->getContext(Action::CREATE);

        $resource = $this->bodyToResource($context);

        try {
            $resource->validate();
        } catch (ResourceValidationException $e) {
            return $this->getValidationErrorResponse($e);
        }

        /** @var CalculatedField $calculatedField */
        $calculatedField = $this->resourceTransformer->toEntity(
            $resource,
            $this->resourceDefinition,
            new EntityFactory(),
            $context
        );

        $calculatedField->jiraTeamworkLink()->associate($link);

        if (!$calculatedField) {
            return $this->getErrorResponse('Invalid field input.');
        }

        // Validate
        if (!$calculatedField->isValidFormula()) {
            return $this->getErrorResponse('Syntax error in formular; please correct.');
        }

        $calculatedField->save();

        $readContext = $this->getContext(Action::VIEW);
        $resources = $this->toResource($calculatedField, $readContext);
        return new ResourceResponse($resources, $readContext);
    }

    /**
     * @param $linkId
     * @param $calculatedFieldId
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteField($linkId, $calculatedFieldId)
    {
        /** @var JiraTeamworkLink $link */
        $link = JiraTeamworkLink::findOrFail($linkId);

        /** @var CalculatedField $calculatedField */
        $calculatedField = CalculatedField::findOrFail($calculatedFieldId);

        if ($calculatedField->jiraTeamworkLink->id !== $link->id) {
            abort(404);
        }

        $this->authorize('linkEdit', [ $link->jiraProject, $link ]);

        $calculatedField->delete();

        return new JsonResponse([ 'success' => true ]);
    }

    /**
     * @return JsonResponse
     */
    public function teamworkFields()
    {
        return new JsonResponse([
            'items' => [
                    [
                    'name' => 'estimated-minutes',
                    'type' => 'number'
                ],
                [
                    'name' => 'progress',
                    'type' => 'number'
                ]
            ]
        ]);
    }
}