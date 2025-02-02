<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ProjectsDueOneDay;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendMailProjectDue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() 
    {
        // 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {  
        $owner = User::whereIn('id', Team::pluck('user_id'))->pluck('email'); 
        $admins = User::whereHas('teams', function ($query) {
            $query->where('team_user.role', 'admin'); 
        })->pluck('email');

        $projects = Project::whereBetween('due_date', [
            Carbon::now()->addDay()->startOfDay(),
            Carbon::now()->addDay()->endOfDay()
        ])->where('status', '!=' , 'completed')->get();
            
        if ($projects->isNotEmpty()) {
            Mail::to($owner, $admins)->send(new ProjectsDueOneDay($projects));
        }
    }
}
