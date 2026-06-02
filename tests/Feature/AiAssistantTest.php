<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_ai_assistant_page(): void
    {
        $user = $this->adminUser();

        $this
            ->actingAs($user)
            ->get(route('admin.ai-assistant.index'))
            ->assertOk()
            ->assertSee('AI Assistant')
            ->assertSee('aiWidgetTrigger');
    }

    public function test_admin_can_ask_ai_assistant_in_local_mode(): void
    {
        $user = $this->adminUser();

        config(['services.ai_assistant.api_key' => null]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('admin.ai-assistant.ask'), [
                'message' => 'Tóm tắt tình hình hôm nay',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('mode', 'local')
            ->assertJsonStructure([
                'answer',
                'mode',
                'context' => [
                    'total_orders',
                    'open_orders',
                    'total_trackings',
                    'open_trackings',
                    'warehouse_transactions_this_month',
                    'open_production_orders',
                    'open_purchase_orders',
                ],
            ]);
    }

    private function adminUser(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($adminRole);

        return $user;
    }
}
