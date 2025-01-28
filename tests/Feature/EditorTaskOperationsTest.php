<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->actingAs($this->owner = User::factory()->withPersonalTeam()->create());

    $this->owner->currentTeam->users()->attach(
        $this->editor = User::factory()->create([
            'current_team_id' => $this->owner->currentTeam->id
        ]), ['role' => 'editor']
    );

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

test('editor can view all tasks', function () {

    $this->actingAs($this->editor)
        ->get(route('projects.tasks.index', $this->project->id))
        ->assertStatus(200);
});

test('editor can view a task', function () {

    $this->actingAs($this->editor)
        ->get(route('tasks.show', ['task' => $this->task->id]))
        ->assertStatus(200);
});

test('editor can create a task', function () {

    $task_details = [
        'title' => 'some title',
        'description' => 'some description',
        'status' => 'in_progress',
        'priority' => 'low',
        'completion_date' => '2025-02-19',
        'project_id' => $this->project->id
    ];

    $this->actingAs($this->editor)
        ->post(route('projects.tasks.store', ['project' => $this->project->id]), $task_details)
        ->assertRedirect(route('projects.show', ['project' => $this->project->id]));
    
    expect($this->project->fresh()->tasks)->toHaveCount(2);
});


test('editor can update a task', function () {

    $updated_task_details = [
        'title' => 'edited task title',
        'description' => 'some description',
        'status' => 'in_progress',
        'priority' => 'low',
        'completion_date' => '2025-02-19',
        'project_id' => $this->project->id
    ];

    $this->actingAs($this->editor)
        ->put(route('tasks.update', $this->task->id), $updated_task_details)
        ->assertRedirect(route('projects.show', ['project' => $this->project->id]));

    expect(Task::where('id', $this->task->id)->value('title'))->toEqual($updated_task_details['title']);

});

test('editor cannot delete a task', function () {

    $this->actingAs($this->editor)
        ->delete(route('tasks.destroy', ['task' => $this->task->id]))
        ->assertStatus(403);
    
    expect($this->project->fresh()->tasks)->toHaveCount(1);
});
