<?php

namespace App\Console\Commands;

use App\JIRA\Issue;
use App\JIRA\Project;
use App\Teamwork\App;
use App\JIRA\Tenant;
use Illuminate\Console\Command;
use Redis;

class FlushRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:flush';

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
        Redis::flushDB();
        $this->output->success('Redis flushed.');
    }
}
