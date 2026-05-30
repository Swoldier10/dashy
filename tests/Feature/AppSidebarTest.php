<?php

namespace Tests\Feature;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AppSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_for_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('app-sidebar')->assertOk();
    }

    public function test_sidebar_user_card_opens_settings_modal(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeHtml('data-test="sidebar-user-menu"');
        $response->assertSeeHtml("\$store.modals.open('settings')");
    }

    public function test_mobile_shell_renders_topbar_and_user_menu_trigger(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSee('data-test="mobile-topbar"', escape: false);
        $response->assertSee('data-test="mobile-user-menu"', escape: false);
        // Primary nav lives in the floating bottom bar; the desktop sidebar nav
        // is also present in the rendered HTML.
        $response->assertSeeTextInOrder(['Chat', 'Calendar', 'Tasks']);
    }

    public function test_mobile_recents_strip_renders_chats_only_on_chat_route(): void
    {
        $user = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'Mobile chat A']);
        Chat::create(['user_id' => $user->id, 'title' => 'Mobile chat B']);
        $this->actingAs($user);

        $chatResponse = $this->get(route('chat'));
        $chatResponse->assertOk();
        $chatResponse->assertSee('data-test="mobile-recents"', escape: false);
        $chatResponse->assertSeeText('Mobile chat A');
        $chatResponse->assertSeeText('Mobile chat B');

        $calendarResponse = $this->get(route('calendar'));
        $calendarResponse->assertOk();
        $calendarResponse->assertDontSee('data-test="mobile-recents"', escape: false);
    }

    public function test_mobile_user_menu_trigger_opens_settings_modal(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        // The mobile topbar avatar button replaces the old dropdown and opens
        // the settings modal, which itself contains the Log out form.
        $response->assertSeeHtml('data-test="mobile-user-menu"');
        $response->assertSeeHtml("\$store.modals.open('settings')");
        $response->assertSeeText('Log out');
    }

    public function test_chat_route_marks_chat_segment_active_and_keeps_recents_visible(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('Chat');
        $response->assertSeeText('Recent chats');
        $response->assertSeeText('New chat');
    }

    public function test_calendar_route_hides_chat_only_sections(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('calendar'));

        $response->assertOk();
        $response->assertDontSeeText('Recent chats');
        $response->assertDontSeeText('New chat');
    }

    public function test_mount_sets_active_segment_from_route_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test('app-sidebar');

        // Default test request resolves to Livewire's update endpoint, so the
        // segment match returns ''; the component still renders cleanly without
        // crashing on an unknown route.
        $this->assertSame('', $component->get('activeSegment'));
        $this->assertFalse($component->get('isChatRoute'));
    }

    public function test_chats_computed_returns_only_current_users_chats(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'Mine']);
        Chat::create(['user_id' => $other->id, 'title' => 'Theirs']);
        $this->actingAs($user);

        $component = Livewire::test('app-sidebar');
        $chats = $component->instance()->chats;

        $this->assertCount(1, $chats);
        $this->assertSame('Mine', $chats->first()->title);
    }

    public function test_start_new_chat_redirects_to_chat_route(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('app-sidebar')
            ->call('startNewChat')
            ->assertRedirect(route('chat'));
    }

    public function test_confirm_then_delete_removes_chat_and_dispatches_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Doomed']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'bye']);

        Livewire::test('app-sidebar')
            ->call('confirmDeleteChat', $chat->id)
            ->assertSet('confirmDeleteChatId', $chat->id)
            ->call('deleteChat')
            ->assertSet('confirmDeleteChatId', null)
            ->assertNoRedirect()
            ->assertDispatched('chat-list-changed');

        $this->assertSame(0, Chat::count());
        $this->assertSame(0, Message::count());
    }

    public function test_deleting_currently_viewed_chat_redirects_to_chat_root(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Doomed']);

        Livewire::test('app-sidebar')
            ->set('activeChatId', $chat->id)
            ->call('confirmDeleteChat', $chat->id)
            ->call('deleteChat')
            ->assertRedirect(route('chat'));

        $this->assertSame(0, Chat::count());
    }

    public function test_cancel_delete_clears_pending_id(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $chat = Chat::create(['user_id' => $user->id]);

        Livewire::test('app-sidebar')
            ->call('confirmDeleteChat', $chat->id)
            ->call('cancelDeleteChat')
            ->assertSet('confirmDeleteChatId', null);

        $this->assertSame(1, Chat::count());
    }

    public function test_tasks_route_lists_users_teams_in_sidebar(): void
    {
        // Teams + projects moved out of AppSidebar into the tasks-page
        // workspace-sidebar (resources/views/livewire/tasks/partials/
        // workspace-sidebar.blade.php). We GET /tasks to render that surface.
        $user = User::factory()->create();
        $acme = Team::factory()->create(['name' => 'Acme Co']);
        $other = Team::factory()->create(['name' => 'Hidden Team']);
        $acme->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        $response = $this->get(route('tasks'));

        $response->assertOk();
        $response->assertSee('data-test="workspace-sidebar"', escape: false);
        $response->assertSeeText('Acme Co');
        $response->assertDontSeeText('Hidden Team');
    }

    public function test_chat_route_does_not_show_team_list(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Should Stay Hidden']);
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertDontSee('data-test="sidebar-teams"', escape: false);
        $response->assertDontSee('data-test="mobile-teams"', escape: false);
        $response->assertDontSeeText('Should Stay Hidden');
    }

    public function test_redesigned_sidebar_renders_today_and_user_card(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSee('data-test="sidebar-today"', escape: false);
        $response->assertSee('data-test="sidebar-user-menu"', escape: false);
        $response->assertSeeText('View all');
    }

    public function test_inbox_nav_item_is_not_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertDontSee('data-test="sidebar-nav-inbox"', escape: false);
        // The Inbox label may exist in unrelated UI; absence of the nav data-test
        // is the precise check.
    }

    public function test_today_date_label_uses_current_day(): void
    {
        Carbon::setTestNow('2026-05-16 10:00:00');

        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSeeText('TODAY, SAT 16');
    }

    public function test_teams_computed_returns_empty_when_segment_is_not_tasks(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        $component = Livewire::test('app-sidebar');

        $this->assertCount(0, $component->instance()->teams);
    }

    public function test_teams_computed_returns_users_teams_when_segment_is_tasks(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Visible']);
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        $component = Livewire::test('app-sidebar')->set('activeSegment', 'tasks');

        $names = $component->instance()->teams->pluck('name')->all();
        $this->assertContains('Visible', $names);
    }

    public function test_toggle_team_adds_then_removes_id(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('app-sidebar')
            ->call('toggleTeam', 42)
            ->assertSet('expandedTeams', [42])
            ->call('toggleTeam', 99)
            ->assertSet('expandedTeams', [42, 99])
            ->call('toggleTeam', 42)
            ->assertSet('expandedTeams', [99]);
    }

    public function test_open_create_project_sets_team_id(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('app-sidebar')
            ->call('openCreateProject', 7)
            ->assertSet('createProjectTeamId', 7);
    }

    public function test_open_create_project_event_listener_opens_modal_with_team_id(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('app-sidebar')
            ->dispatch('open-create-project', teamId: 42)
            ->assertSet('createProjectTeamId', 42);
    }

    public function test_create_project_makes_it_appear_in_sidebar(): void
    {
        // AppSidebar still owns the create-project Livewire flow + modal.
        // The visible project list moved to the tasks-page workspace-sidebar,
        // so the post-create "appears in sidebar" assertion is verified by
        // round-tripping through /tasks?team={id}.
        Storage::fake('public');
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openCreateProject', $team->id)
            ->set('newProjectName', 'Brand new project')
            ->set('newProjectDescription', 'Some description')
            ->call('createProject')
            ->assertSet('newProjectName', '')
            ->assertSet('createProjectTeamId', $team->id)
            ->assertDispatched('project-list-changed');

        $this->assertDatabaseHas('projects', [
            'team_id' => $team->id,
            'name' => 'Brand new project',
            'description' => 'Some description',
        ]);

        $this->get(route('tasks').'?team='.$team->id)
            ->assertOk()
            ->assertSeeText('Brand new project');
    }

    public function test_create_project_with_logo_stores_file_and_renders(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openCreateProject', $team->id)
            ->set('newProjectName', 'Logo project')
            ->set('newProjectLogo', UploadedFile::fake()->image('logo.png'))
            ->call('createProject');

        $this->assertCount(1, Storage::disk('public')->files("project-logos/{$team->id}"));
        $this->assertNotNull(Project::first()->logo);

        $this->get(route('tasks').'?team='.$team->id)
            ->assertOk()
            ->assertSeeText('Logo project');
    }

    public function test_create_project_validation_error_surfaces(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('openCreateProject', $team->id)
            ->set('newProjectName', '')
            ->call('createProject')
            ->assertHasErrors(['name']);

        $this->assertSame(0, Project::count());
    }

    public function test_owner_can_delete_project_and_it_disappears(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Doomed project']);
        $this->actingAs($user);

        // Project is visible in the workspace-sidebar before deletion.
        $this->get(route('tasks').'?team='.$team->id)->assertSeeText('Doomed project');

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('confirmDeleteProject', $project->id)
            ->assertSet('confirmDeleteProjectId', $project->id)
            ->call('deleteProject')
            ->assertSet('confirmDeleteProjectId', null)
            ->assertDispatched('project-list-changed');

        $this->assertSame(0, Project::count());

        // And gone from the workspace-sidebar afterwards.
        $this->get(route('tasks').'?team='.$team->id)->assertDontSeeText('Doomed project');
    }

    public function test_member_cannot_delete_or_edit_project(): void
    {
        // Project settings + deletion live behind ProjectPolicy::delete /
        // ProjectPolicy::update, both of which require Owner role. Members
        // calling the AppSidebar Livewire methods must be blocked by the
        // service-layer Gate check (rendered affordances were dropped in the
        // workspace-sidebar redesign, so there's no DOM toggle to assert).
        //
        // Livewire converts AuthorizationException into a 403-style response
        // in test mode rather than letting it bubble, so we assert enforcement
        // by checking the project is untouched after each attempt.
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Visible project']);
        $this->actingAs($user);

        // The project is still visible in the workspace-sidebar list — only
        // the destructive actions are gated.
        $this->get(route('tasks').'?team='.$team->id)->assertSeeText('Visible project');

        // Delete attempt: DeleteProjectService rejects non-owners.
        Livewire::test('app-sidebar')
            ->call('confirmDeleteProject', $project->id)
            ->call('deleteProject');
        $this->assertNotNull(Project::find($project->id));

        // Update attempt: UpdateProjectService rejects non-owners.
        Livewire::test('app-sidebar')
            ->call('openProjectSettings', $project->id)
            ->set('editProjectName', 'Renamed by member')
            ->set('editProjectDescription', 'should not stick')
            ->call('updateProject');
        $this->assertSame('Visible project', Project::find($project->id)->name);
    }

    public function test_owner_can_open_project_settings_modal(): void
    {
        // The settings modal still lives on the AppSidebar Livewire component;
        // opening it pre-fills the edit form with the current project values.
        // The visual entry point moved to the per-project workspace pages —
        // this test verifies the Livewire method contract.
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'Cog project',
            'description' => 'Cog desc',
        ]);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->assertSet('projectSettingsId', $project->id)
            ->assertSet('editProjectName', 'Cog project')
            ->assertSet('editProjectDescription', 'Cog desc');
    }

    public function test_owner_can_update_project_and_change_reflects_in_sidebar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'Old name',
            'description' => 'Old desc',
        ]);
        $this->actingAs($user);

        // Visible under the old name before rename.
        $this->get(route('tasks').'?team='.$team->id)->assertSeeText('Old name');

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->set('editProjectName', 'Renamed project')
            ->set('editProjectDescription', 'Renamed desc')
            ->call('updateProject')
            ->assertSet('projectSettingsId', null)
            ->assertSet('editProjectName', '')
            ->assertDispatched('project-list-changed');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Renamed project',
            'description' => 'Renamed desc',
        ]);

        // Workspace-sidebar shows the new name after the rename.
        $response = $this->get(route('tasks').'?team='.$team->id);
        $response->assertSeeText('Renamed project');
        $response->assertDontSeeText('Old name');
    }

    public function test_owner_can_replace_logo_via_settings(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->set('editProjectLogo', UploadedFile::fake()->image('new.png'))
            ->call('updateProject');

        $this->assertNotNull($project->fresh()->logo);
        $this->assertCount(1, Storage::disk('public')->files("project-logos/{$team->id}"));
    }

    public function test_settings_validation_error_keeps_modal_open(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Stay']);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->set('editProjectName', '')
            ->call('updateProject')
            ->assertHasErrors(['name'])
            ->assertSet('projectSettingsId', $project->id);

        $this->assertSame('Stay', $project->fresh()->name);
    }

    public function test_member_cannot_update_via_settings(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id, 'name' => 'Stay']);
        $this->actingAs($user);

        Livewire::test('app-sidebar')
            ->set('projectSettingsId', $project->id)
            ->set('editProjectName', 'Hacked')
            ->call('updateProject');

        $this->assertSame('Stay', $project->fresh()->name);
    }

    public function test_member_cannot_delete_via_method_call(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $this->actingAs($user);

        // Livewire wraps AuthorizationException into a 403 response under the
        // hood; the project must remain intact regardless.
        Livewire::test('app-sidebar')
            ->call('confirmDeleteProject', $project->id)
            ->call('deleteProject');

        $this->assertSame(1, Project::count());
    }

    public function test_projects_only_load_on_tasks_segment(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        Project::factory()->create(['team_id' => $team->id, 'name' => 'Hidden on chat']);
        $this->actingAs($user);

        $component = Livewire::test('app-sidebar');

        $this->assertSame('', $component->get('activeSegment'));
        $this->assertSame([], $component->instance()->projectsByTeamId);
    }

    private function ownerWithTeam(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($user);

        return [$user, $team];
    }

    public function test_buffered_status_added_then_persisted_on_create(): void
    {
        [, $team] = $this->ownerWithTeam();

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openCreateProject', $team->id)
            ->set('newProjectName', 'P')
            ->set('pendingStatusName.not_started', 'TODO')
            ->call('addBufferedStatus', 'not_started')
            ->set('pendingStatusName.not_started', 'BACKLOG')
            ->call('addBufferedStatus', 'not_started')
            ->set('pendingStatusName.active', 'IN PROGRESS')
            ->call('addBufferedStatus', 'active')
            ->call('createProject')
            ->assertSet('bufferedStatuses', []);

        $project = Project::firstWhere('name', 'P');
        $this->assertNotNull($project);

        $statuses = $project->statuses()->get();
        $this->assertCount(3, $statuses);

        $notStarted = $statuses->where('category', ProjectStatusCategory::NotStarted)->values();
        $this->assertSame(['TODO', 'BACKLOG'], $notStarted->pluck('name')->all());
        $this->assertSame([0, 1], $notStarted->pluck('position')->all());

        $active = $statuses->where('category', ProjectStatusCategory::Active)->values();
        $this->assertSame(['IN PROGRESS'], $active->pluck('name')->all());
    }

    public function test_buffered_rename_and_delete(): void
    {
        [, $team] = $this->ownerWithTeam();

        $component = Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('openCreateProject', $team->id)
            ->set('pendingStatusName.not_started', 'A')
            ->call('addBufferedStatus', 'not_started')
            ->set('pendingStatusName.not_started', 'B')
            ->call('addBufferedStatus', 'not_started');

        $cidA = $component->get('bufferedStatuses')[0]['cid'];
        $cidB = $component->get('bufferedStatuses')[1]['cid'];

        $component
            ->call('renameBufferedStatus', $cidA, 'A2')
            ->call('deleteBufferedStatus', $cidB);

        $buffer = $component->get('bufferedStatuses');
        $this->assertCount(1, $buffer);
        $this->assertSame('A2', $buffer[0]['name']);
    }

    public function test_buffered_reorder(): void
    {
        [, $team] = $this->ownerWithTeam();

        $component = Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('openCreateProject', $team->id)
            ->set('pendingStatusName.not_started', 'A')
            ->call('addBufferedStatus', 'not_started')
            ->set('pendingStatusName.not_started', 'B')
            ->call('addBufferedStatus', 'not_started')
            ->set('pendingStatusName.not_started', 'C')
            ->call('addBufferedStatus', 'not_started');

        $cids = array_column($component->get('bufferedStatuses'), 'cid');

        $component->call('reorderBufferedStatuses', 'not_started', [$cids[2], $cids[0], $cids[1]]);

        $names = array_column($component->get('bufferedStatuses'), 'name');
        $this->assertSame(['C', 'A', 'B'], $names);
    }

    public function test_edit_flow_add_status_persists(): void
    {
        [, $team] = $this->ownerWithTeam();
        $project = Project::factory()->create(['team_id' => $team->id]);

        Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->set('pendingStatusName.active', 'IN PROGRESS')
            ->call('addStatus', 'active');

        $this->assertDatabaseHas('project_statuses', [
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'IN PROGRESS',
            'position' => 0,
        ]);
    }

    public function test_edit_flow_rename_status(): void
    {
        [, $team] = $this->ownerWithTeam();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'OLD',
        ]);

        Livewire::test('app-sidebar')
            ->call('renameStatus', $status->id, 'NEW');

        $this->assertSame('NEW', $status->fresh()->name);
    }

    public function test_edit_flow_delete_status(): void
    {
        [, $team] = $this->ownerWithTeam();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        Livewire::test('app-sidebar')
            ->call('deleteStatus', $status->id);

        $this->assertSame(0, ProjectStatus::count());
    }

    public function test_edit_flow_reorder_status(): void
    {
        [, $team] = $this->ownerWithTeam();
        $project = Project::factory()->create(['team_id' => $team->id]);

        $a = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 0,
        ]);
        $b = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 1,
        ]);
        $c = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 2,
        ]);

        Livewire::test('app-sidebar')
            ->set('projectSettingsId', $project->id)
            ->call('reorderStatuses', 'active', [$c->id, $a->id, $b->id]);

        $this->assertSame(0, $c->fresh()->position);
        $this->assertSame(1, $a->fresh()->position);
        $this->assertSame(2, $b->fresh()->position);
    }

    public function test_opening_settings_renders_existing_statuses_in_modal(): void
    {
        [, $team] = $this->ownerWithTeam();
        $project = Project::factory()->create(['team_id' => $team->id]);

        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'BACKLOG',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'IN PROGRESS',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'name' => 'SHIPPED',
            'position' => 0,
        ]);

        $html = Livewire::test('app-sidebar')
            ->set('activeSegment', 'tasks')
            ->call('toggleTeam', $team->id)
            ->call('openProjectSettings', $project->id)
            ->html();

        // The modal must render the existing statuses inside the project-settings
        // status manager — regression guard for the dashy-modal x-show/<template x-if>
        // bug where dynamic content inside <template> never reached the live DOM.
        $managerStart = strpos($html, 'data-test="project-status-manager-edit"');
        $this->assertNotFalse($managerStart, 'Edit status manager block not found in rendered modal.');
        $managerHtml = substr($html, $managerStart);

        $this->assertStringContainsString('BACKLOG', $managerHtml);
        $this->assertStringContainsString('IN PROGRESS', $managerHtml);
        $this->assertStringContainsString('SHIPPED', $managerHtml);
    }

    public function test_dashy_modal_keeps_slot_in_live_dom(): void
    {
        // Regression guard: the dashy-modal component must not wrap its slot
        // content in <template x-if>. That hides dynamic content inside a
        // DocumentFragment that Livewire's morph can't traverse, which is what
        // broke the project-settings statuses. See the comment in modal.blade.php.
        $modal = file_get_contents(resource_path('views/components/dashy/modal.blade.php'));

        // Match the directive only — the explanatory comment above the markup
        // mentions <template x-if> by name on purpose, so look for the live
        // directive with its trailing quote.
        $this->assertStringNotContainsString('<template x-if=', $modal);
        $this->assertStringContainsString('x-show="$store.modals.is', $modal);
    }

    public function test_member_cannot_mutate_status_via_methods(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'name' => 'STAY']);
        $this->actingAs($member);

        Livewire::test('app-sidebar')
            ->set('projectSettingsId', $project->id)
            ->set('pendingStatusName.active', 'NOPE')
            ->call('addStatus', 'active');

        Livewire::test('app-sidebar')->call('renameStatus', $status->id, 'CHANGED');
        Livewire::test('app-sidebar')->call('deleteStatus', $status->id);

        $this->assertSame(1, ProjectStatus::count());
        $this->assertSame('STAY', $status->fresh()->name);
    }
}
