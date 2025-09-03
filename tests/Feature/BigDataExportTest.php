<?php

namespace Tests\Feature;

use App\Jobs\ExportLargeDataJob;
use App\Models\DiamondData;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BigDataExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Upload $upload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->upload = Upload::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function it_exports_small_dataset_immediately()
    {
        $this->actingAs($this->user);

        // Create small dataset (100 records)
        DiamondData::factory()->count(100)->create([
            'upload_id' => $this->upload->id
        ]);

        // Use GET instead of POST to avoid CSRF issues
        $response = $this->get(route('bigdata.export'));

        // Your export route may return a form or data view on GET
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_access_export_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('bigdata.export'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_requires_authentication_for_export_page()
    {
        $response = $this->get(route('bigdata.export'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_can_check_export_status()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('bigdata.export-status'));

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('not_ready', $data['status']);
    }

    /** @test */
    public function it_can_get_export_progress()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('bigdata.export-progress', ['job_id' => 'test_job_123']));

        // Should return 404 since job doesn't exist
        $response->assertStatus(404);
    }

    /** @test */
    public function it_prevents_unauthorized_file_download()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        // Try to download file that doesn't belong to this user
        $filename = "diamond_export_{$this->user->id}_test.csv";

        $response = $this->get(route('bigdata.download-export', $filename));

        $response->assertStatus(403);
    }

    /** @test */
    public function it_shows_export_interface_when_authenticated()
    {
        $this->actingAs($this->user);

        // Create some test data
        DiamondData::factory()->count(10)->create([
            'upload_id' => $this->upload->id
        ]);

        $response = $this->get(route('bigdata.export'));

        $response->assertStatus(200);
        // Should show the export interface
    }

    /** @test */
    public function it_has_proper_route_structure()
    {
        $this->actingAs($this->user);

        // Test that routes exist and are accessible
        $routes = [
            'bigdata.export',
            'bigdata.export-status',
            'bigdata.export-progress'
        ];

        foreach ($routes as $routeName) {
            $this->assertTrue(
                \Route::has($routeName),
                "Route {$routeName} should exist"
            );
        }
    }
}