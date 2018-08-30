<?php

namespace App\Http\Api\ResourceDefinitions;

use App\JIRA\Field;
use App\JIRA\Project;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class ProjectResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class FieldResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Field::class);

        $this->identifier('id');

        $this->field('name')
            ->visible(true, true);

        $this->field('key')
            ->visible(true, true);

        $this->field('type')
            ->visible(true, true);
    }


}