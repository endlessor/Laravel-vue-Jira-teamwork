<?php

namespace App\Http\Api\ResourceDefinitions\Teamwork;

use App\JIRA\Project;
use App\Teamwork\App;
use App\Teamwork\Company;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class TeamworkCompanyResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class TeamworkCompanyResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(Company::class);

        $this->identifier('id');

        $this->field('name')
            ->visible(true, true)
        ;

        
    }


}