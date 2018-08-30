<?php

namespace App\Http\Api\ResourceDefinitions;

use App\Http\Api\ResourceDefinitions\Teamwork\TeamworkProjectResourceDefinition;
use App\JIRA\Field;
use App\JIRA\Project;
use App\JiraTeamworkLink;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class LinkResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class LinkResourceDefinition extends ResourceDefinition
{
    public function __construct()
    {
        parent::__construct(JiraTeamworkLink::class);

        $this->identifier('id');

        $this->field('teamwork_default_list')
            ->display('defaultTaskList')
            ->visible(true, true)
            ->writeable(true, true);

        $this->relationship('teamworkProject', TeamworkProjectResourceDefinition::class)
            ->visible(true)
            ->one()
            ->expanded()
            ->writeable(true, true)
        ;

        $this->relationship('calculatedFields', CalculatedFieldResourceDefinition::class)
            ->visible(true)
            ->many()
            ->expanded()
            ->writeable(true, true)
        ;
    }


}