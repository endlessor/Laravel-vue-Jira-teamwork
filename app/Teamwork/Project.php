<?php

namespace App\Teamwork;

use App\CalculatedField;
use App\Helpers\LoggingTrait;
use App\JIRA\Issue;
use App\JiraTeamworkLink;
use App\Teamwork\Collections\TaskCollection;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Redis;

class Project extends Model
{
    use LoggingTrait;

    protected $table = 'teamwork_projects';

    /**
     * @var TaskCollection
     */
    private $tasks;

    /**
     * @var int
     */
    private $tasklistId;

    /**
     * @var array
     */
    private $tasklists;

    /**
     * @param Company $company
     * @param $projectData
     * @return Project
     */
    public static function syncFromData(Company $company, $projectData)
    {
        $project = $company->projects()
            ->where('teamwork_id', $projectData['id'])
            ->first();

        if (!$project) {
            $project = new Project();
            $project->teamwork_id = $projectData['id'];
            $project->company()->associate($company);
        } else {
            $project->setRelation('company', $company);
        }

        $project->name = (string) $projectData['name'];
        $project->description = (string) $projectData['description'];

        $project->save();

        return $project;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jiraProjects()
    {
        return $this->hasMany(\App\JiraTeamworkLink::class, 'teamwork_project_id');
    }

    /**
     * @return TaskCollection
     */
    public function getTasks()
    {
        if (!isset($this->tasks)) {
            $this->tasks = $this->loadTasks();
        }

        return $this->tasks;
    }

    /**
     * @param int $teamworkTaskId
     * @return Task|null
     */
    public function getTask($teamworkTaskId)
    {
        $teamworkTaskId = intval($teamworkTaskId);
        $client = $this->getClient();

        try {
            $task = $client->task($teamworkTaskId)->find([ 'includeCompletedTasks' => 1 ]);

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                return null;
            }
        }

        if ($task && $task['todo-item']) {
            $task = new Task($task['todo-item']);
            if ($task->isDeleted()) {
                $this->warning('Teamwork task ' . $teamworkTaskId . ' is deleted.');
                return null;
            }

            return $task;
        }
        return null;
    }

    /**
     * @param JiraTeamworkLink $link
     * @param Issue $issue
     */
    public function syncIssue(JiraTeamworkLink $link, Issue $issue)
    {
        $task = $this->getTaskFromKey($this->getIssueKey($issue));

        if (!$task) {
            $this->createFromJira($link, $issue);
        } else {

            // Check if completed (completed tasks can't be edited anymore)
            if ($task->isCompleted()) {
                $this->warning($issue->getKey() .
                    ': Teamwork task ' . $task->getId() . ' is completed and cannot be edited anymore.');
                return;
            }

            $this->updateFromJira($link, $issue, $task);
        }
    }

    /**
     * @param $name
     * @return int
     */
    public function touchTasklistFromName($name)
    {
        $lists = $this->loadTaskLists();
        foreach ($lists as $list) {
            if ($list['name'] == $name) {
                return $list;
            }
        }

        // Create
        $client = $this->getClient();

        $result = $client->project($this->teamwork_id)->createTasklist([
            'name' => $name
        ]);

        $newId = intval($result['TASKLISTID']);

        $tasklist = $client->tasklist($newId)->find();
        $tasklist = $tasklist['todo-list'];

        $this->tasklists[] = $tasklist;

        return $tasklist;
    }

    /**
     * @return TaskCollection|array
     */
    protected function loadTasks()
    {
        $client = $this->getClient();

        $taskCollection = new TaskCollection();

        $tasklists = $this->loadTaskLists();
        foreach ($tasklists as $v) {

            $this->tasklistId = intval($v['id']);

            $recordsPerPage = 250;
            $page = 1;

            do {

                $tasks = $client
                    ->tasklist(intval($v['id']))
                    ->tasks([
                        'includeCompletedTasks' => 1,
                        'pageSize' => $recordsPerPage,
                        'page' => $page
                    ]);

                $page ++;

                foreach ($tasks['todo-items'] as $task) {
                    $taskCollection[] = new Task($task);
                }

            } while (count($tasks['todo-items']) > 0);

            $this->info('Loaded ' . count($taskCollection) . ' tasks from Teamwork');
        }

        return $taskCollection;
    }

