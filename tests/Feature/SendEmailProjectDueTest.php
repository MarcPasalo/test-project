<?php

use App\Jobs\SendMailProjectDue;
use App\Mail\ProjectsDueOneDay;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

beforeEach(function () {
    Mail::fake();

    $this->actingAs($this->owner = User::factory()->withPersonalTeam()->create());

    $this->owner->currentTeam->users()->attach(
        $this->admin = User::factory()->create([
            'current_team_id' => $this->owner->currentTeam->id
        ]), ['role' => 'admin']
    );

    //Due in one day
    $this->first_project_details = [
        'title' => 'First Project',
        'status' => 'in_progress',
        'due_date' => Carbon::now()->addDay()->startOfDay(),
        'team_id' => $this->owner->currentTeam->id
    ];

    //Due in one day but completed
    $this->second_project_details = [
        'title' => 'Second Project',
        'status' => 'completed',
        'due_date' => Carbon::now()->addDay()->endOfDay(),
        'team_id' => $this->owner->currentTeam->id
    ];

    // Not due in one day
    $this->third_project_details = [
        'title' => 'Third Project',
        'status' => 'in_progress',
        'due_date' => Carbon::now()->addDays(2), 
        'team_id' => $this->owner->currentTeam->id
    ];

    $this->owner->currentTeam->projects()->create($this->first_project_details);

    $this->owner->currentTeam->projects()->create($this->second_project_details);

    $this->owner->currentTeam->projects()->create($this->third_project_details);
});

test('the scheduled job should be pushed', function () {
    Queue::fake();

    SendMailProjectDue::dispatch();

    Queue::assertPushed(SendMailProjectDue::class);
});

test('emails should be sent when the job is dispatched', function () {
    SendMailProjectDue::dispatch();

    $adminsAndOwners = User::whereHas('teams', function ($query) {
        $query->where('team_user.role', 'admin')
              ->orWhereColumn('teams.user_id', 'users.id'); 
    })->pluck('email');

    Mail::assertSent(ProjectsDueOneDay::class, function ($mail) use ($adminsAndOwners) {
        return $mail->hasTo($adminsAndOwners)
            && $mail->projects->contains('title', 'First Project')
            && (! $mail->projects->contains('title', 'Second Project'))
            && (! $mail->projects->contains('title', 'Third Project')); 
    });
});