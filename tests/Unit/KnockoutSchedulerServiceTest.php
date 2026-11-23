<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Scheduling\KnockoutSchedulerService;
use App\Services\Scheduling\MatchGameFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnockoutSchedulerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_knockout_quarter_finals_generates_cross_pairings(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải 2 Bảng Top 4',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
        ]);

        $gA = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);
        $gB = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng B']);

        $playersA = [];
        $playersB = [];
        for ($i = 1; $i <= 5; $i++) {
            $pA = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'name' => "Kỳ thủ A{$i}"]);
            $pB = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'name' => "Kỳ thủ B{$i}"]);
            $playersA[] = $pA;
            $playersB[] = $pB;

            Standing::create([
                'tournament_id' => $tournament->id,
                'group_id' => $gA->id,
                'participant_id' => $pA->id,
                'points' => 10 - $i, // A1 highest, A5 lowest
                'tie_break_score' => 0.0,
            ]);

            Standing::create([
                'tournament_id' => $tournament->id,
                'group_id' => $gB->id,
                'participant_id' => $pB->id,
                'points' => 10 - $i, // B1 highest, B5 lowest
                'tie_break_score' => 0.0,
            ]);
        }

        $scheduler = new KnockoutSchedulerService(new MatchGameFactory);
        $timeControls = [
            (object) ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
            (object) ['game_number' => 2, 'base_minutes' => 15, 'increment_seconds' => 2],
        ];

        // 1. Generate QF
        $qfRound = $scheduler->generateQuarterFinals($tournament, $timeControls);
        $this->assertEquals('knockout', $qfRound->stage);
        $this->assertEquals('Tứ Kết', $qfRound->stage_name);
        $this->assertCount(4, $qfRound->matches);

        // Verify pairings:
        // Match 1: 1A vs 4B
        $m1 = $qfRound->matches[0];
        $this->assertEquals($playersA[0]->id, $m1->player1_id);
        $this->assertEquals($playersB[3]->id, $m1->player2_id);

        // Match 2: 2B vs 3A
        $m2 = $qfRound->matches[1];
        $this->assertEquals($playersB[1]->id, $m2->player1_id);
        $this->assertEquals($playersA[2]->id, $m2->player2_id);

        // Match 3: 1B vs 4A
        $m3 = $qfRound->matches[2];
        $this->assertEquals($playersB[0]->id, $m3->player1_id);
        $this->assertEquals($playersA[3]->id, $m3->player2_id);

        // Match 4: 2A vs 3B
        $m4 = $qfRound->matches[3];
        $this->assertEquals($playersA[1]->id, $m4->player1_id);
        $this->assertEquals($playersB[2]->id, $m4->player2_id);

        // Set winners for QF
        $m1->player1_score = 2.0; $m1->player2_score = 0.0; $m1->status = 'completed'; $m1->save(); // 1A wins
        $m2->player1_score = 1.5; $m2->player2_score = 0.5; $m2->status = 'completed'; $m2->save(); // 2B wins
        $m3->player1_score = 2.0; $m3->player2_score = 0.0; $m3->status = 'completed'; $m3->save(); // 1B wins
        $m4->player1_score = 2.0; $m4->player2_score = 0.0; $m4->status = 'completed'; $m4->save(); // 2A wins

        // 2. Generate Semi-Finals
        $sfRound = $scheduler->generateSemiFinals($tournament, $qfRound, $timeControls);
        $this->assertNotNull($sfRound);
        $this->assertEquals('Bán Kết', $sfRound->stage_name);
        $this->assertCount(2, $sfRound->matches);

        // SF1: Winner QF1 (1A) vs Winner QF2 (2B)
        $this->assertEquals($playersA[0]->id, $sfRound->matches[0]->player1_id);
        $this->assertEquals($playersB[1]->id, $sfRound->matches[0]->player2_id);

        // SF2: Winner QF3 (1B) vs Winner QF4 (2A)
        $this->assertEquals($playersB[0]->id, $sfRound->matches[1]->player1_id);
        $this->assertEquals($playersA[1]->id, $sfRound->matches[1]->player2_id);

        // Set winners & losers for SF
        $sf1 = $sfRound->matches[0];
        $sf1->player1_score = 2.0; $sf1->player2_score = 0.0; $sf1->status = 'completed'; $sf1->save(); // 1A wins, 2B loses

        $sf2 = $sfRound->matches[1];
        $sf2->player1_score = 0.5; $sf2->player2_score = 1.5; $sf2->status = 'completed'; $sf2->save(); // 2A wins, 1B loses

        // 3. Generate Finals & 3rd Place Match
        $finalRound = $scheduler->generateFinals($tournament, $sfRound, $timeControls);
        $this->assertNotNull($finalRound);
        $this->assertEquals('Chung Kết & Tranh Hạng 3', $finalRound->stage_name);
        $this->assertCount(2, $finalRound->matches);

        // Match 1 (Chung Kết): Winner SF1 (1A) vs Winner SF2 (2A)
        $this->assertEquals($playersA[0]->id, $finalRound->matches[0]->player1_id);
        $this->assertEquals($playersA[1]->id, $finalRound->matches[0]->player2_id);

        // Match 2 (Tranh Hạng 3): Loser SF1 (2B) vs Loser SF2 (1B)
        $this->assertEquals($playersB[1]->id, $finalRound->matches[1]->player1_id);
        $this->assertEquals($playersB[0]->id, $finalRound->matches[1]->player2_id);
    }
}
