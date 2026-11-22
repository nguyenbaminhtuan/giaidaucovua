---
phase: 2
title: Scheduling Engines
status: completed
priority: P1
dependencies:
  - 1
---

# Phase 2: Scheduling Engines

## Overview
Implement deterministic pairing algorithms for both Round-Robin (group-based Berger tables with bye handling) and Swiss-system (score-bracket pairing, color balancing, and rematch prevention).

## Requirements
- Functional:
  - Round-Robin: For any group of $N$ players, generate all rounds ($N-1$ rounds for even $N$, $N$ rounds for odd $N$ with 1 bye per round).
  - Multi-game instantiation: For each match, create $M$ games with configured time controls (`base_minutes` + `increment_seconds`) and strict color alternation between games.
  - Swiss System: For each round $R$, group active players by score points, pair within score groups, forbid rematches, balance White/Black counts, and assign Byes fairly.
  - Confirmation Action: Triggering "Xác nhận tạo cặp" transactionally commits all rounds, matches, and games.
- Non-functional:
  - Pure domain services isolated from HTTP controllers (`RoundRobinSchedulerService`, `SwissSchedulerService`).
  - Unit-tested for edge cases: 3 players (odd), 4 players (even), 7 players, 8 players, repeated rematch rejection.

## Architecture
```
[Tournament Setup Form] ──> [TournamentService::confirmFormatAndPairings()]
                                      │
                 ┌────────────────────┴────────────────────┐
                 ▼                                         ▼
   [RoundRobinSchedulerService]                 [SwissSchedulerService]
   • Polygon Rotation (Berger)                  • Score Brackets
   • Odd N -> Dummy Bye Player                  • Rematch Graph Checking
   • Generates All Group Rounds                 • Generates Next Round Only
                 │                                         │
                 └────────────────────┬────────────────────┘
                                      ▼
                        [MatchFactory / GameFactory]
                        • Creates ChessMatch records
                        • Creates ChessGame records with time controls
                        • Alternates White/Black colors
```

## Related Code Files
- Create:
  - `app/Services/Scheduling/RoundRobinSchedulerService.php`
  - `app/Services/Scheduling/SwissSchedulerService.php`
  - `app/Services/Scheduling/MatchGameFactory.php`
  - `tests/Unit/RoundRobinSchedulerServiceTest.php`
  - `tests/Unit/SwissSchedulerServiceTest.php`

## Implementation Steps
1. Build `RoundRobinSchedulerService` using standard cyclic Berger pairing algorithm.
2. Build `MatchGameFactory` to create `ChessMatch` and child `ChessGame` records with correct time controls and color alternation (`Game 1: A=White, B=Black; Game 2: B=White, A=Black`).
3. Build `SwissSchedulerService` with greedy backtracking / blossom-matching heuristic to prevent duplicate pairings and handle floaters/byes.
4. Add comprehensive unit tests verifying:
   - Every participant in a 5-player round-robin plays exactly 4 matches and gets 1 bye.
   - No two players meet twice in round-robin.
   - Swiss engine avoids duplicate matchups across consecutive rounds.

## Success Criteria
- [ ] Round-robin generates correct number of pairings for both even and odd participant counts.
- [ ] Child `ChessGame` records match configured time controls and alternate colors accurately.
- [ ] Swiss pairings enforce zero rematches across tested simulation rounds.

## Risk Assessment
- Risk: Deadlock in Swiss pairing when near final rounds if players in the same score group have all played each other.
  - Mitigation: Implement floater fallbacks (pairing with adjacent score group) with backtracking.
