<?php

namespace App\Http\Api\ResourceDefinitions;

use App\CalculatedField;
use CatLab\Charon\Models\ResourceDefinition;

/**
 * Class CalculatedFieldResourceDefinition
 * @package App\Http\Api\ResourceDefinitions
 */
class CalculatedFieldResourceDefinition extends ResourceDefinition
{

    public function __construct()
    {
        parent::__construct(CalculatedField::class);

        $this->identifier('id');

        $this->field('target_field')
            ->display('targetField')
            ->visible(true, true)
            ->writeable(true, true)
        ;

        $this->field('formula')
            ->visible(true, true)
            ->writeable(true, true)
        ;
    }

}