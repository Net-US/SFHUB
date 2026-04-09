<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Asset;
use App\Models\FinanceAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AssetCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ==================== PHYSICAL ASSETS ====================

    public function test_can_create_physical_asset()
    {
        $data = [
            'name' => 'MacBook Pro 14"',
            'type' => 'electronics',
            'purchase_value' => 25000000,
            'current_value' => 22000000,
            'purchase_date' => now()->subMonths(6)->toDateString(),
            'description' => 'Laptop untuk kerja',
            'location' => 'Ruang Kerja',
            'serial_number' => 'MBP123456789',
            'is_insured' => true,
            'insurance_expiry' => now()->addMonths(12)->toDateString(),
            'notes' => 'Dibeli di iBox'
        ];

        $response = $this->postJson('/assets', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Aset berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('assets', [
            'user_id' => $this->user->id,
            'name' => 'MacBook Pro 14"',
            'type' => 'electronics',
            'purchase_value' => 25000000,
            'current_value' => 22000000,
            'is_insured' => true
        ]);
    }

    public function test_can_update_physical_asset()
    {
        $asset = Asset::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'MacBook Pro Updated',
            'type' => 'electronics',
            'current_value' => 20000000,
            'description' => 'Updated description',
            'location' => 'Kamar Tidur',
            'notes' => 'Updated notes'
        ];

        $response = $this->putJson("/assets/{$asset->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Aset berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'name' => 'MacBook Pro Updated',
            'current_value' => 20000000,
            'condition' => 'Good'
        ]);
    }

    public function test_can_update_asset_value()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'current_value' => 10000000
        ]);

        $data = [
            'current_value' => 12000000
        ];

        $response = $this->patchJson("/assets/{$asset->id}/value", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Nilai aset diupdate.',
                'current_value' => 12000000.0
            ]);

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'current_value' => 12000000
        ]);
    }

    public function test_can_delete_physical_asset()
    {
        $asset = Asset::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Aset berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_can_show_asset_details()
    {
        $asset = Asset::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'asset' => [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'category' => $asset->category
                ]
            ]);
    }

    // ==================== FINANCE ACCOUNTS (via AssetController) ====================

    public function test_can_create_finance_account_via_asset_controller()
    {
        $data = [
            'name' => 'Mandiri',
            'type' => 'bank',
            'account_number' => '1234567890',
            'balance' => 5000000,
            'color' => '#3b82f6',
            'notes' => 'Account untuk gaji'
        ];

        $response = $this->postJson('/assets/accounts', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('finance_accounts', [
            'user_id' => $this->user->id,
            'name' => 'Mandiri',
            'type' => 'bank',
            'balance' => 5000000
        ]);
    }

    public function test_can_update_finance_account_via_asset_controller()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'BCA Updated',
            'account_number' => '0987654321',
            'color' => '#ef4444',
            'notes' => 'Updated notes',
            'is_active' => false
        ];

        $response = $this->putJson("/assets/accounts/{$account->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('finance_accounts', [
            'id' => $account->id,
            'name' => 'BCA Updated',
            'is_active' => false
        ]);
    }

    public function test_can_update_account_balance_via_asset_controller()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 2000000]);

        $data = [
            'balance' => 3000000
        ];

        $response = $this->patchJson("/assets/accounts/{$account->id}/balance", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Saldo berhasil diupdate.',
                'new_balance' => 3000000.0
            ]);

        $this->assertDatabaseHas('finance_accounts', [
            'id' => $account->id,
            'balance' => 3000000
        ]);
    }

    public function test_can_delete_finance_account_via_asset_controller()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/assets/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('finance_accounts', ['id' => $account->id]);
    }

    // ==================== ASSETS SUMMARY ====================

    public function test_get_assets_summary()
    {
        // Create physical assets
        Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 12000000
        ]);

        Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 5000000,
            'current_value' => 4000000
        ]);

        // Create finance accounts
        FinanceAccount::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'cash',
            'balance' => 2000000
        ]);

        FinanceAccount::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'bank',
            'balance' => 8000000
        ]);

        $response = $this->getJson('/assets/summary');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'physical_assets' => [
                    'total_purchase' => 15000000.0, // 10000000 + 5000000
                    'total_current' => 16000000.0, // 12000000 + 4000000
                    'total_appreciation' => 1000000.0, // 16000000 - 15000000
                    'count' => 2
                ],
                'accounts' => [
                    'cash' => 2000000.0,
                    'bank' => 8000000.0,
                    'e_wallet' => 0.0,
                    'investment' => 0.0,
                    'receivable' => 0.0,
                    'total' => 10000000.0
                ]
            ]);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_cannot_access_other_users_assets()
    {
        $otherUser = User::factory()->create();
        $otherAsset = Asset::factory()->create(['user_id' => $otherUser->id]);

        // Try to update other user's asset
        $response = $this->putJson("/assets/{$otherAsset->id}", [
            'name' => 'Hacked Asset'
        ]);

        $response->assertStatus(404);

        // Try to delete other user's asset
        $response = $this->deleteJson("/assets/{$otherAsset->id}");

        $response->assertStatus(404);

        // Try to get other user's asset details
        $response = $this->getJson("/assets/{$otherAsset->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_access_other_users_accounts_via_asset_controller()
    {
        $otherUser = User::factory()->create();
        $otherAccount = FinanceAccount::factory()->create(['user_id' => $otherUser->id]);

        // Try to update other user's account
        $response = $this->putJson("/assets/accounts/{$otherAccount->id}", [
            'name' => 'Hacked Account'
        ]);

        $response->assertStatus(404);

        // Try to delete other user's account
        $response = $this->deleteJson("/assets/accounts/{$otherAccount->id}");

        $response->assertStatus(404);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_physical_asset_validation()
    {
        // Test required fields
        $response = $this->postJson('/assets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'purchase_price', 'current_value', 'purchase_date', 'condition']);

        // Test invalid category
        $response = $this->postJson('/assets', [
            'name' => 'Test Asset',
            'category' => 'invalid_category',
            'purchase_price' => 1000000,
            'current_value' => 1000000,
            'purchase_date' => now()->toDateString(),
            'condition' => 'Good'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);

        // Test negative purchase price
        $response = $this->postJson('/assets', [
            'name' => 'Test Asset',
            'category' => 'electronics',
            'purchase_price' => -1000000,
            'current_value' => 1000000,
            'purchase_date' => now()->toDateString(),
            'condition' => 'Good'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purchase_price']);

        // Test negative current value
        $response = $this->postJson('/assets', [
            'name' => 'Test Asset',
            'category' => 'electronics',
            'purchase_price' => 1000000,
            'current_value' => -1000000,
            'purchase_date' => now()->toDateString(),
            'condition' => 'Good'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_value']);

        // Test invalid condition
        $response = $this->postJson('/assets', [
            'name' => 'Test Asset',
            'category' => 'electronics',
            'purchase_price' => 1000000,
            'current_value' => 1000000,
            'purchase_date' => now()->toDateString(),
            'condition' => 'invalid_condition'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['condition']);

        // Test future purchase date
        $response = $this->postJson('/assets', [
            'name' => 'Test Asset',
            'category' => 'electronics',
            'purchase_price' => 1000000,
            'current_value' => 1000000,
            'purchase_date' => now()->addDay()->toDateString(),
            'condition' => 'Good'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purchase_date']);
    }

    public function test_finance_account_validation_via_asset_controller()
    {
        // Test required fields
        $response = $this->postJson('/assets/accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'balance']);

        // Test invalid type
        $response = $this->postJson('/assets/accounts', [
            'name' => 'Test Account',
            'type' => 'invalid_type',
            'balance' => 1000000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Test negative balance
        $response = $this->postJson('/assets/accounts', [
            'name' => 'Test Account',
            'type' => 'cash',
            'balance' => -1000000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balance']);
    }

    // ==================== BUSINESS LOGIC TESTS ====================

    public function test_asset_appreciation_calculation()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 12000000
        ]);

        $this->assertEquals(2000000, $asset->getAppreciation());
        $this->assertEquals(20.0, $asset->getAppreciationPercentage());
        $this->assertTrue($asset->hasAppreciated());
    }

    public function test_asset_depreciation_calculation()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 8000000
        ]);

        $this->assertEquals(-2000000, $asset->getAppreciation());
        $this->assertEquals(-20.0, $asset->getAppreciationPercentage());
        $this->assertFalse($asset->hasAppreciated());
    }

    public function test_asset_age_calculation()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_date' => now()->subDays(365)
        ]);

        $this->assertEquals(365, $asset->getAgeInDays());
        $this->assertEquals(12, $asset->getAgeInMonths());
        $this->assertEquals(1, $asset->getAgeInYears());
    }

    public function test_warranty_expiry_alert()
    {
        // Asset with warranty expiring in 15 days
        $asset1 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'warranty_expiry' => now()->addDays(15)
        ]);

        $this->assertTrue($asset1->isWarrantyExpiringSoon(30));
        $this->assertFalse($asset1->isWarrantyExpired());

        // Asset with expired warranty
        $asset2 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'warranty_expiry' => now()->subDays(10)
        ]);

        $this->assertFalse($asset2->isWarrantyExpiringSoon(30));
        $this->assertTrue($asset2->isWarrantyExpired());

        // Asset with no warranty
        $asset3 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'warranty_expiry' => null
        ]);

        $this->assertFalse($asset3->isWarrantyExpiringSoon(30));
        $this->assertFalse($asset3->isWarrantyExpired());
    }

    public function test_insurance_expiry_alert()
    {
        // Asset with insurance expiring in 20 days
        $asset1 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'is_insured' => true,
            'insurance_expiry' => now()->addDays(20)
        ]);

        $this->assertTrue($asset1->isInsuranceExpiringSoon(30));
        $this->assertFalse($asset1->isInsuranceExpired());

        // Asset with expired insurance
        $asset2 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'is_insured' => true,
            'insurance_expiry' => now()->subDays(5)
        ]);

        $this->assertFalse($asset2->isInsuranceExpiringSoon(30));
        $this->assertTrue($asset2->isInsuranceExpired());

        // Asset without insurance
        $asset3 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'is_insured' => false
        ]);

        $this->assertFalse($asset3->isInsuranceExpiringSoon(30));
        $this->assertFalse($asset3->isInsuranceExpired());
    }

    public function test_asset_status_color()
    {
        // Appreciated asset
        $asset1 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 12000000
        ]);

        $this->assertEquals('text-emerald-600', $asset1->getStatusColor());

        // Depreciated asset
        $asset2 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 8000000
        ]);

        $this->assertEquals('text-rose-600', $asset2->getStatusColor());

        // Stable asset
        $asset3 = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 10000000
        ]);

        $this->assertEquals('text-stone-600', $asset3->getStatusColor());
    }

    public function test_cannot_delete_account_with_transactions_via_asset_controller()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        // Create a transaction for this account
        \App\Models\Transaction::factory()->create([
            'finance_account_id' => $account->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/assets/accounts/{$account->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun tidak bisa dihapus karena sudah ada transaksi.'
            ]);
    }

    public function test_asset_formatted_values()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'purchase_price' => 10000000,
            'current_value' => 12000000
        ]);

        $this->assertEquals('Rp 10.000.000', $asset->getFormattedPurchasePrice());
        $this->assertEquals('Rp 12.000.000', $asset->getFormattedCurrentValue());
        $this->assertEquals('+Rp 2.000.000', $asset->getFormattedAppreciation());
    }

    public function test_asset_condition_colors()
    {
        $excellentAsset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'condition' => 'Excellent'
        ]);

        $goodAsset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'condition' => 'Good'
        ]);

        $fairAsset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'condition' => 'Fair'
        ]);

        $poorAsset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'condition' => 'Poor'
        ]);

        $this->assertEquals('text-emerald-600', $excellentAsset->getConditionColor());
        $this->assertEquals('text-blue-600', $goodAsset->getConditionColor());
        $this->assertEquals('text-amber-600', $fairAsset->getConditionColor());
        $this->assertEquals('text-rose-600', $poorAsset->getConditionColor());
    }
}
