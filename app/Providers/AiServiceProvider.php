<?php

namespace App\Providers;

use App\Domains\Chat\Ai\Services\AiToolRegistry;
use App\Domains\Chat\Ai\Tools\AddProjectStatusTool;
use App\Domains\Chat\Ai\Tools\ArchiveTaskTool;
use App\Domains\Chat\Ai\Tools\AskUserChoiceTool;
use App\Domains\Chat\Ai\Tools\AssignTaskTool;
use App\Domains\Chat\Ai\Tools\AttachImageToTaskTool;
use App\Domains\Chat\Ai\Tools\BulkArchiveTasksTool;
use App\Domains\Chat\Ai\Tools\BulkAssignTasksTool;
use App\Domains\Chat\Ai\Tools\BulkDeleteTasksTool;
use App\Domains\Chat\Ai\Tools\BulkMoveTasksToStatusTool;
use App\Domains\Chat\Ai\Tools\CreateEventTool;
use App\Domains\Chat\Ai\Tools\CreateProjectTool;
use App\Domains\Chat\Ai\Tools\CreateTaskTool;
use App\Domains\Chat\Ai\Tools\DeleteEventTool;
use App\Domains\Chat\Ai\Tools\DeleteProjectStatusTool;
use App\Domains\Chat\Ai\Tools\DeleteProjectTool;
use App\Domains\Chat\Ai\Tools\DeleteTaskTool;
use App\Domains\Chat\Ai\Tools\FindUserByEmailTool;
use App\Domains\Chat\Ai\Tools\ForgetTool;
use App\Domains\Chat\Ai\Tools\GetProjectOverviewTool;
use App\Domains\Chat\Ai\Tools\GetTaskTool;
use App\Domains\Chat\Ai\Tools\GetTimeSummaryTool;
use App\Domains\Chat\Ai\Tools\ListEventsTool;
use App\Domains\Chat\Ai\Tools\ListMemoriesTool;
use App\Domains\Chat\Ai\Tools\ListMyOpenTasksTool;
use App\Domains\Chat\Ai\Tools\ListOverdueTasksTool;
use App\Domains\Chat\Ai\Tools\ListProjectsTool;
use App\Domains\Chat\Ai\Tools\ListTasksTool;
use App\Domains\Chat\Ai\Tools\ListTeamMembersTool;
use App\Domains\Chat\Ai\Tools\LogManualTimeTool;
use App\Domains\Chat\Ai\Tools\MoveTaskToStatusTool;
use App\Domains\Chat\Ai\Tools\PlanTool;
use App\Domains\Chat\Ai\Tools\RecentActivityTool;
use App\Domains\Chat\Ai\Tools\RememberTool;
use App\Domains\Chat\Ai\Tools\RenameProjectStatusTool;
use App\Domains\Chat\Ai\Tools\RenameProjectTool;
use App\Domains\Chat\Ai\Tools\SemanticSearchTool;
use App\Domains\Chat\Ai\Tools\StartTimerTool;
use App\Domains\Chat\Ai\Tools\StopTimerTool;
use App\Domains\Chat\Ai\Tools\UnarchiveTaskTool;
use App\Domains\Chat\Ai\Tools\UnassignTaskTool;
use App\Domains\Chat\Ai\Tools\UpdateEventTool;
use App\Domains\Chat\Ai\Tools\UpdateTaskDatesTool;
use App\Domains\Chat\Ai\Tools\UpdateTaskDescriptionTool;
use App\Domains\Chat\Ai\Tools\UpdateTaskNameTool;
use App\Domains\Chat\Ai\Tools\UpdateTaskPriorityTool;
use App\Domains\Chat\Ai\Tools\WhoIsWorkingOnTool;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiToolRegistry::class, function (Application $app): AiToolRegistry {
            $registry = new AiToolRegistry;

            // Existing confirm_write + structural tools.
            $registry->register($app->make(CreateTaskTool::class));
            $registry->register($app->make(CreateProjectTool::class));
            $registry->register($app->make(AskUserChoiceTool::class));
            $registry->register($app->make(PlanTool::class));

            // Phase 1D — auto_read tools that give the assistant workspace vision.
            $registry->register($app->make(ListTasksTool::class));
            $registry->register($app->make(GetTaskTool::class));
            $registry->register($app->make(ListMyOpenTasksTool::class));
            $registry->register($app->make(ListOverdueTasksTool::class));
            $registry->register($app->make(ListProjectsTool::class));
            $registry->register($app->make(GetProjectOverviewTool::class));
            $registry->register($app->make(ListTeamMembersTool::class));
            $registry->register($app->make(WhoIsWorkingOnTool::class));
            $registry->register($app->make(RecentActivityTool::class));
            $registry->register($app->make(FindUserByEmailTool::class));
            $registry->register($app->make(GetTimeSummaryTool::class));
            $registry->register($app->make(SemanticSearchTool::class));

            // Phase 1E — single-target confirm_write tools that let the assistant
            // mutate state through the existing card → Apply pattern.
            $registry->register($app->make(UpdateTaskNameTool::class));
            $registry->register($app->make(UpdateTaskDescriptionTool::class));
            $registry->register($app->make(UpdateTaskPriorityTool::class));
            $registry->register($app->make(UpdateTaskDatesTool::class));
            $registry->register($app->make(MoveTaskToStatusTool::class));
            $registry->register($app->make(AttachImageToTaskTool::class));
            $registry->register($app->make(AssignTaskTool::class));
            $registry->register($app->make(UnassignTaskTool::class));
            $registry->register($app->make(ArchiveTaskTool::class));
            $registry->register($app->make(UnarchiveTaskTool::class));
            $registry->register($app->make(StartTimerTool::class));
            $registry->register($app->make(StopTimerTool::class));
            $registry->register($app->make(LogManualTimeTool::class));
            $registry->register($app->make(RenameProjectTool::class));
            $registry->register($app->make(AddProjectStatusTool::class));
            $registry->register($app->make(RenameProjectStatusTool::class));
            $registry->register($app->make(DeleteProjectStatusTool::class));

            // Phase 1F — bulk + destructive tools. The presenter renders these
            // through the bulk-write / destructive card variants so the user
            // always reviews exactly what's about to change.
            $registry->register($app->make(BulkMoveTasksToStatusTool::class));
            $registry->register($app->make(BulkAssignTasksTool::class));
            $registry->register($app->make(BulkArchiveTasksTool::class));
            $registry->register($app->make(BulkDeleteTasksTool::class));
            $registry->register($app->make(DeleteTaskTool::class));
            $registry->register($app->make(DeleteProjectTool::class));

            // Calendar events — full CRUD for the user's personal calendar.
            $registry->register($app->make(CreateEventTool::class));
            $registry->register($app->make(ListEventsTool::class));
            $registry->register($app->make(UpdateEventTool::class));
            $registry->register($app->make(DeleteEventTool::class));

            // Phase 4 — memory tools. The assistant persists / recalls / forgets
            // user and team preferences so future sessions stay informed.
            $registry->register($app->make(RememberTool::class));
            $registry->register($app->make(ForgetTool::class));
            $registry->register($app->make(ListMemoriesTool::class));

            return $registry;
        });
    }
}
