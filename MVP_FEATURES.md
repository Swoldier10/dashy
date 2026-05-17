# Dashy — MVP Feature Backlog

Snapshot of what's built and what's missing for a "nice MVP", based on a full codebase analysis on 2026-05-16.

## What's already built

The foundation is solid. The core pillars are all present:

- **Auth** — Email/password + Google OAuth, 2FA, email verification, password reset, account deletion
- **Teams** — Multi-tenant workspaces, member roles (Owner/Member), hourly rate + currency, team logo
- **Projects** — Project container scoped to a team, customizable kanban columns (statuses)
- **Tasks** — CRUD, multi-assignee, priority, start/end datetime, position/reorder, attachments, archive, bulk ops
- **Calendar** — Events with recurrence rules, all-day/timed, colors, location
- **Time tracking** — Start/stop timer, manual entry, project rollups, monthly Excel export
- **AI Chat (the differentiator)** — Claude/Codex backend, 40+ tools wired into tasks/events/projects/time, voice transcription (Whisper), image attachments, persistent memory, history compaction, semantic search (embeddings)
- **Settings** — Appearance, AI memory management, security/2FA

## Tier 1 — Must-have before showing this to a paying user

1. **Team invitations** — `AddTeamMemberService` exists but no invite-by-email flow. A real workspace needs to onboard a teammate who doesn't have an account yet (invite token → signup → auto-join team).
2. **Notifications** — assignment, mention, due-soon, comment-reply. In-app bell + email digest. Collaboration is silent right now.
3. **Task comments / activity feed** — tasks have attachments but no discussion thread or audit trail ("Anna moved this to Done, 2h ago"). Critical for multi-person task work.
4. **Quick search** — semantic search exists via the AI, but users also need a `Cmd-K`-style instant text search across tasks/projects/events. Don't force opening chat for "where's that bug?".
5. **Empty states + onboarding** — first-time user lands on `/chat`. Needs a guided "create your first project / try saying 'plan my week'" moment, otherwise the AI's value isn't obvious.
6. **Responsive pass** — CLAUDE.md rule 10 demands it; explicit audit of the chat panel, kanban board, calendar, and task drawer at 375px before MVP.

## Tier 2 — Strongly recommended (the "nice" in nice MVP)

7. **Task comments → AI-aware** — once comments exist, let the chat assistant read them ("summarize discussion on task #42"). Cheap win since the tool registry is already there.
8. **Subtasks or checklists** — even a flat checklist on a task. Users will hit this immediately.
9. **Recurring tasks** — calendar already has recurrence; tasks should too (weekly cleanup, monthly invoice).
10. **Reporting view** — chart.js is installed but unwired. A team-level page: time per project, open tasks per assignee, overdue count. Data exists in `ProjectTimeStatsService` and `ListOverdueTasksService`.
11. **Invoice / billable export per client-project** — hourly rate + time entries → PDF or richer Excel. Monthly export exists; productize it.
12. **Per-task time view in calendar** — tasks with start/end already render; surface time entries on the calendar too so people *see* where their week went.
13. **Keyboard shortcuts** — at minimum `Cmd-K` (search), `C` (new task), `Esc` (close drawer). Power users expect this in any work app.

## Tier 3 — Defer past MVP

- Real-time collab cursors / presence (Reverb)
- Native mobile apps
- Third-party integrations (Slack, GitHub, Jira)
- Templates, approval workflows, custom fields
- Public API + webhooks
- Billing/Stripe (charging for Dashy itself — separate from invoicing customers)

## The opinionated short answer

If forced to pick **five** items that unlock "nice MVP": **invitations, notifications, task comments, quick search, and onboarding/empty states.** Everything else is in surprisingly good shape — the AI tool surface is the moat, and it's mostly there.

Scope warning: each of those five is multi-day work that touches UI + Service + Action layers + tests per project rules. Pick one, scope it tight, ship it end-to-end before starting the next.
