<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppServiceProviderConsoleBootTest extends TestCase
{
    public function test_console_commands_run_without_settings_table(): void
    {
        Schema::dropIfExists('settings');

        $this->artisan('list')
            ->assertExitCode(0);
    }
}
