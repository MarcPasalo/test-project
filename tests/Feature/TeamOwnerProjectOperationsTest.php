<?php

use App\Models\Project;
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

});

test('team owner can view all projects', function () {

    $this->actingAs($this->owner)
        ->get(route('projects.index', $this->project))
        ->assertStatus(200);
});

test('team owner can view a project', function () {

    $this->actingAs($this->owner)
        ->get(route('projects.show', $this->project->id))
        ->assertStatus(200);
});

test('team owner can create a project', function () {

    $project_details = [
        'title' => 'another project',
        'status' => 'in_progress',
        'due_date' => '2025-03-19',
        'team_id' => $this->owner->currentTeam->id
    ];

    $this->actingAs($this->owner)
        ->post(route('projects.store', $project_details))
        ->assertRedirect(route('projects.index'));
    
    expect($this->owner->currentTeam->projects->fresh())->toHaveCount(2);
});


test('team owner can update a project', function () {

    $updated_project_details = [
        'title' => 'edited project title',
        'status' => 'in_progress',
        'due_date' => '2025-03-19',
        'team_id' => $this->owner->currentTeam->id
    ];

    $this->actingAs($this->owner)
        ->put(route('projects.update', $this->project->id), $updated_project_details)
        ->assertRedirect(route('projects.show', $this->project->id));

    expect(Project::where('id', $this->project->id)->value('title'))->toEqual($updated_project_details['title']);

});

test('team owner can delete a project', function () {

    $this->actingAs($this->owner)
        ->delete(route('projects.destroy', $this->project->id))
        ->assertRedirect(route('projects.index'));

    expect($this->owner->currentTeam->fresh()->projects)->toHaveCount(0);
});
