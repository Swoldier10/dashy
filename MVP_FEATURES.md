# Dashy — MVP Feature Backlog

Snapshot of what's built and what's missing for a launchable product, based on a full codebase analysis on 2026-06-02 (supersedes the 2026-05-16 audit).

## What's already built

The foundation is solid and close to launchable. The core pillars are all present:

- **Auth** — Email/password + Google OAuth, 2FA, email verification, password reset, account deletion
- **Teams** — Multi-tenant workspaces, member roles (Owner/Member), hourly rate + currency, team logo, **full invite-by-email flow** (invite token → signup → auto-join, accept/resend/revoke, expiry purge, Brevo-branded email)
- **Projects** — Project container scoped to a team, customizable kanban columns (statuses), **per-project dashboard with chart.js time/billing charts** (daily hours, monthly totals, rate → money)
- **Tasks** — CRUD, multi-assignee, priority, start/end datetime, position/reorder, attachments, archive, **bulk ops with toolbar** (move/assign/archive/delete/priority/due-date)
- **Calendar** — Events with recurrence rules, all-day/timed, colors, location, **one-way Google Calendar sync**, working-hours preferences
- **Time tracking** — Start/stop timer, manual entry, project rollups, monthly Excel export, ClickUp-style time popover in the task drawer
- **AI Chat (the differentiator)** — Claude/Codex backend, **48 tools** wired into tasks/events/projects/teams/time, voice transcription (Whisper), image attachments (incl. attach-to-task), persistent memory, history compaction, semantic search (embeddings), plan tool
- **Settings** — Appearance, AI memory management, security/2FA, Google Calendar connection

Of the previous audit's "five that unlock MVP", **invitations shipped** and **reporting is partially done** (project-level, not team-level).

## Tier 1 — Must-have before showing this to a paying user

1. **Notifications** — nothing exists (zero traces in code). Assignment, mention, due-soon, invite-accepted. In-app bell + email. Now that invitations are live, multi-user teams are reachable — which makes the silence visible. The single biggest gap.
2. **Task comments / activity feed** — no Comment model, no audit trail ("Anna moved this to Done, 2h ago"). Two people can't discuss a task inside the product. Once comments exist, the AI reads them nearly for free via the existing tool registry.
3. **Quick search (Cmd-K)** — no command palette, no keyboard handlers anywhere. The Search domain + embeddings exist but are only reachable through chat. Don't force opening chat for "where's that bug?".
4. **Empty states + onboarding** — partial (tasks pages have empty states), but a first-time user lands on `/chat` with no guided "create your first project / try saying 'plan my week'" moment. The differentiator is invisible to a new signup.

## Tier 1.5 — Launch logistics (not features, but you can't ship without them)

5. **Public landing page** — `/` redirects straight to `/login`. Nothing explains what Dashy is before asking for credentials.
6. **Legal pages** — register page references terms, but no terms/privacy routes exist. Required for GDPR and Google OAuth verification.
7. **Production infra checklist** — SQLite has a concurrent-write ceiling (fine to start, know the limit); `QUEUE_CONNECTION=database` needs a worker/supervisor in prod; `MAIL_MAILER=log` needs the Brevo config promoted to prod env; **AI cost controls** — no per-user rate limit or token budget on chat, one enthusiastic user can run up the Anthropic bill.
8. **Responsive pass** — CLAUDE.md rule 10 demands it; explicit audit of the chat panel, kanban board, calendar, and task drawer at 375px before launch.

## Tier 2 — First month after launch (users will ask within weeks)

9. **Subtasks or checklists** — confirmed absent; the most common "day 2" request in any task tool.
10. **Recurring tasks** — calendar already has recurrence; tasks should too (weekly cleanup, monthly invoice).
11. **Team-level reporting** — lift the project dashboard to team scope: time per project, open tasks per assignee, overdue count. Data exists in `ProjectTimeStatsService` and `ListOverdueTasksService`.
12. **Invoice / billable export per client-project** — hourly rate + time entries → PDF or richer Excel. Monthly export exists; productize it.
13. **Per-task time view in calendar** — tasks with start/end already render; surface time entries on the calendar too so people *see* where their week went.
14. **Keyboard shortcuts** beyond Cmd-K — `C` (new task), `Esc` (close drawer). Power users expect this in any work app.

## Tier 3 — Defer past MVP

- Real-time collab cursors / presence (Reverb)
- Native mobile apps
- Third-party integrations (Slack, GitHub, Jira)
- Templates, approval workflows, custom fields
- Public API + webhooks
- Billing/Stripe (charging for Dashy itself — separate from invoicing customers)

## The opinionated short answer

Four features stand between Dashy and launch: **notifications, task comments, Cmd-K search, and onboarding** — plus the non-feature trio of landing page, legal pages, and prod infra (queue worker, mail, AI spend caps). Build order: **notifications → comments → onboarding → Cmd-K**, because the first two make the just-shipped invitations actually pay off — right now you can invite a teammate into a product that never speaks to them.

Scope warning: each of the four is multi-day work that touches UI + Service + Action layers + tests per project rules. Pick one, scope it tight, ship it end-to-end before starting the next.
