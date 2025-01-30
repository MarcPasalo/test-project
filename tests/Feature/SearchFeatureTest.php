<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->actingAs($this->owner = User::factory()->withPersonalTeam()->create());

    $this->project_details = [
        'title' => 'some title',
        'status' => 'in_progress',
        'due_date' => '2025-02-19',
        'team_id' => $this->owner->currentTeam->id
    ];

    $this->project = $this->owner->currentTeam->projects()->create($this->project_details);

    $this->task_details = [
        'title' => 'some title',
        'description' => 'some description',
        'status' => 'in_progress',
        'priority' => 'low',
        'completion_date' => '2025-02-19',
        'project_id' => $this->project->id
    ];

    $this->task = $this->project->tasks()->create($this->task_details);

});

test('user can search on projects', function () {
    $search = $this->task_details['title'];

    $response = $this->actingAs($this->owner)->get(route('projects.index', ['search' => $search]));

    $response->assertStatus(200);
    $response->assertSee($search);
});

test('user can search on tasks', function () {
    $search = $this->task_details['title'];

    $response = $this->actingAs($this->owner)->get(route('tasks.show', ['task' => $this->task->id, 'search' => $search]));

    $response->assertStatus(200);
    $response->assertSee($search);
});