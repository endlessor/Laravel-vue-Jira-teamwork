<?php

namespace App\Http\Api\ResourceDefinitions;

use App\JIRA\Project;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class ProjectResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class ProjectResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Project::class);

        $this->identifier('id');

        $this->field('name')
            ->visible(true, true);

        $this->field('projectKey')
            ->visible(true, true)
            ->display('key')
        ;
    }


}