    /**
     * Look for a task with a given key.
     * (Use redis to speed up the process and to avoid overlaps)
     * @param $key
     * @return Task|null
     */
    protected function getTaskFromKey($key)
    {
        // First check redis.
        $redisTaskKey = 'teamwork:' . $this->id . ':taskIds:' . $key;

        $teamworkId = Redis::get($redisTaskKey);
        if ($teamworkId) {

            // Check if the task has been changed in teamwork
            $this->info('Found redis task id ' . $teamworkId . ' for task ' . $key);

            $task = $this->getTask($teamworkId);

            // Check if the key is still valid
            if ($task && $task->doesKeyMatch($key)) {
                // Return the task!
                return $task;
            }

            $this->warning('Title of ' . $teamworkId . ' was changed, or the task was removed... refreshing.');

            // Nope, not valid anymore. Clear and load fresh.
            Redis::del($redisTaskKey);
        }

        $tasks = $this->getTasks();

        /** @var Task $task */
        $task = $tasks->getFromKey($key);

        // Also set in redis (if found)
        if ($task) {
            Redis::set($redisTaskKey, $task->getId());
        }

        return $task;
    }

    /**
     * @param JiraTeamworkLink $link
     * @param Issue $issue
     * @return Task
     */
    protected function createFromJira(JiraTeamworkLink $link, Issue $issue)
    {
        $this->info('Task ' . $issue->getKey() . ' does not exist yet... creating!');

        /** @var App $app */
        $app = $this->company->apps->first();
        $client = $app->getClient();

        $data = $this->getFieldsToSet($link, $issue);

        $taskList = $this->touchTasklistFromName($link->teamwork_default_list);

        $taskData = $client->tasklist(intval($taskList['id']))->createTask($data);
        $data['id'] = $taskData['id'];

        // Also write a comment with the jira link.
        $client->comments()->create('tasks', $taskData['id'], [
            'body' => $link->jiraProject->getIssueUrl($issue)
        ]);

        return new Task($data);
    }

    /**
     * @param JiraTeamworkLink $link
     * @param Issue $issue
     * @param Task $task
     */
    protected function updateFromJira(JiraTeamworkLink $link, Issue $issue, Task $task)
    {
        /** @var App $app */
        $app = $this->company->apps->first();
        $client = $app->getClient();

        $data = $this->getFieldsToSet($link, $issue);

        // Check if data actually changed (otherwise don't update)
        if (!$task->haveFieldsChanged($data)) {
            $this->info('Data hasn\'t changed... not editing.');
            return;
        }

        $result = $client
            ->task($task->getId())
            ->edit($data);
    }

    /**
     * @param JiraTeamworkLink $link
     * @param Issue $issue
     * @return array
     */
    protected function getFieldsToSet(JiraTeamworkLink $link, Issue $issue)
    {
        $allowedFields = [
            'estimated-minutes' => 'int'
        ];

        $update = [];

        $key = $this->getIssueKey($issue);
        $update['content'] = $key . ' ' . $issue->getSummary();

        /** @var CalculatedField $calculatedField */
        foreach ($link->calculatedFields as $calculatedField) {
            $result = $calculatedField->evaluate($link->jiraProject, $issue);
            if ($result !== null) {

                if (!isset($allowedFields[$calculatedField->target_field])) {
                    continue;
                }

                switch ($allowedFields[$calculatedField->target_field]) {
                    case 'int':
                        $update[$calculatedField->target_field] = intval($result);
                        break;

                    default:
                        $update[$calculatedField->target_field] = $result;
                        break;
                }
            }
        }

        return $update;
    }

    /**
     * @param Issue $issue
     * @return string
     */
    protected function getIssueKey(Issue $issue)
    {
        return '[' . $issue->getKey() . ']';
    }

    /**
     * @return array
     */
    protected function loadTaskLists()
    {
        if (isset($this->tasklists)) {
            return $this->tasklists;
        }

        $client = $this->getClient();
        $tasklists = $client->project($this->teamwork_id)->tasklists([
            'status' => 'all',
            'complete' => true
        ]);

        $this->tasklists = [];
        foreach ($tasklists['tasklists'] as $list) {
            $this->tasklists[] = $list;
        }
        return $this->tasklists;
    }

    /**
     * Return the string that will be placed in front of all logs.
     * @return string
     */
    protected function getLogPrefix()
    {
        return '[Teamwork:' . $this->teamwork_id . ']';
    }

    /**
     * @return mixed
     */
    protected function getClient()
    {
        /** @var App $app */
        $app = $this->company->apps->first();
        return $app->getClient();
    }
}