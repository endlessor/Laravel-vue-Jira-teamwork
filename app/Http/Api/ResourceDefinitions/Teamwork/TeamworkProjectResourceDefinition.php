<?php

namespace App\Http\Api\ResourceDefinitions\Teamwork;

use App\JIRA\Project;
use App\Teamwork\App;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class TeamworkProjectResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class TeamworkProjectResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(\App\Teamwork\Project::class);

        $this->identifier('id');

        $this->field('name')
            ->visible(true, true)
        ;

        $this->relationship('company', TeamworkCompanyResourceDefinition::class)
            ->one()
            ->expanded()
            ->visible(true, true);
    }


}