---
phase: 4
title: Standings & Rankings
status: completed
priority: P1
dependencies:
  - 1
  - 2
  - 3
---

# Phase 4: Standings & Rankings

## Overview
Implement the real-time Standings and Rankings module. Compute group rankings using Win/Draw/Loss points (Win = 1 pt, Draw = 0.5 pt, Loss = 0 pt) and chess tie-break metrics (Head-to-head, Sonneborn-Berger for Round-Robin, Buchholz for Swiss). Provide a clean, reactive group standings table.

## Requirements
- Functional:
  - Calculation Engine:
    - Triggered upon match completion (`MatchResultConfirmed` event).
    - Recalculates stats for both players: Matches Played ($P$), Won ($W$), Drawn ($D$), Lost ($L$), Total Points ($Pts$).
    - Computes secondary tie-break scores:
      - Round-Robin: Sonneborn-Berger coefficient (sum of scores of defeated opponents + half scores of drawn opponents) and Direct Encounter.
      - Swiss: Buchholz coefficient (sum of all opponents' total scores).
    - Sort order: `Points DESC`, `TieBreak DESC`, `Wins DESC`.
  - Standings Table UI:
    - Dedicated "Bảng Xếp Hạng" tab/view.
    - Filterable by Group (Bảng A, Bảng B, Bảng C...).
    - Clear columns: Hạng (Rank: 1, 2, 3...), Kỳ thủ (Player), Số trận (P), Thắng (W), Hòa (D), Thua (L), Hệ số phụ (SB/BH), Tổng điểm (Pts).
    - Visual podium indicators for Top 1, 2, 3 (Gold, Silver, Bronze badges).
- Non-functional:
  - Atomic database transactions during recalculation to prevent race conditions.
  - Cached or precomputed standings table for instant UI rendering.

## Architecture
```
[User clicks Green Checkmark]
               │
               ▼
[MatchResultConfirmed Event]
               │
               ▼
[UpdateStandingsListener]
               │
               ▼
[StandingsCalculatorService]
   ├── 1. Query all completed matches in group/tournament
   ├── 2. Compute Points: W * 1.0 + D * 0.5 + L * 0.0
   ├── 3. Calculate Tie-breaks (Sonneborn-Berger / Buchholz)
   └── 4. Upsert `standings` records
               │
               ▼
[Livewire: StandingsBoard Component]
   └── Group Tabs (A, B, C...) -> Table sorted by Rank 1, 2, 3...
```

## Related Code Files
- Create:
  - `app/Events/MatchResultConfirmed.php`
  - `app/Listeners/UpdateStandingsListener.php`
  - `app/Services/Standings/StandingsCalculatorService.php`
  - `app/Livewire/Tournament/StandingsBoard.php`
  - `resources/views/livewire/tournament/standings-board.blade.php`
  - `tests/Unit/StandingsCalculatorServiceTest.php`
  - `tests/Feature/StandingsBoardTest.php`

## Implementation Steps
1. Create `MatchResultConfirmed` event and wire it to `UpdateStandingsListener`.
2. Build `StandingsCalculatorService` with robust point calculation and Sonneborn-Berger / Buchholz tie-break algorithms.
3. Build `StandingsBoard` Livewire component with tab switching across groups (Bảng A, B, C...).
4. Design responsive Tailwind CSS table showing rank, player name, match records, tie-break, and total points.
5. Write unit tests for:
   - Point calculation accuracy (decimal sums).
   - Correct ranking order (points descending).
   - Sonneborn-Berger tie-break differentiation when players have equal points.
6. Write feature test verifying that clicking the green checkmark on a match immediately reflects updated standings on the rankings tab.

## Success Criteria
- [ ] Scoring accurately awards 1.0 point for win, 0.5 point for draw, 0 for loss.
- [ ] Standings table automatically sorts from highest score to lowest score (Hạng 1, 2, 3...).
- [ ] Equal scores are broken by standard chess tie-break metrics.
- [ ] Tab switching between groups displays isolated standings per group.

## Risk Assessment
- Risk: Tie-break circular dependencies in incomplete tournaments.
  - Mitigation: Calculate tie-breaks dynamically based only on currently completed matches, defaulting unplayed opponents' points to 0.
