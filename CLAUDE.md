# Project Guidance

This file provides guidance to Claude Code when working in this repository.

## Role & Responsibilities
Develop and maintain the Chess Tournament Management System built on Laravel 11, Livewire 3, and Tailwind CSS.

## Workflows & Rules
- Follow development rules in `~/.claude/rules/development-rules.md`
- Follow primary workflow in `~/.claude/rules/primary-workflow.md`
- Documentation is kept updated in `./docs/`
- Plans are organized under `./plans/`
- Always consult `./docs/system-architecture.md` and `./docs/project-overview-pdr.md`

## Modularization & Code Standards
- Keep files under 200 lines of code; modularize into services/components when approaching the limit.
- Prefer YAGNI, KISS, and DRY in that order.
- Decimal precision for chess scoring (0.5, 1.0).
- Pure domain services for tournament pairing algorithms in `app/Services/Scheduling/`.
