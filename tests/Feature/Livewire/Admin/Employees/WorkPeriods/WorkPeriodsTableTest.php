<?php

namespace Tests\Feature\Livewire\Admin\Employees\WorkPeriods;

use App\Livewire\Admin\Employees\WorkPeriods\WorkPeriodsTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class WorkPeriodsTableTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(WorkPeriodsTable::class)
            ->assertStatus(200);
    }
}
