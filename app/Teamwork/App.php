<?php

namespace App\Teamwork;

use App\JIRA\Tenant;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;

use GuzzleHttp\Client as Guzzle;
use Rossedman\Teamwork\Client;
use Rossedman\Teamwork\Factory as Teamwork;

/**
 * Class App
 * @package App\Teamwork
 */
class App extends Model
{
    protected $table = 'teamwork_apps';

    /**
     * @var Teamwork
     */
    private $client;

    /**
     * @return Teamwork
     */
    public function getClient()
    {
        if (!isset($this->client)) {
            $client = new Client(new Guzzle, $this->token, $this->url);
            $this->client = new Teamwork($client);
        }

        return $this->client;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->url;
    }

    /**
     * Check if this app is valid.
     * @return bool
     */
    public function isValidCredentials()
    {
        // Load projects to validate app
        try {
            $client = $this->getClient();
            $projects = $client->project()->all();

            return true;
        } catch (ClientException $e) {
            return false;
        }
    }

    /**
     * @return Project[]
     */
    public function getProjects()
    {
        $client = $this->getClient();

        $projects = $client->project()->all();

        $out = [];
        foreach ($projects['projects'] as $project) {

            $company = Company::syncFromData($project['company']);
            $company->apps()->sync([ $this->id ], false);

            $project = Project::syncFromData($company, $project);

            $out[] = $project;

        }
        return $out;

    }
}
