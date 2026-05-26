<?php

namespace Tests\Feature;

use App\Models\RemedialTerm;
use Carbon\Carbon;
use Tests\TestCase;

class RemedialTermTest extends TestCase
{
    public function test_admin_can_create_list_update_and_delete_remedial_term(): void
    {
        $payload = [
            'year'                 => 2025,
            'semester'             => 1,
            'name'                 => 'Đợt phụ đạo test',
            'start_date'           => Carbon::now()->subDays(5)->toDateString(),
            'end_date'             => Carbon::now()->addMonths(2)->toDateString(),
            'registration_start'   => Carbon::now()->subDay()->toDateString(),
            'registration_end'     => Carbon::now()->addMonth()->toDateString(),
            'remedial_coefficient' => 1,
            'price_per_period'     => 150000,
            'price_coefficient'    => 1,
            'is_current_term'      => true,
        ];

        $create = $this->actingAsAdmin()
            ->apiJson('POST', '/admin/remedial-terms', $payload);

        $create->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Đợt phụ đạo test');

        $termId = $create->json('data.id');

        $this->actingAsAdmin()
            ->apiJson('GET', '/admin/remedial-terms')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Đợt phụ đạo test']);

        $this->actingAsAdmin()
            ->apiJson('PATCH', "/admin/remedial-terms/{$termId}", [
                'name' => 'Đợt phụ đạo đã sửa',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Đợt phụ đạo đã sửa');

        $this->actingAsAdmin()
            ->apiJson('DELETE', "/admin/remedial-terms/{$termId}")
            ->assertOk();

        $this->assertDatabaseHas('remedial_terms', [
            'id'         => $termId,
            'is_deleted' => true,
        ]);
    }

    public function test_bo_mon_cannot_access_admin_remedial_terms(): void
    {
        $this->actingAsBoMon()
            ->apiJson('GET', '/admin/remedial-terms')
            ->assertForbidden();
    }
}
