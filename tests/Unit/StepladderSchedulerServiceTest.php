<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Models\Tournament;
use App\Services\Scheduling\MatchGameFactory;
use App\Services\Scheduling\StepladderSchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StepladderSchedulerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stepladder_generates_round1_and_finals_properly(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải Chung Kết Top 5',
            'format' => 'stepladder_top5',
        ]);

        $players = [];
        for ($i = 1; $i <= 5; $i++) {
            $players[$i] = Participant::create([
                'tournament_id' => $tournament->id,
                'name' => "Kỳ thủ Hạng {$i}",
                'seed' => $i,
            ]);
        }

        $scheduler = new StepladderSchedulerService(new MatchGameFactory);
        $timeControls = [
            (object) ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
            (object) ['game_number' => 2, 'base_minutes' => 5, 'increment_seconds' => 2],
            (object) ['game_number' => 3, 'base_minutes' => 5, 'increment_seconds' => 2],
        ];

        // 1. Generate Round 1 (Trận 1 & Trận 2)
        $r1 = $scheduler->generateRound1($tournament, $timeControls);
        $this->assertNotNull($r1);
        $this->assertEquals('knockout', $r1->stage);
        $this->assertCount(2, $r1->matches);

        // Trận 1: Hạng 2 vs Hạng 3
        $t1 = $r1->matches[0];
        $this->assertEquals($players[2]->id, $t1->player1_id);
        $this->assertEquals($players[3]->id, $t1->player2_id);

        // Trận 2: Hạng 4 vs Hạng 5
        $t2 = $r1->matches[1];
        $this->assertEquals($players[4]->id, $t2->player1_id);
        $this->assertEquals($players[5]->id, $t2->player2_id);

        // Simulate results:
        // Trận 1: Hạng 2 thắng (2.0 - 1.0) => Thắng T1: Hạng 2, Thua T1: Hạng 3
        $t1->player1_score = 2.0; $t1->player2_score = 1.0; $t1->status = 'completed'; $t1->save();

        // Trận 2: Hạng 5 thắng (1.0 - 2.0) => Thắng T2: Hạng 5
        $t2->player1_score = 1.0; $t2->player2_score = 2.0; $t2->status = 'completed'; $t2->save();

        // 2. Generate Finals Round (Tranh HCV & Tranh HCĐ)
        $finalsRound = $scheduler->generateFinalsRound($tournament, $r1, $timeControls);
        $this->assertNotNull($finalsRound);
        $this->assertCount(2, $finalsRound->matches);

        // Match Tranh HCV: Thắng Trận 1 (Hạng 2) vs Hạng 1 (players[1])
        $mGold = $finalsRound->matches[0];
        $this->assertEquals($players[2]->id, $mGold->player1_id);
        $this->assertEquals($players[1]->id, $mGold->player2_id);

        // Match Tranh HCĐ: Thua Trận 1 (Hạng 3) vs Thắng Trận 2 (Hạng 5)
        $mBronze = $finalsRound->matches[1];
        $this->assertEquals($players[3]->id, $mBronze->player1_id);
        $this->assertEquals($players[5]->id, $mBronze->player2_id);
    }
}
