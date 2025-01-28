<?php

use App\Models\Project;
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

});

test('editor can view all projects', function () {

    $this->actingAs($this->editor)
        ->get(route('projects.index'))
        ->assertStatus(200);
});

test('editor can view a project', function () {

    $this->actingAs($this->editor)
        ->get(route('projects.show', $this->project->id))
        ->assertStatus(200);
});

test('editor can create a project', function () {

    $project_details = [
        'title' => 'another project',
        'status' => 'in_progress',
        'due_date' => '2025-03-19',
        'team_id' => $this->editor->currentTeam->id
    ];

    $this->actingAs($this->editor)
        ->post(route('projects.store', $project_details))
        ->assertRedirect(route('projects.index'));
    
    expect($this->editor->currentTeam->projects->fresh())->toHaveCount(2);
});


test('editor can update a project', function () {

    $updated_project_details = [
        'title' => 'edited project title',
        'status' => 'in_progress',
        'due_date' => '2025-03-19',
        'team_id' => $this->editor->currentTeam->id
    ];

    $this->actingAs($this->editor)
        ->put(route('projects.update', $this->project->id), $updated_project_details)
        ->assertRedirect(route('projects.show', $this->project->id));

    expect(Project::where('id', $this->project->id)->value('title'))->toEqual($updated_project_details['title']);

});

test('editor cannot delete a project', function () {

    $this->actingAs($this->editor)
        ->delete(route('projects.destroy', $this->project->id))
        ->assertStatus(403);
    
    expect($this->editor->currentTeam->fresh()->projects)->toHaveCount(1);
});
