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

test('team owner can view all tasks', function () {

    $this->actingAs($this->owner)
        ->get(route('projects.tasks.index', $this->project->id))
        ->assertStatus(200);
});

test('team owner can view a task', function () {

    $this->actingAs($this->owner)
        ->get(route('tasks.show', ['task' => $this->task->id]))
        ->assertStatus(200);
});

test('team owner can create a task', function () {

    $task_details = [
        'title' => 'some title',
        'description' => 'some description',
        'status' => 'in_progress',
        'priority' => 'low',
        'completion_date' => '2025-02-19',
        'project_id' => $this->project->id
    ];

    $this->actingAs($this->owner)
        ->post(route('projects.tasks.store', ['project' => $this->project->id]), $task_details)
        ->assertRedirect(route('projects.show', ['project' => $this->project->id]));
    
    expect($this->project->fresh()->tasks)->toHaveCount(2);
});


test('team owner can update a task', function () {

    $updated_task_details = [
        'title' => 'edited task title',
        'description' => 'some description',
        'status' => 'in_progress',
        'priority' => 'low',
        'completion_date' => '2025-02-19',
        'project_id' => $this->project->id
    ];

    $this->actingAs($this->owner)
        ->put(route('tasks.update', $this->task->id), $updated_task_details)
        ->assertRedirect(route('projects.show', ['project' => $this->project->id]));

    expect(Task::where('id', $this->task->id)->value('title'))->toEqual($updated_task_details['title']);

});

test('team owner can delete a task', function () {

    $this->actingAs($this->owner)
        ->delete(route('tasks.destroy', ['task' => $this->task->id]))
        ->assertRedirect(route('projects.show', ['project' => $this->project->id]));

    expect($this->project->fresh()->tasks)->toHaveCount(0);
});
