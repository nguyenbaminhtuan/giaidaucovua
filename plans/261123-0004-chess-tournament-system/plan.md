---
title: Chess Tournament Management System
description: >-
  Laravel 11 + Livewire 3 web application for chess tournaments supporting
  Round-Robin (group-based) and Swiss system, Fischer time controls, match-game
  accordion, and real-time standings.
status: completed
priority: P1
branch: main
tags:
  - laravel
  - livewire
  - chess
  - tournament
  - round-robin
  - swiss-system
blockedBy: []
blocks: []
created: '2026-11-22T17:07:44.233Z'
createdBy: 'ck:plan'
source: skill
---

# Chess Tournament Management System

## Overview

Build a web application using PHP Laravel 11 and Livewire 3 to organize chess tournaments. Supports:
1. **Format Selection**: Round-Robin with custom groups (A, B, C, D...) and Swiss-system pairing by score.
2. **Match & Time Control Configuration**: Multi-game matches (e.g. 2 games/match) with custom Fischer time controls per game (minutes base + increment seconds, e.g., 5+2, 15+2) and automatic color alternation.
3. **Master-Detail Accordion UI**: Collapsible match cards with quick score selector buttons `[1 - 0]`, `[½ - ½]`, `[0 - 1]`, automatic match score summation, and green checkmark confirmation.
4. **Live Standings & Tie-breaks**: Group-based standings ordered by total points (Win = 1, Draw = 0.5, Loss = 0) with tie-break calculations (Head-to-head, Sonneborn-Berger / Buchholz).

## Phases

| Phase | Name | Status |
|-------|------|--------|
| 1 | [Setup & Database Schema](./phase-01-setup-database-schema.md) | Completed |
| 2 | [Scheduling Engines](./phase-02-scheduling-engines.md) | Completed |
| 3 | [Match UI & Score Management](./phase-03-match-ui-score-management.md) | Completed |
| 4 | [Standings & Rankings](./phase-04-standings-rankings.md) | Completed |

## Acceptance Criteria

- [x] New tournament can be created with format selection (`round_robin_groups` or `swiss`).
- [x] Round-robin allows naming groups (A, B, C...) and assigning participants.
- [x] Users can configure number of games per match with individual time controls (base minutes + increment seconds).
- [x] Clicking "Xác nhận" generates valid round-robin pairings (Berger tables) with bye handling for odd player counts.
- [x] Match list displays each pairing with accordion dropdown to expand game details.
- [x] Scores can be selected with quick buttons (`1-0`, `½-½`, `0-1`) and match score auto-aggregates.
- [x] Green checkmark confirmation button locks match results and records points into standings.
- [x] Standings tab renders group tables ranked by points descending with tie-breaks.

## Tech Stack & Principles

- **Framework**: Laravel 11 (PHP 8.3+)
- **Frontend / State**: Livewire 3 + Alpine.js + Tailwind CSS
- **Database**: SQLite (local) / MySQL compatible
- **Architecture**: Domain Service Pattern, Event-driven standings calculation
- **Principles**: YAGNI, KISS, DRY, Modularization (< 200 lines per file)
