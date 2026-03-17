<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InvestmentInstrument;
use App\Models\InvestmentPurchase;
use App\Models\FinanceAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvestmentCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ==================== INVESTMENT INSTRUMENTS ====================

    public function test_can_create_investment_instrument()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);

        $data = [
            'name' => 'Reksadana Saham ABC',
            'type' => 'mutual_fund',
            'finance_account_id' => $account->id,
            'current_price' => 1500.50,
            'currency' => 'IDR',
            'description' => 'Reksadana dengan fokus saham blue chip',
            'risk_level' => 'medium',
            'expected_return' => 15.5,
            'is_active' => true
        ];

        $response = $this->postJson('/investments', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Instrumen investasi berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('investment_instruments', [
            'user_id' => $this->user->id,
            'name' => 'Reksadana Saham ABC',
            'type' => 'mutual_fund',
            'current_price' => 1500.50,
            'risk_level' => 'medium'
        ]);
    }

    public function test_can_update_investment_instrument()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id
        ]);

        $data = [
            'name' => 'Reksadana Saham Updated',
            'current_price' => 1600.75,
            'currency' => 'IDR',
            'description' => 'Updated description',
            'risk_level' => 'high',
            'expected_return' => 18.0,
            'is_active' => false
        ];

        $response = $this->putJson("/investments/{$instrument->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Instrumen investasi berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('investment_instruments', [
            'id' => $instrument->id,
            'name' => 'Reksadana Saham Updated',
            'current_price' => 1600.75,
            'risk_level' => 'high',
            'is_active' => false
        ]);
    }

    public function test_can_update_instrument_price()
    {
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'current_price' => 1000.00
        ]);

        $data = [
            'current_price' => 1200.50
        ];

        $response = $this->patchJson("/investments/{$instrument->id}/price", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Harga instrumen berhasil diupdate.',
                'current_price' => 1200.50
            ]);

        $this->assertDatabaseHas('investment_instruments', [
            'id' => $instrument->id,
            'current_price' => 1200.50
        ]);
    }

    public function test_can_delete_investment_instrument()
    {
        $instrument = InvestmentInstrument::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/investments/{$instrument->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Instrumen investasi berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('investment_instruments', ['id' => $instrument->id]);
    }

    public function test_can_show_investment_instrument()
    {
        $instrument = InvestmentInstrument::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/investments/{$instrument->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'instrument' => [
                    'id' => $instrument->id,
                    'name' => $instrument->name,
                    'type' => $instrument->type
                ]
            ]);
    }

    // ==================== INVESTMENT PURCHASES ====================

    public function test_can_create_investment_purchase()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id,
            'current_price' => 1000.00
        ]);

        $data = [
            'quantity' => 100,
            'unit_price' => 950.00,
            'total_amount' => 95000,
            'purchase_date' => now()->toDateString(),
            'notes' => 'Pembelian pertama',
            'fees' => 1000
        ];

        $response = $this->postJson("/investments/{$instrument->id}/purchases", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembelian investasi berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('investment_purchases', [
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id,
            'quantity' => 100,
            'unit_price' => 950.00,
            'total_amount' => 95000,
            'fees' => 1000
        ]);
    }

    public function test_can_delete_investment_purchase()
    {
        $instrument = InvestmentInstrument::factory()->create(['user_id' => $this->user->id]);
        $purchase = InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/investments/purchases/{$purchase->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembelian investasi berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('investment_purchases', ['id' => $purchase->id]);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_cannot_access_other_users_investments()
    {
        $otherUser = User::factory()->create();
        $otherInstrument = InvestmentInstrument::factory()->create(['user_id' => $otherUser->id]);

        // Try to update other user's instrument
        $response = $this->putJson("/investments/{$otherInstrument->id}", [
            'name' => 'Hacked Investment'
        ]);

        $response->assertStatus(404);

        // Try to delete other user's instrument
        $response = $this->deleteJson("/investments/{$otherInstrument->id}");

        $response->assertStatus(404);

        // Try to add purchase to other user's instrument
        $response = $this->postJson("/investments/{$otherInstrument->id}/purchases", [
            'quantity' => 100,
            'unit_price' => 1000,
            'total_amount' => 100000,
            'purchase_date' => now()->toDateString()
        ]);

        $response->assertStatus(404);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_investment_instrument_validation()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);

        // Test required fields
        $response = $this->postJson('/investments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'finance_account_id', 'current_price']);

        // Test invalid type
        $response = $this->postJson('/investments', [
            'name' => 'Test Investment',
            'type' => 'invalid_type',
            'finance_account_id' => $account->id,
            'current_price' => 1000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Test negative price
        $response = $this->postJson('/investments', [
            'name' => 'Test Investment',
            'type' => 'stock',
            'finance_account_id' => $account->id,
            'current_price' => -1000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_price']);

        // Test invalid risk level
        $response = $this->postJson('/investments', [
            'name' => 'Test Investment',
            'type' => 'stock',
            'finance_account_id' => $account->id,
            'current_price' => 1000,
            'risk_level' => 'invalid_risk'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['risk_level']);

        // Test invalid expected return (must be between 0 and 100)
        $response = $this->postJson('/investments', [
            'name' => 'Test Investment',
            'type' => 'stock',
            'finance_account_id' => $account->id,
            'current_price' => 1000,
            'expected_return' => 150
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expected_return']);
    }

    public function test_investment_purchase_validation()
    {
        $instrument = InvestmentInstrument::factory()->create(['user_id' => $this->user->id]);

        // Test required fields
        $response = $this->postJson("/investments/{$instrument->id}/purchases", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity', 'unit_price', 'total_amount', 'purchase_date']);

        // Test zero quantity
        $response = $this->postJson("/investments/{$instrument->id}/purchases", [
            'quantity' => 0,
            'unit_price' => 1000,
            'total_amount' => 0,
            'purchase_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // Test negative unit price
        $response = $this->postJson("/investments/{$instrument->id}/purchases", [
            'quantity' => 100,
            'unit_price' => -1000,
            'total_amount' => -100000,
            'purchase_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price']);

        // Test negative total amount
        $response = $this->postJson("/investments/{$instrument->id}/purchases", [
            'quantity' => 100,
            'unit_price' => 1000,
            'total_amount' => -100000,
            'purchase_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_amount']);

        // Test future purchase date
        $response = $this->postJson("/investments/{$instrument->id}/purchases", [
            'quantity' => 100,
            'unit_price' => 1000,
            'total_amount' => 100000,
            'purchase_date' => now()->addDay()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purchase_date']);
    }

    // ==================== BUSINESS LOGIC TESTS ====================

    public function test_investment_total_value_calculation()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id,
            'current_price' => 1500.00
        ]);

        // Add first purchase
        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id,
            'quantity' => 100,
            'unit_price' => 1000.00,
            'total_amount' => 100000
        ]);

        // Add second purchase
        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id,
            'quantity' => 50,
            'unit_price' => 1200.00,
            'total_amount' => 60000
        ]);

        $instrument->refresh();
        
        // Total quantity should be 150 (100 + 50)
        $this->assertEquals(150, $instrument->total_quantity);
        
        // Total value should be 150 * 1500 = 225000
        $this->assertEquals(225000, $instrument->total_value);
        
        // Total investment should be 100000 + 60000 = 160000
        $this->assertEquals(160000, $instrument->total_investment);
        
        // Profit should be 225000 - 160000 = 65000
        $this->assertEquals(65000, $instrument->total_profit);
    }

    public function test_investment_profit_percentage_calculation()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id,
            'current_price' => 1200.00
        ]);

        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id,
            'quantity' => 100,
            'unit_price' => 1000.00,
            'total_amount' => 100000
        ]);

        $instrument->refresh();
        
        // Current value: 100 * 1200 = 120000
        // Investment: 100000
        // Profit: 20000
        // Profit percentage: (20000 / 100000) * 100 = 20%
        $this->assertEquals(20.0, $instrument->profit_percentage);
    }

    public function test_cannot_create_purchase_for_inactive_instrument()
    {
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false
        ]);

        $response = $this->postJson("/investments/{$instrument->id}/purchases", [
            'quantity' => 100,
            'unit_price' => 1000,
            'total_amount' => 100000,
            'purchase_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat melakukan pembelian untuk instrumen yang tidak aktif.'
            ]);
    }

    public function test_investment_account_must_be_investment_type()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'bank']);

        $response = $this->postJson('/investments', [
            'name' => 'Test Investment',
            'type' => 'stock',
            'finance_account_id' => $account->id,
            'current_price' => 1000
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun yang dipilih harus bertipe investment.'
            ]);
    }

    public function test_investment_summary_api()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment', 'balance' => 500000]);
        
        $instrument1 = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'current_price' => 1500.00
        ]);
        
        $instrument2 = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'current_price' => 2000.00
        ]);

        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument1->id,
            'user_id' => $this->user->id,
            'quantity' => 100,
            'unit_price' => 1000.00,
            'total_amount' => 100000
        ]);

        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument2->id,
            'user_id' => $this->user->id,
            'quantity' => 50,
            'unit_price' => 1800.00,
            'total_amount' => 90000
        ]);

        $response = $this->getJson('/investments/summary');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'summary' => [
                    'total_investment' => 190000.0, // 100000 + 90000
                    'total_current_value' => 250000.0, // (100 * 1500) + (50 * 2000)
                    'total_profit' => 60000.0, // 250000 - 190000
                    'profit_percentage' => 31.58 // (60000 / 190000) * 100
                ]
            ]);
    }

    public function test_investment_performance_tracking()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'type' => 'investment']);
        $instrument = InvestmentInstrument::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id,
            'current_price' => 1000.00,
            'expected_return' => 15.0
        ]);

        // Purchase at 1000
        InvestmentPurchase::factory()->create([
            'investment_instrument_id' => $instrument->id,
            'user_id' => $this->user->id,
            'quantity' => 100,
            'unit_price' => 1000.00,
            'total_amount' => 100000,
            'purchase_date' => now()->subMonths(6)
        ]);

        // Update price to 1200 (20% gain)
        $instrument->update(['current_price' => 1200.00]);
        $instrument->refresh();

        // Should show performance vs expected return
        $this->assertGreaterThan($instrument->expected_return, $instrument->profit_percentage);
        $this->assertTrue($instrument->isPerformingWell());
    }
}
