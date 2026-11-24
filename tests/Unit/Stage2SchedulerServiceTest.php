<?php

namespace Tests\Unit;

use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Scheduling\MatchGameFactory;
use App\Services\Scheduling\Stage2SchedulerService;
use App\Services\Scheduling\StepladderSchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Stage2SchedulerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage2_generates_7_rounds_for_8_advancing_players(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải 2 Bảng',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
            'bonus_top1' => 4.0,
            'bonus_top2' => 3.0,
            'bonus_top3' => 2.0,
        ]);

        $gA = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);
        $gB = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng B']);

        for ($i = 1; $i <= 4; $i++) {
            $pA = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'name' => "A{$i}"]);
            $pB = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'name' => "B{$i}"]);

            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'participant_id' => $pA->id, 'points' => 10 - $i]);
            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'participant_id' => $pB->id, 'points' => 10 - $i]);
        }

        $factory = new MatchGameFactory;
        $stepladder = new StepladderSchedulerService($factory);
        $service = new Stage2SchedulerService($factory, $stepladder);

        $rounds = $service->generateStage2RoundRobin($tournament);

        // 8 players -> 7 rounds
        $this->assertCount(7, $rounds);

        $allMatchPairs = [];
        foreach ($rounds as $round) {
            $this->assertEquals('stage2_round_robin', $round->stage);
            $this->assertCount(4, $round->matches); // 4 matches per round

            foreach ($round->matches as $m) {
                $key = min($m->player1_id, $m->player2_id) . '-' . max($m->player1_id, $m->player2_id);
                $allMatchPairs[] = $key;
            }
        }

        // 8 players -> 8*7/2 = 28 unique matches
        $this->assertCount(28, $allMatchPairs);
        $this->assertCount(28, array_unique($allMatchPairs));
    }

    public function test_sync_bonus_points_and_promote_to_stepladder(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải Bonus Test',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
            'bonus_top1' => 4.0,
            'bonus_top2' => 3.0,
            'bonus_top3' => 2.0,
        ]);

        $players = [];
        for ($i = 1; $i <= 8; $i++) {
            $p = Participant::create(['tournament_id' => $tournament->id, 'name' => "Player {$i}"]);
            $players[$i] = $p;

            Standing::create([
                'tournament_id' => $tournament->id,
                'stage' => 'stage2_round_robin',
                'participant_id' => $p->id,
                'points' => 5.0, // all start with 5 pts in round robin
                'bonus_points' => 0.0,
            ]);
        }

        // Simulate Knockout branch Finals:
        $koFinalRound = Round::create([
            'tournament_id' => $tournament->id,
            'stage' => 'knockout',
            'stage_name' => 'Chung Kết & Tranh Hạng 3',
            'round_number' => 10,
        ]);

        // Gold Match: Player 1 (wins Top 1) vs Player 2 (loses Top 2)
        ChessMatch::create([
            'round_id' => $koFinalRound->id,
            'player1_id' => $players[1]->id,
            'player2_id' => $players[2]->id,
            'player1_score' => 2.0,
            'player2_score' => 1.0,
            'status' => 'completed',
        ]);

        // Bronze Match: Player 3 (wins Top 3) vs Player 4
        ChessMatch::create([
            'round_id' => $koFinalRound->id,
            'player1_id' => $players[3]->id,
            'player2_id' => $players[4]->id,
            'player1_score' => 2.0,
            'player2_score' => 0.0,
            'status' => 'completed',
        ]);

        $factory = new MatchGameFactory;
        $stepladder = new StepladderSchedulerService($factory);
        $service = new Stage2SchedulerService($factory, $stepladder);

        $service->syncBonusPoints($tournament);

        $st1 = Standing::where('stage', 'stage2_round_robin')->where('participant_id', $players[1]->id)->first();
        $st2 = Standing::where('stage', 'stage2_round_robin')->where('participant_id', $players[2]->id)->first();
        $st3 = Standing::where('stage', 'stage2_round_robin')->where('participant_id', $players[3]->id)->first();

        // Player 1 got +4, Player 2 got +3, Player 3 got +2
        $this->assertEquals(4.0, $st1->bonus_points);
        $this->assertEquals(9.0, $st1->total_points); // 5 + 4 = 9

        $this->assertEquals(3.0, $st2->bonus_points);
        $this->assertEquals(8.0, $st2->total_points); // 5 + 3 = 8

        $this->assertEquals(2.0, $st3->bonus_points);
        $this->assertEquals(7.0, $st3->total_points); // 5 + 2 = 7

        // Promote to Stepladder
        $stepR1 = $service->promoteToStepladder($tournament);
        $this->assertNotNull($stepR1);
        $this->assertEquals('Giai Đoạn 1 (Trận 1 & Trận 2)', $stepR1->stage_name);
        $this->assertCount(2, $stepR1->matches);

        // Player 1 had 9 pts -> Seed 1 (waits for Finals)
        $this->assertEquals(1, $players[1]->fresh()->seed);
        // Player 2 had 8 pts -> Seed 2 (plays in Match 1)
        $this->assertEquals(2, $players[2]->fresh()->seed);
    }
}
