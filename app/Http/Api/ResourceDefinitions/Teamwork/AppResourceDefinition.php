<?php

namespace App\Http\Api\ResourceDefinitions\Teamwork;

use App\JIRA\Project;
use App\Teamwork\App;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class ProjectResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class AppResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(App::class);

        $this->identifier('id');

        $this->field('name')
            ->visible(true, true)
        ;

        $this->field('url')
            ->visible(true, true)
            ->writeable(true, true)
        ;

        $this->field('token')
            ->visible(true, true)
            ->display('token')
            ->writeable(true, true)
        ;
    }


}