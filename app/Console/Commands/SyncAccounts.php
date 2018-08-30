<?php

namespace App\Console\Commands;

use App\JIRA\Issue;
use App\JIRA\Project;
use App\Teamwork\App;
use App\JIRA\Tenant;
use Illuminate\Console\Command;

class SyncAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::useFiles('php://stdout', 'info');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->syncTenant($tenant);
        }
    }

    /**
     * @param Tenant $tenant
     */
    private function syncTenant(Tenant $tenant)
    {
        $this->output->comment('Synchronizing ' . $tenant->baseUrl);

        $apps = [];
        foreach ($tenant->apps as $app) {
            $this->syncApp($app);
            $apps[] = $app;
        }

        $tenant->syncFields();

        $projects = $tenant->getProjects();
        foreach ($projects as $project) {
            $this->syncProject($project);
        }
    }

    /**
     * @param App $app
     */
    private function syncApp(App $app)
    {
        $this->output->comment('Synchronizing ' . $app->url);

        // Sync projects
        $projects = $app->getProjects();
    }

    /**
     * @param Project $project
     */
    private function syncProject(Project $project)
    {
        if (count($project->teamworkProjects) === 0) {
            \Log::info('Project ' . $project->id . ' has no external projects defined. Not syncing.');
            return;
        }

        $issues = $project->getIssues();
        foreach ($issues as $issue) {
            $this->syncIssue($project, $issue);

            // Sleep one second to reduce amount of api requests
            sleep(1);
        }
    }

    /**
     * @param Project $project
     * @param Issue $issue
     */
    private function syncIssue(Project $project, Issue $issue)
    {
        $project->syncIssue($issue);
    }
}
