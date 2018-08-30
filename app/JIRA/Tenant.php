<?php

namespace App\JIRA;

use App\JIRA\Field;
use App\JIRA\Issue;
use App\JIRA\Project;
use App\JIRA\Utils\PagedRequest;
use App\JWTRequest;
use App\Teamwork\App;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /**
     * @var Tenant
     */
    private static $authenticatedTenant = null;

    /** @var FieldCollection */
    private $fieldCollection;

    /**
     * @param Tenant $tenant
     */
    public static function setAuthenticatedTenant(Tenant $tenant)
    {
        self::$authenticatedTenant = $tenant;
    }

    /**
     * @return Tenant|null
     */
    public static function getAuthenticatedTenant()
    {
        return self::$authenticatedTenant;
    }

    /**
     * @param $key
     * @return Tenant|null
     */
    public static function fromClientKey($key)
    {
        $tenant = self::where('clientKey', $key)->get()->first();
        return $tenant;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apps()
    {
        return $this->hasMany(App::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        $request = new JWTRequest($this);
        $json = $request->get('/rest/api/2/project');

        $out = [];
        foreach ($json as $v) {
            $project = Project::syncFromData($this, $v);
            $out[] = $project;
        }
        return $out;
    }

    /**
     * @param $jiraProjectId
     * @return \App\JIRA\Project|null
     */
    public function getProject($jiraProjectId)
    {
        foreach ($this->getProjects() as $project) {
            if ($project->jira_id == $jiraProjectId) {
                return $project;
            }
        }

        return null;
    }

    /**
     * @param Project $project
     * @return Issue[]
     */
    public function getIssues(Project $project)
    {
        $list = new PagedRequest($this, '/rest/api/2/search?jql=project=' . $project->key, []);
        $list->setItemsKey('issues');
        $list->setTransformer(function($item) {
            return new Issue($item);
        });
        $list->init();

        return $list;
    }

    /**
     *
     */
    public function syncFields()
    {
        $request = new JWTRequest($this);
        $json = $request->get('/rest/api/2/field');

        foreach ($json as $v) {
            $fields[] = Field::syncFromData($this, $v);
        }
    }
}
