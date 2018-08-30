<?php

namespace App;

use App\JIRA\Issue;
use App\JIRA\Project;
use Illuminate\Database\Eloquent\Model;
use RR\Shunt\Context;
use RR\Shunt\Parser;

/**
 * Class CalculatedField
 * @package App
 */
class CalculatedField extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jiraTeamworkLink()
    {
        return $this->belongsTo(JiraTeamworkLink::class, 'link_id');
    }

    /**
     * Check if provided formula is valid.
     * @return bool
     */
    public function isValidFormula()
    {
        // let's fake an issue
        $project = $this->jiraTeamworkLink->jiraProject;
        $issue = new Issue([
            'id' => -1,
            'key' => 'DEBUG-1',
            'fields' => [
                'summary' => 'Debug task to validate formula'
            ]
        ]);

        try {
            $this->evaluate($project, $issue);
            return true;
        } catch (\Exception $e) {

            \Log::info($e);
            return false;
        }
    }

    /**
     * @param Project $project
     * @param Issue $issue
     * @return mixed
     * @throws \Exception
     */
    public function evaluate(Project $project, Issue $issue)
    {
        $ctx = new Context();

        // Register a getter for field values
        $ctx->def('field', function($id) use ($project, $issue) {

            $field = $project->getFields()->where('id', $id)->first();
            if (!$field) {
                throw new \InvalidArgumentException("Could not find field " . $id);
            }

            return $issue->getField($field->key);
        });

        $equation = $this->formula;
        $value = Parser::parse($equation, $ctx);

        \Log::info(
            '[JIRA:issues:' . $issue->getKey() . ':' . $this->id . '] ' .
            $this->target_field . ' = ' . $equation . ' = ' . $value
        );

        return $value;
    }
}
