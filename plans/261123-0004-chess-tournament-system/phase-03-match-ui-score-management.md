---
phase: 3
title: Match UI & Score Management
status: completed
priority: P1
dependencies:
  - 1
  - 2
---

# Phase 3: Match UI & Score Management

## Overview
Develop the tournament setup form and the interactive Master-Detail Match Accordion using Livewire 3 + Alpine.js. Enable rapid score entry via game result buttons `[ 1 - 0 ]`, `[ ½ - ½ ]`, `[ 0 - 1 ]`, automated match score aggregation, and the green checkmark confirmation mechanism.

## Requirements
- Functional:
  - Dynamic tournament setup form:
    - Select format (`round_robin_groups` or `swiss`).
    - Add/remove groups (A, B, C...) and assign participant names.
    - Set number of games per match (e.g. 2 games) with per-game Fischer time controls (base minutes + increment seconds, e.g., 5+2, 15+2).
    - "Xác nhận" button triggers pairing generation and navigates to the tournament dashboard.
  - Interactive Match List:
    - Group and Round filter tabs.
    - Match Header displays: `Player 1 (Score 1) - (Score 2) Player 2`, status badge, expand toggle, and green checkmark button.
  - Collapsible Game Accordion:
    - Expand match row to reveal individual games.
    - Each game displays: Game number, time control badge (`5m + 2s`), Player Colors (White vs Black).
    - 3 rapid result buttons: `[ 1 - 0 ]` (White wins), `[ ½ - ½ ]` (Draw), `[ 0 - 1 ]` (Black wins).
    - Active result is highlighted; match aggregate score updates immediately without full-page reload.
  - Green Checkmark Confirmation:
    - Disabled until all games in the match have recorded results.
    - Clicking marks match as `completed`, locks input fields, and dispatches `MatchResultConfirmed` event.
- Non-functional:
  - Instant UI reactivity using Alpine.js for accordion animation and Livewire 3 for server-side score persistence.
  - Optimistic or snappy state updates with zero full-page refreshes.

## Architecture
```
[Livewire: TournamentManager]
   ├── [SetupWizard Component] -> Creates Tournament, Groups, TimeControls, calls Schedulers
   └── [MatchBoard Component]
          ├── (Round & Group Selector Tabs)
          └── [MatchRow Component] (repeated per match)
                 ├── Match Header (Scores, Status, Green Check Button)
                 └── Alpine.js x-collapse Accordion
                        └── [GameResultSelector] (1-0, 0.5-0.5, 0-1)
                                   │
                                   ▼ (wire:click="selectResult")
                        [MatchService::recordGameResult()]
                                   │
                                   ▼ (triggers)
                        Match Header Score Re-aggregation
```

## Related Code Files
- Create:
  - `app/Livewire/Tournament/SetupWizard.php`
  - `app/Livewire/Tournament/MatchBoard.php`
  - `app/Livewire/Tournament/MatchRow.php`
  - `resources/views/livewire/tournament/setup-wizard.blade.php`
  - `resources/views/livewire/tournament/match-board.blade.php`
  - `resources/views/livewire/tournament/match-row.blade.php`
  - `resources/views/components/layouts/app.blade.php`
  - `tests/Feature/MatchScoreManagementTest.php`

## Implementation Steps
1. Create `SetupWizard` Livewire component allowing dynamic addition of groups, players, and time controls.
2. Create `MatchBoard` component rendering matches categorized by group and round.
3. Build `MatchRow` sub-component with Alpine.js collapsible accordion drawer.
4. Implement `selectGameResult(gameId, result)` method in `MatchRow` that saves game result, re-calculates `player1_score` and `player2_score` on the match, and updates UI state.
5. Implement `confirmMatch(matchId)` method triggered by the green checkmark button to seal the match and dispatch `MatchResultConfirmed`.
6. Add Feature test testing the full UI flow: create tournament -> expand match -> choose results -> confirm with green checkmark.

## Success Criteria
- [ ] Users can configure custom games with different base minutes and increments (e.g. game 1: 5m+2s, game 2: 15m+2s).
- [ ] Clicking match row expands/collapses game accordion smoothly.
- [ ] Clicking `[ 1 - 0 ]`, `[ ½ - ½ ]`, or `[ 0 - 1 ]` immediately updates match total score.
- [ ] Green checkmark confirmation transitions match to completed and locks scores.

## Risk Assessment
- Risk: Concurrent updates or double-clicks on confirmation button.
  - Mitigation: Add `wire:loading.attr="disabled"` on the green checkmark button and database transaction with row-level locks on the match record.
