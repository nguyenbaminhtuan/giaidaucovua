<?php

namespace App\Services\Scheduling;

use App\Models\Participant;
use App\Models\Round;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

class StepladderSchedulerService
{
    public function __construct(
        protected MatchGameFactory $matchGameFactory
    ) {}

    public function generateRound1(Tournament $tournament, $timeControls = []): ?Round
    {
        return DB::transaction(function () use ($tournament, $timeControls) {
            $participants = $tournament->participants()
                ->orderBy('seed')
                ->get()
                ->values();

            if ($participants->count() < 5) {
                return null;
            }

            // Seeds:
            // 0: Hạng 1 (Chờ ở chung kết)
            // 1: Hạng 2
            // 2: Hạng 3
            // 3: Hạng 4
            // 4: Hạng 5
            $p2 = $participants[1];
            $p3 = $participants[2];
            $p4 = $participants[3];
            $p5 = $participants[4];

            $round = Round::create([
                'tournament_id' => $tournament->id,
                'stage' => 'knockout',
                'stage_name' => 'Giai Đoạn 1 (Trận 1 & Trận 2)',
                'round_number' => 1,
            ]);

            // Trận 1: Hạng 2 vs Hạng 3
            $this->matchGameFactory->createMatch($round, $p2, $p3, false, $timeControls);

            // Trận 2: Hạng 4 vs Hạng 5
            $this->matchGameFactory->createMatch($round, $p4, $p5, false, $timeControls);

            return $round;
        });
    }

    public function generateFinalsRound(Tournament $tournament, Round $round1, $timeControls = []): ?Round
    {
        return DB::transaction(function () use ($tournament, $round1, $timeControls) {
            $matches = $round1->matches()->orderBy('id')->get();
            if ($matches->count() < 2) {
                return null;
            }

            $m1 = $matches[0]; // Hạng 2 vs Hạng 3
            $m2 = $matches[1]; // Hạng 4 vs Hạng 5

            $winnerT1 = $m1->getWinner();
            $loserT1 = $m1->getLoser();
            $winnerT2 = $m2->getWinner();

            $p1 = $tournament->participants()->where('seed', 1)->first()
                ?? $tournament->participants()->first();

            if (! $winnerT1 || ! $loserT1 || ! $winnerT2 || ! $p1) {
                return null;
            }

            $maxRound = $tournament->rounds()->max('round_number') ?? 0;
            $finalsRound = Round::create([
                'tournament_id' => $tournament->id,
                'stage' => 'knockout',
                'stage_name' => 'Giai Đoạn 2 (Tranh HCV & Tranh HCĐ)',
                'round_number' => $maxRound + 1,
            ]);

            // Trận Tranh HCV: Thắng Trận 1 vs Hạng 1
            $this->matchGameFactory->createMatch($finalsRound, $winnerT1, $p1, false, $timeControls);

            // Trận Tranh HCĐ: Thua Trận 1 vs Thắng Trận 2
            $this->matchGameFactory->createMatch($finalsRound, $loserT1, $winnerT2, false, $timeControls);

            return $finalsRound;
        });
    }
}
