<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DebtCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ==================== DEBTS ====================

    public function test_can_create_debt()
    {
        $data = [
            'debtor' => 'Fintech XYZ',
            'type' => 'payable',
            'amount' => 5000000,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(12)->toDateString(),
            'interest_rate' => 12.5,
            'description' => 'Pinjaman untuk modal usaha',
        ];

        $response = $this->postJson('/debts', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('debts', [
            'user_id' => $this->user->id,
            'debtor' => 'Fintech XYZ',
            'type' => 'payable',
            'amount' => 5000000,
            'status' => 'active'
        ]);
    }

    public function test_can_update_debt()
    {
        $debt = Debt::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'debtor' => 'Bank ABC Updated',
            'due_date' => now()->addMonths(18)->toDateString(),
            'interest_rate' => 10.0,
            'description' => 'Updated description',
            'status' => 'paid'
        ];

        $response = $this->putJson("/debts/{$debt->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Hutang berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'debtor' => 'Bank ABC Updated',
            'status' => 'paid'
        ]);
    }

    public function test_can_delete_debt()
    {
        $debt = Debt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/debts/{$debt->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Hutang berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('debts', ['id' => $debt->id]);
    }

    public function test_can_mark_debt_as_paid()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'remaining_amount' => 1000000
        ]);

        $response = $this->postJson("/debts/{$debt->id}/mark-paid");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Hutang berhasil ditandai sebagai lunas.'
            ]);

        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'status' => 'paid',
            'remaining_amount' => 0
        ]);
    }

    // ==================== DEBT PAYMENTS ====================

    public function test_can_add_debt_payment()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'remaining_amount' => 3000000
        ]);

        $data = [
            'amount' => 1000000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'transfer',
            'notes' => 'Pembayaran cicilan ke-3'
        ];

        $response = $this->postJson("/debts/{$debt->id}/payments", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembayaran berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('debt_payments', [
            'debt_id' => $debt->id,
            'amount' => 1000000,
            'payment_method' => 'transfer',
            'notes' => 'Pembayaran cicilan ke-3'
        ]);

        // Check if remaining amount is updated
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'remaining_amount' => 2000000
        ]);
    }

    public function test_can_get_debt_payments()
    {
        $debt = Debt::factory()->create(['user_id' => $this->user->id]);

        DebtPayment::factory()->count(3)->create([
            'debt_id' => $debt->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/debts/{$debt->id}/payments");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_delete_debt_payment()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'remaining_amount' => 2000000
        ]);

        $payment = DebtPayment::factory()->create([
            'debt_id' => $debt->id,
            'user_id' => $this->user->id,
            'amount' => 500000
        ]);

        $response = $this->deleteJson("/debts/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus.'
            ]);

        $this->assertSoftDeleted('debt_payments', ['id' => $payment->id]);

        // Check if remaining amount is restored
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'remaining_amount' => 2500000
        ]);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_cannot_access_other_users_debts()
    {
        $otherUser = User::factory()->create();
        $otherDebt = Debt::factory()->create(['user_id' => $otherUser->id]);

        // Try to update other user's debt
        $response = $this->putJson("/debts/{$otherDebt->id}", [
            'name' => 'Hacked Debt'
        ]);

        $response->assertStatus(404);

        // Try to delete other user's debt
        $response = $this->deleteJson("/debts/{$otherDebt->id}");

        $response->assertStatus(404);

        // Try to add payment to other user's debt
        $response = $this->postJson("/debts/{$otherDebt->id}/payments", [
            'amount' => 100000,
            'payment_date' => now()->toDateString()
        ]);

        $response->assertStatus(404);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_debt_validation()
    {
        // Test required fields
        $response = $this->postJson('/debts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'creditor', 'principal_amount', 'debt_type', 'due_date']);

        // Test invalid debt type
        $response = $this->postJson('/debts', [
            'name' => 'Test Debt',
            'creditor' => 'Test Creditor',
            'principal_amount' => 1000000,
            'debt_type' => 'invalid_type',
            'due_date' => now()->addMonths(6)->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['debt_type']);

        // Test negative amount
        $response = $this->postJson('/debts', [
            'name' => 'Test Debt',
            'creditor' => 'Test Creditor',
            'principal_amount' => -1000000,
            'debt_type' => 'loan',
            'due_date' => now()->addMonths(6)->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['principal_amount']);

        // Test past due date
        $response = $this->postJson('/debts', [
            'name' => 'Test Debt',
            'creditor' => 'Test Creditor',
            'principal_amount' => 1000000,
            'debt_type' => 'loan',
            'due_date' => now()->subDays(1)->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_debt_payment_validation()
    {
        $debt = Debt::factory()->create(['user_id' => $this->user->id]);

        // Test required fields
        $response = $this->postJson("/debts/{$debt->id}/payments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_date']);

        // Test zero amount
        $response = $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 0,
            'payment_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test negative amount
        $response = $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => -100000,
            'payment_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test payment amount exceeding remaining amount
        $debt->update(['remaining_amount' => 500000]);

        $response = $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 600000,
            'payment_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    // ==================== BUSINESS LOGIC TESTS ====================

    public function test_debt_remaining_amount_calculates_correctly()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 5000000,
            'remaining_amount' => 5000000
        ]);

        // Add first payment
        $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 1000000,
            'payment_date' => now()->toDateString()
        ]);

        $debt->refresh();
        $this->assertEquals(4000000, $debt->remaining_amount);

        // Add second payment
        $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 2000000,
            'payment_date' => now()->toDateString()
        ]);

        $debt->refresh();
        $this->assertEquals(2000000, $debt->remaining_amount);
    }

    public function test_debt_auto_marks_as_paid_when_fully_paid()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 3000000,
            'remaining_amount' => 3000000,
            'status' => 'active'
        ]);

        // Add payment that fully pays the debt
        $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 3000000,
            'payment_date' => now()->toDateString()
        ]);

        $debt->refresh();
        $this->assertEquals('paid', $debt->status);
        $this->assertEquals(0, $debt->remaining_amount);
    }

    public function test_cannot_add_payment_to_paid_debt()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'remaining_amount' => 0
        ]);

        $response = $this->postJson("/debts/{$debt->id}/payments", [
            'amount' => 100000,
            'payment_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menambahkan pembayaran ke hutang yang sudah lunas.'
            ]);
    }

    public function test_cannot_delete_payment_from_paid_debt()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 2000000,
            'remaining_amount' => 0,
            'status' => 'paid'
        ]);

        $payment = DebtPayment::factory()->create([
            'debt_id' => $debt->id,
            'user_id' => $this->user->id,
            'amount' => 2000000
        ]);

        $response = $this->deleteJson("/debts/payments/{$payment->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus pembayaran dari hutang yang sudah lunas.'
            ]);
    }

    public function test_debt_interest_calculation()
    {
        $debt = Debt::factory()->create([
            'user_id' => $this->user->id,
            'principal_amount' => 10000000,
            'interest_rate' => 12, // 12% per year
            'interest_type' => 'simple',
            'tenure_months' => 12
        ]);

        // For simple interest: Total = Principal + (Principal * Rate * Time)
        $expectedInterest = 10000000 * (12 / 100) * (12 / 12); // 1 year
        $expectedTotal = 10000000 + $expectedInterest;

        $this->assertEquals($expectedTotal, $debt->total_amount);
    }
}
