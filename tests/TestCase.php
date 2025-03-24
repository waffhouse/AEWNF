<?php

namespace Tests;

use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Add custom assertions for Livewire/Volt components
        TestResponse::macro('assertSeeLivewire', function ($component) {
            $this->assertSee('wire:id');
            $this->assertSee($component);
            return $this;
        });
        
        TestResponse::macro('assertSeeVolt', function ($component) {
            $this->assertSee('wire:id');
            return $this;
        });
    }
    
    /**
     * Seed the database with test data
     */
    protected function seedTestDatabase()
    {
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);
    }
}
