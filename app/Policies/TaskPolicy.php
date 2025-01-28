<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->belongsToTeam($task->project->team);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->belongsToTeam($task->project->team);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->belongsToTeam($task->project->team) && (! Auth::user()->hasTeamRole(Auth::user()->currentTeam, 'editor'));
    }
}
