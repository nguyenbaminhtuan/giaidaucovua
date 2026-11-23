# Codebase Summary

## Project Structure
```
├── app/
│   ├── Events/               # Domain Events (MatchResultConfirmed)
│   ├── Listeners/            # Event Listeners (UpdateStandingsListener)
│   ├── Livewire/             # Reactive UI Components
│   │   ├── Auth/
│   │   │   └── AdminBar.php
│   │   └── Tournament/
│   │       ├── TournamentList.php
│   │       ├── SetupWizard.php
│   │       ├── TournamentDashboard.php
│   │       ├── PlayoffBoard.php
│   │       ├── MatchRow.php
│   │       └── StandingsBoard.php
│   ├── Models/               # Eloquent Models
│   │   ├── Tournament.php
│   │   ├── TournamentGroup.php
│   │   ├── Participant.php
│   │   ├── TournamentTimeControl.php
│   │   ├── Round.php
│   │   ├── ChessMatch.php
│   │   ├── ChessGame.php
│   │   └── Standing.php
│   └── Services/             # Pure Domain Logic
│       ├── TournamentCreationService.php
│       ├── Scheduling/
│       │   ├── RoundRobinSchedulerService.php
│       │   ├── SwissSchedulerService.php
│       │   ├── KnockoutSchedulerService.php
│       │   └── MatchGameFactory.php
│       └── Standings/
│           └── StandingsCalculatorService.php
├── database/migrations/      # Schema migrations
├── docs/                     # Architectural & Engineering documentation
├── plans/                    # Step-by-step phased execution plans
└── resources/views/          # Blade & Livewire Templates
```
