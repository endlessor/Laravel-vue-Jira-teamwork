<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class JiraTeamworkLink
 * @package App
 */
class JiraTeamworkLink extends Model
{
    protected $table = 'jira_teamwork_links';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teamworkProject()
    {
        return $this->belongsTo(\App\Teamwork\Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jiraProject()
    {
        return $this->belongsTo(\App\JIRA\Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calculatedFields()
    {
        return $this->hasMany(CalculatedField::class, 'link_id');
    }
}
