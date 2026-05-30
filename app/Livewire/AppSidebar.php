<?php

namespace App\Livewire;

use App\Domains\Calendar\DTOs\AgendaRow;
use App\Domains\Calendar\Services\ListTodayAgendaService;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\DeleteChatService;
use App\Domains\Chat\Services\ListUserChatsService;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\CreateProjectService;
use App\Domains\Projects\Services\CreateProjectStatusService;
use App\Domains\Projects\Services\DeleteProjectService;
use App\Domains\Projects\Services\DeleteProjectStatusService;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Projects\Services\ListProjectStatusesForProjectService;
use App\Domains\Projects\Services\RenameProjectStatusService;
use App\Domains\Projects\Services\ReorderProjectStatusesService;
use App\Domains\Projects\Services\UpdateProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Livewire\Concerns\ResolvesCodexState;
use App\Support\Concerns\DispatchesDashyUi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AppSidebar extends Component
{
    use DispatchesDashyUi;
    use ResolvesCodexState;
    use WithFileUploads;

    public ?int $confirmDeleteChatId = null;

    public ?int $activeChatId = null;

    public bool $isChatRoute = false;

    public string $activeSegment = '';

    public ?int $activeProjectId = null;

    /** @var array<int, int> */
    public array $expandedTeams = [];

    public ?int $createProjectTeamId = null;

    public string $newProjectName = '';

    public string $newProjectDescription = '';

    public $newProjectLogo = null;

    public ?int $confirmDeleteProjectId = null;

    public ?int $projectSettingsId = null;

    public string $editProjectName = '';

    public string $editProjectDescription = '';

    public $editProjectLogo = null;

    public ?string $editProjectCurrentLogo = null;

    /** @var list<array{cid:string,category:string,name:string}> */
    public array $bufferedStatuses = [];

    /** @var array<string,string> */
    public array $pendingStatusName = [
        'not_started' => '',
        'active' => '',
        'done' => '',
        'closed' => '',
    ];

    public function mount(): void
    {
        $this->isChatRoute = request()->routeIs('chat*');
        $this->activeSegment = match (true) {
            request()->routeIs('chat*') => 'chat',
            request()->routeIs('calendar') => 'calendar',
            request()->routeIs('tasks*') => 'tasks',
            default => '',
        };

        $routeChat = request()->route('chat');
        $this->activeChatId = $routeChat !== null ? (int) $routeChat : null;

        $routeProject = request()->route('project');
        $this->activeProjectId = $routeProject !== null ? (int) $routeProject : null;

        if ($this->activeProjectId !== null) {
            foreach ($this->projectsByTeamId as $teamId => $projects) {
                if ($projects->contains('id', $this->activeProjectId)
                    && ! in_array((int) $teamId, $this->expandedTeams, true)) {
                    $this->expandedTeams[] = (int) $teamId;
                }
            }
        }
    }

    /**
     * @return Collection<int, Chat>
     */
    #[Computed]
    public function chats(): Collection
    {
        return app(ListUserChatsService::class)->execute(Auth::user());
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function teams(): Collection
    {
        if ($this->activeSegment !== 'tasks') {
            return new Collection;
        }

        return app(ListTeamsForUserService::class)->execute(Auth::user());
    }

    /**
     * @return list<AgendaRow>
     */
    #[Computed]
    public function todayAgenda(): array
    {
        return app(ListTodayAgendaService::class)->executeFor(
            Auth::user(),
            CarbonImmutable::now(),
        );
    }

    #[Computed]
    public function todayDateLabel(): string
    {
        return __('TODAY, :date', ['date' => mb_strtoupper(CarbonImmutable::now()->format('D j'))]);
    }

    public function toggleTeam(int $teamId): void
    {
        $index = array_search($teamId, $this->expandedTeams, true);

        if ($index === false) {
            $this->expandedTeams[] = $teamId;

            return;
        }

        unset($this->expandedTeams[$index]);
        $this->expandedTeams = array_values($this->expandedTeams);
    }

    #[On('open-create-project')]
    public function openCreateProject(int $teamId): void
    {
        $this->createProjectTeamId = $teamId;
        $this->resetCreateProjectForm();
        $this->openModal('create-project');
    }

    public function cancelCreateProject(): void
    {
        $this->resetCreateProjectForm();
    }

    public function createProject(CreateProjectService $service, FindTeamForUserService $teams): void
    {
        if ($this->createProjectTeamId === null) {
            return;
        }

        // Resolve + authorize the target team through the Teams domain rather
        // than querying the membership relation from the component (UI layer).
        $team = $teams->execute(Auth::user(), $this->createProjectTeamId);
        if ($team === null) {
            throw new ModelNotFoundException;
        }

        $service->execute(
            Auth::user(),
            $team,
            [
                'name' => $this->newProjectName,
                'description' => $this->newProjectDescription !== '' ? $this->newProjectDescription : null,
            ],
            $this->newProjectLogo,
            array_map(
                fn (array $entry) => ['category' => $entry['category'], 'name' => $entry['name']],
                $this->bufferedStatuses,
            ),
        );

        $this->resetCreateProjectForm();

        $this->closeModal('create-project');
        $this->toast('success', __('Project created.'));
        $this->dispatch('project-list-changed');
    }

    public function confirmDeleteProject(int $projectId): void
    {
        $this->confirmDeleteProjectId = $projectId;
        $this->openModal('confirm-project-deletion');
    }

    public function openProjectSettings(int $projectId): void
    {
        $project = $this->findLoadedProject($projectId);

        if ($project === null) {
            return;
        }

        $this->projectSettingsId = $projectId;
        $this->editProjectName = (string) $project->name;
        $this->editProjectDescription = (string) ($project->description ?? '');
        $this->editProjectLogo = null;
        $this->editProjectCurrentLogo = $project->logo;
        $this->resetErrorBag();

        $this->openModal('project-settings');
    }

    public function cancelProjectSettings(): void
    {
        $this->resetProjectSettingsForm();
    }

    public function updateProject(UpdateProjectService $service): void
    {
        if ($this->projectSettingsId === null) {
            return;
        }

        $service->execute(
            Auth::user(),
            $this->projectSettingsId,
            [
                'name' => $this->editProjectName,
                'description' => $this->editProjectDescription !== '' ? $this->editProjectDescription : null,
            ],
            $this->editProjectLogo,
        );

        $this->resetProjectSettingsForm();

        $this->closeModal('project-settings');
        $this->toast('success', __('Project updated.'));
        $this->dispatch('project-list-changed');
    }

    private function findLoadedProject(int $projectId): ?Project
    {
        foreach ($this->projectsByTeamId as $projects) {
            foreach ($projects as $project) {
                if ((int) $project->id === $projectId) {
                    return $project;
                }
            }
        }

        return null;
    }

    private function resetProjectSettingsForm(): void
    {
        $this->projectSettingsId = null;
        $this->editProjectName = '';
        $this->editProjectDescription = '';
        $this->editProjectLogo = null;
        $this->editProjectCurrentLogo = null;
        $this->resetPendingStatusInputs();
        $this->resetErrorBag();
    }

    public function cancelDeleteProject(): void
    {
        $this->confirmDeleteProjectId = null;
        $this->closeModal('confirm-project-deletion');
    }

    public function deleteProject(DeleteProjectService $service): void
    {
        if ($this->confirmDeleteProjectId === null) {
            return;
        }

        $service->execute(Auth::user(), $this->confirmDeleteProjectId);
        $this->confirmDeleteProjectId = null;

        $this->closeModal('confirm-project-deletion');
        $this->toast('success', __('Project deleted.'));
        $this->dispatch('project-list-changed');
    }

    /**
     * @return array<int, Collection<int, Project>>
     */
    #[Computed]
    public function projectsByTeamId(): array
    {
        if ($this->activeSegment !== 'tasks') {
            return [];
        }

        return app(ListProjectsForUserService::class)
            ->execute(Auth::user())
            ->groupBy('team_id')
            ->all();
    }

    public function canDeleteProjectsIn(Team $team): bool
    {
        $role = $team->pivot?->role ?? null;

        if ($role instanceof TeamRole) {
            return $role === TeamRole::Owner;
        }

        return $role === TeamRole::Owner->value;
    }

    private function resetCreateProjectForm(): void
    {
        $this->newProjectName = '';
        $this->newProjectDescription = '';
        $this->newProjectLogo = null;
        $this->bufferedStatuses = [];
        $this->resetPendingStatusInputs();
        $this->resetErrorBag();
    }

    private function resetPendingStatusInputs(): void
    {
        foreach ($this->pendingStatusName as $key => $_value) {
            $this->pendingStatusName[$key] = '';
        }
    }

    public function addBufferedStatus(string $category): void
    {
        if (ProjectStatusCategory::tryFrom($category) === null) {
            return;
        }

        $name = trim($this->pendingStatusName[$category] ?? '');

        if ($name === '') {
            return;
        }

        $this->bufferedStatuses[] = [
            'cid' => (string) Str::random(8),
            'category' => $category,
            'name' => $name,
        ];

        $this->pendingStatusName[$category] = '';
    }

    public function renameBufferedStatus(string $cid, string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            return;
        }

        foreach ($this->bufferedStatuses as $index => $entry) {
            if ($entry['cid'] === $cid) {
                $this->bufferedStatuses[$index]['name'] = $name;

                return;
            }
        }
    }

    public function deleteBufferedStatus(string $cid): void
    {
        $this->bufferedStatuses = array_values(array_filter(
            $this->bufferedStatuses,
            fn (array $entry) => $entry['cid'] !== $cid,
        ));
    }

    /**
     * @param  list<string>  $orderedCids
     */
    public function reorderBufferedStatuses(string $category, array $orderedCids): void
    {
        if (ProjectStatusCategory::tryFrom($category) === null) {
            return;
        }

        $byCid = [];
        foreach ($this->bufferedStatuses as $entry) {
            $byCid[$entry['cid']] = $entry;
        }

        $reordered = [];

        // First, append the items in the requested order (only those in this category).
        foreach ($orderedCids as $cid) {
            if (isset($byCid[$cid]) && $byCid[$cid]['category'] === $category) {
                $reordered[] = $byCid[$cid];
                unset($byCid[$cid]);
            }
        }

        // Then preserve every other entry (other categories or unmatched cids) in their original order.
        foreach ($this->bufferedStatuses as $entry) {
            if (isset($byCid[$entry['cid']])) {
                $reordered[] = $entry;
            }
        }

        $this->bufferedStatuses = $reordered;
    }

    public function addStatus(string $category, CreateProjectStatusService $service): void
    {
        if ($this->projectSettingsId === null) {
            return;
        }

        $cat = ProjectStatusCategory::tryFrom($category);
        if ($cat === null) {
            return;
        }

        $name = trim($this->pendingStatusName[$category] ?? '');

        if ($name === '') {
            return;
        }

        $service->execute(Auth::user(), $this->projectSettingsId, $cat, $name);
        $this->pendingStatusName[$category] = '';
    }

    public function renameStatus(int $statusId, string $name, RenameProjectStatusService $service): void
    {
        $name = trim($name);

        if ($name === '') {
            return;
        }

        $service->execute(Auth::user(), $statusId, $name);
    }

    public function deleteStatus(int $statusId, DeleteProjectStatusService $service): void
    {
        $service->execute(Auth::user(), $statusId);
    }

    /**
     * @param  list<int|string>  $orderedIds
     */
    public function reorderStatuses(string $category, array $orderedIds, ReorderProjectStatusesService $service): void
    {
        if ($this->projectSettingsId === null) {
            return;
        }

        $cat = ProjectStatusCategory::tryFrom($category);
        if ($cat === null) {
            return;
        }

        $service->execute(Auth::user(), $this->projectSettingsId, $cat, $orderedIds);
    }

    /**
     * @return array<string, \Illuminate\Support\Collection>
     */
    #[Computed]
    public function editProjectStatusesByCategory(): array
    {
        if ($this->projectSettingsId === null) {
            return [];
        }

        $project = $this->findLoadedProject($this->projectSettingsId);
        if ($project === null) {
            return [];
        }

        return app(ListProjectStatusesForProjectService::class)
            ->execute(Auth::user(), $project)
            ->groupBy(fn ($status) => $status->category->value)
            ->all();
    }

    /**
     * @return array<string, list<array{cid:string,category:string,name:string}>>
     */
    public function bufferedStatusesByCategory(): array
    {
        $grouped = [
            'not_started' => [],
            'active' => [],
            'done' => [],
            'closed' => [],
        ];

        foreach ($this->bufferedStatuses as $entry) {
            if (isset($grouped[$entry['category']])) {
                $grouped[$entry['category']][] = $entry;
            }
        }

        return $grouped;
    }

    #[On('project-list-changed')]
    public function refreshProjects(): void
    {
        // Empty body — listener triggers re-render so projectsByTeamId re-evaluates.
    }

    public function startNewChat(): void
    {
        $this->redirect(route('chat'), navigate: true);
    }

    public function confirmDeleteChat(int $chatId): void
    {
        $this->confirmDeleteChatId = $chatId;
        $this->openModal('confirm-chat-deletion');
    }

    public function cancelDeleteChat(): void
    {
        $this->confirmDeleteChatId = null;
        $this->closeModal('confirm-chat-deletion');
    }

    public function deleteChat(DeleteChatService $service): void
    {
        if ($this->confirmDeleteChatId === null) {
            return;
        }

        $deletedChatId = $this->confirmDeleteChatId;

        $service->execute(Auth::user(), $deletedChatId);
        $this->confirmDeleteChatId = null;

        $this->closeModal('confirm-chat-deletion');
        $this->toast('success', __('Chat deleted.'));

        if ($this->activeChatId === $deletedChatId) {
            $this->redirect(route('chat'), navigate: true);

            return;
        }

        $this->dispatch('chat-list-changed');
    }

    #[On('chat-list-changed')]
    public function refresh(): void
    {
        // Empty body — listener triggers re-render so the chats computed re-evaluates.
    }

    public function render()
    {
        return view('livewire.app-sidebar');
    }
}
