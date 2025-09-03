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

        // Based on your controller, export method handles both GET and logic internally
        $response = $this->post(route('bigdata.export'));

        $response->assertStatus(200);

        // Should return a file download
        $this->assertTrue(
            str_contains($response->headers->get('content-type'), 'spreadsheet') ||
            str_contains($response->headers->get('content-type'), 'csv') ||
            str_contains($response->headers->get('content-disposition'), 'attachment')
        );
    }

    /** @test */
    public function it_queues_large_dataset_export()
    {
        Queue::fake();
        $this->actingAs($this->user);

        // Mock DiamondData to simulate large dataset
        $this->mock(\App\Models\DiamondData::class, function ($mock) {
            $mock->shouldReceive('filter')->andReturnSelf();
            $mock->shouldReceive('count')->andReturn(100000); // Large dataset
        });

        $response = $this->post(route('bigdata.export'));

        $response->assertStatus(202);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertTrue($data['queued']);
        $this->assertArrayHasKey('job_id', $data);

        // Assert job was queued
        Queue::assertPushed(ExportLargeDataJob::class);
    }

    /** @test */
    public function it_returns_error_when_no_data_found()
    {
        $this->actingAs($this->user);

        // POST to export with filters that won't match any data
        $response = $this->post(route('bigdata.export'), [
            'cut' => 'NonExistentCut'
        ]);

        $response->assertStatus(400);

        $data = $response->json();
        $this->assertFalse($data['success']);
        $this->assertEquals('No data found matching your filters.', $data['message']);
    }

    /** @test */
    public function it_applies_filters_to_export()
    {
        $this->actingAs($this->user);

        // Create test data with specific attributes
        DiamondData::factory()->create([
            'upload_id' => $this->upload->id,
            'cut' => 'Round',
            'color' => 'D'
        ]);

        DiamondData::factory()->create([
            'upload_id' => $this->upload->id,
            'cut' => 'Princess',
            'color' => 'E'
        ]);

        $response = $this->post(route('bigdata.export'), [
            'cut' => 'Round',
            'color' => 'D'
        ]);

        $response->assertStatus(200);
        // Should successfully export the filtered data
    }

    /** @test */
    public function it_requires_authentication_for_export()
    {
        $response = $this->post(route('bigdata.export'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_handles_memory_error_by_queuing_job()
    {
        Queue::fake();
        $this->actingAs($this->user);

        // Create some data first
        DiamondData::factory()->count(10)->create([
            'upload_id' => $this->upload->id
        ]);

        // Mock DiamondData to simulate memory error
        $this->mock(\App\Models\DiamondData::class, function ($mock) {
            $mock->shouldReceive('filter')->andReturnSelf();
            $mock->shouldReceive('count')->andReturn(80000);
            $mock->shouldReceive('with')->andReturnSelf();
            $mock->shouldReceive('select')->andReturnSelf();
            $mock->shouldReceive('where')->andReturnSelf();
            $mock->shouldReceive('orderBy')->andReturnSelf();
            $mock->shouldReceive('limit')->andReturnSelf();
            $mock->shouldReceive('get')->andThrow(new \Exception('Allowed memory size exhausted'));
        });

        $response = $this->post(route('bigdata.export'));

        // Should fallback to queued job
        $response->assertStatus(202);
        Queue::assertPushed(ExportLargeDataJob::class);
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
}