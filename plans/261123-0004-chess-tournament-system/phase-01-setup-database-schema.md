---
phase: 1
title: Setup & Database Schema
status: completed
priority: P1
dependencies: []
---

# Phase 1: Setup & Database Schema

## Overview
Bootstrap Laravel 11 application with Livewire 3 and Tailwind CSS. Establish domain models, database migrations, and relationships for tournaments, groups, participants, rounds, matches, games, and standings.

## Requirements
- Functional:
  - Database support for Round-Robin groups and Swiss tournaments.
  - Multi-game matches with Fischer time controls per game.
  - Tracking points with decimal precision (e.g. 0.5, 1.0, 1.5).
  - Proper foreign keys, cascading deletes, and indexed queries.
- Non-functional:
  - SQLite default for zero-config local run, compatible with MySQL/PostgreSQL.
  - All models adhere to single responsibility principle.

## Architecture
Domain Models:
```
Tournament (1) ─── (N) TournamentGroup (1) ─── (N) Participant
    │                       │
    ├── (N) TimeControl     └── (N) Round (1) ─── (N) ChessMatch (1) ─── (N) ChessGame
    │                                                   │
    └── (N) Standing ───────────────────────────────────┘
```

## Related Code Files
- Create:
  - `database/migrations/2026_11_23_000001_create_tournaments_table.php`
  - `database/migrations/2026_11_23_000002_create_tournament_groups_table.php`
  - `database/migrations/2026_11_23_000003_create_participants_table.php`
  - `database/migrations/2026_11_23_000004_create_tournament_time_controls_table.php`
  - `database/migrations/2026_11_23_000005_create_rounds_table.php`
  - `database/migrations/2026_11_23_000006_create_chess_matches_table.php`
  - `database/migrations/2026_11_23_000007_create_chess_games_table.php`
  - `database/migrations/2026_11_23_000008_create_standings_table.php`
  - `app/Models/Tournament.php`
  - `app/Models/TournamentGroup.php`
  - `app/Models/Participant.php`
  - `app/Models/TournamentTimeControl.php`
  - `app/Models/Round.php`
  - `app/Models/ChessMatch.php`
  - `app/Models/ChessGame.php`
  - `app/Models/Standing.php`
  - `tests/Feature/TournamentSchemaTest.php`

## Implementation Steps
1. Bootstrap Laravel 11 project structure in current working directory.
2. Install Livewire 3 and configure Tailwind CSS with Vite.
3. Write migrations for all 8 domain tables with proper types (`decimal(4,1)` for scores, enum/string for statuses).
4. Implement Eloquent models with fillable attributes, casts, and relations (`hasMany`, `belongsTo`).
5. Run migrations and write `TournamentSchemaTest` to verify relations and cascading behavior.

## Success Criteria
- [ ] Laravel 11 boot sequence operational (`php artisan test` runs without error).
- [ ] Database migrations execute cleanly (`php artisan migrate:fresh`).
- [ ] All Eloquent relations (`Tournament -> Groups -> Participants`, `Match -> Games`) verified by feature tests.

## Risk Assessment
- Risk: SQLite foreign key enforcement disabled by default in some configurations.
  - Mitigation: Explicitly enable foreign keys in `config/database.php` SQLite connection.
