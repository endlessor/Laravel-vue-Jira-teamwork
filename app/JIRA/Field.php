<?php

namespace App\JIRA;

use App\JIRA\Tenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Field
 * @package App\JIRA
 */
class Field extends Model
{
    protected $table = 'jira_fields';

    /**
     * @param Tenant $tenant
     * @param $fieldData
     * @return Project
     */
    public static function syncFromData(Tenant $tenant, $fieldData)
    {
        $project = $tenant->fields()
            ->where('jira_id', $fieldData['id'])
            ->first();

        if (!$project) {
            $project = new self();
            $project->jira_id = $fieldData['id'];
            $project->tenant()->associate($tenant);
        } else {
            $project->setRelation('tenant', $tenant);
        }

        $project->name = (string) $fieldData['name'];
        $project->key = (string) $fieldData['key'];

        if (isset($fieldData['schema'])) {
            $project->type = (string) $fieldData['schema']['type'];
        }

        $project->save();

        return $project;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}