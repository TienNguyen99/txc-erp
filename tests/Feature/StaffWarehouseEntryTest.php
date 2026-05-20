<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\WarehouseDocument;
use App\Models\WarehouseTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffWarehouseEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_receipt_creates_printable_warehouse_document(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $user = User::factory()->create(['name' => 'Kho 01']);
        $user->assignRole($staffRole);

        Order::create([
            'job_no' => 'JOB-STAFF-001',
            'ma_hh' => 'HH-ST-001',
            'color' => 'BLACK',
            'yrd' => 50,
            'lenh_sanxuat' => 'LSX-STAFF-001',
            'status' => 'in_production',
        ]);

        $response = $this->actingAs($user)
            ->post(route('staff.warehouse.store'), [
                'lenh_sx' => 'LSX-STAFF-001',
                'ngay' => '2026-05-20',
                'rows' => [
                    [
                        'ma_hh' => 'HH-ST-001',
                        'mau' => 'BLACK',
                        'size' => 'M',
                        'so_luong' => 50,
                    ],
                ],
            ]);

        $document = WarehouseDocument::with('items')->first();

        $response->assertRedirect(route('staff.warehouse.index', ['lenh_sx' => 'LSX-STAFF-001']));
        $this->assertNotNull($document);
        $this->assertSame('NHAPKHO', $document->type);
        $this->assertSame('Kho 01', $document->createdBy?->name);
        $this->assertSame(1, $document->items->count());
        $this->assertSame(1, WarehouseTransaction::where('warehouse_document_id', $document->id)->count());
    }
}
