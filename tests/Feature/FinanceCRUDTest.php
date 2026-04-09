<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FinanceAccount;
use App\Models\Transaction;
use App\Models\SavingsGoal;
use App\Models\Budget;
use App\Models\PendingNeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinanceCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ==================== FINANCE ACCOUNTS ====================

    public function test_can_create_finance_account()
    {
        $data = [
            'name' => 'BCA',
            'type' => 'bank',
            'account_number' => '1234567890',
            'balance' => 1000000,
            'color' => '#3b82f6',
            'notes' => 'Account utama'
        ];

        $response = $this->postJson('/finance/accounts', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('finance_accounts', [
            'user_id' => $this->user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'balance' => 1000000
        ]);
    }

    public function test_can_update_finance_account()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'BCA Updated',
            'account_number' => '0987654321',
            'color' => '#ef4444',
            'notes' => 'Updated notes',
            'is_active' => false
        ];

        $response = $this->putJson("/finance/accounts/{$account->id}", $data);

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

    public function test_can_update_account_balance()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 1000000]);

        $response = $this->patchJson("/finance/accounts/{$account->id}/balance", [
            'balance' => 2000000
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Saldo berhasil diupdate.',
                'new_balance' => 2000000.0
            ]);

        $this->assertDatabaseHas('finance_accounts', [
            'id' => $account->id,
            'balance' => 2000000
        ]);
    }

    public function test_can_delete_finance_account()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/finance/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Akun berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('finance_accounts', ['id' => $account->id]);
    }

    public function test_cannot_delete_account_with_transactions()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);
        Transaction::factory()->create(['finance_account_id' => $account->id, 'user_id' => $this->user->id]);

        $response = $this->deleteJson("/finance/accounts/{$account->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun tidak dapat dihapus karena sudah memiliki transaksi.'
            ]);
    }

    // ==================== TRANSACTIONS ====================

    public function test_can_create_income_transaction()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'type' => 'income',
            'finance_account_id' => $account->id,
            'amount' => 500000,
            'category' => 'Salary',
            'description' => 'Gaji bulanan',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Bonus included'
        ];

        $response = $this->postJson('/finance/transactions', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.'
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 500000,
            'category' => 'Salary'
        ]);
    }

    public function test_can_create_expense_transaction()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 1000000]);

        $data = [
            'type' => 'expense',
            'finance_account_id' => $account->id,
            'amount' => 200000,
            'category' => 'Food',
            'description' => 'Makan siang',
            'transaction_date' => now()->toDateString()
        ];

        $response = $this->postJson('/finance/transactions', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 200000,
            'category' => 'Food'
        ]);
    }

    public function test_can_create_transfer_transaction()
    {
        $fromAccount = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 1000000]);
        $toAccount = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 500000]);

        $data = [
            'type' => 'transfer',
            'finance_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 300000,
            'fee' => 5000,
            'description' => 'Transfer ke tabungan',
            'transaction_date' => now()->toDateString()
        ];

        $response = $this->postJson('/finance/transactions', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'transfer',
            'amount' => 300000,
            'fee' => 5000
        ]);
    }

    public function test_can_delete_transaction()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->create([
            'finance_account_id' => $account->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->deleteJson("/finance/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    // ==================== SAVINGS GOALS ====================

    public function test_can_create_savings_goal()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'Liburan ke Bali',
            'finance_account_id' => $account->id,
            'target_amount' => 5000000,
            'daily_saving' => 100000,
            'target_date' => now()->addMonths(6)->toDateString(),
            'notes' => 'Target liburan keluarga'
        ];

        $response = $this->postJson('/finance/savings-goals', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Target tabungan berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('savings_goals', [
            'user_id' => $this->user->id,
            'name' => 'Liburan ke Bali',
            'target_amount' => 5000000
        ]);
    }

    public function test_can_update_savings_goal()
    {
        $goal = SavingsGoal::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'Liburan ke Jepang',
            'target_amount' => 10000000,
            'current_amount' => 2000000,
            'daily_saving' => 150000,
            'status' => 'active'
        ];

        $response = $this->putJson("/finance/savings-goals/{$goal->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Target tabungan berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('savings_goals', [
            'id' => $goal->id,
            'name' => 'Liburan ke Jepang',
            'target_amount' => 10000000,
            'current_amount' => 2000000
        ]);
    }

    public function test_can_delete_savings_goal()
    {
        $goal = SavingsGoal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/finance/savings-goals/{$goal->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Target tabungan berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('savings_goals', ['id' => $goal->id]);
    }

    // ==================== BUDGETS ====================

    public function test_can_create_budget()
    {
        $data = [
            'category' => 'Makanan',
            'amount' => 2000000,
            'period' => 'monthly',
            'alert_threshold' => 80
        ];

        $response = $this->postJson('/finance/budgets', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Budget berhasil ditambahkan.'
            ]);

        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category' => 'Makanan',
            'amount' => 2000000,
            'period' => 'monthly'
        ]);
    }

    public function test_cannot_create_duplicate_budget()
    {
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'category' => 'Makanan',
            'period' => 'monthly'
        ]);

        $data = [
            'category' => 'Makanan',
            'amount' => 2000000,
            'period' => 'monthly'
        ];

        $response = $this->postJson('/finance/budgets', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Budget untuk kategori "Makanan" sudah ada.'
            ]);
    }

    public function test_can_update_budget()
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'amount' => 3000000,
            'alert_threshold' => 75,
            'is_active' => true
        ];

        $response = $this->putJson("/finance/budgets/{$budget->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Budget berhasil diupdate.'
            ]);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 3000000,
            'alert_threshold' => 75
        ]);
    }

    public function test_can_delete_budget()
    {
        $budget = Budget::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/finance/budgets/{$budget->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Budget berhasil dihapus.'
            ]);

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    // ==================== PENDING NEEDS ====================

    public function test_can_create_pending_need()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'Laptop baru',
            'finance_account_id' => $account->id,
            'amount' => 8000000,
            'category' => 'Elektronik',
            'notes' => 'Untuk kerja'
        ];

        $response = $this->postJson('/finance/pending-needs', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kebutuhan ditambahkan. Saldo tersedia otomatis berkurang.'
            ]);

        $this->assertDatabaseHas('pending_needs', [
            'user_id' => $this->user->id,
            'name' => 'Laptop baru',
            'amount' => 8000000,
            'status' => 'pending'
        ]);
    }

    public function test_can_purchase_pending_need()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);
        $need = PendingNeed::factory()->create([
            'user_id' => $this->user->id,
            'finance_account_id' => $account->id,
            'status' => 'pending'
        ]);

        $data = [
            'transaction_date' => now()->toDateString(),
            'notes' => 'Sudah dibeli di toko'
        ];

        $response = $this->postJson("/finance/pending-needs/{$need->id}/purchase", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Pembelian dicatat sebagai transaksi pengeluaran.'
            ]);

        $this->assertDatabaseHas('pending_needs', [
            'id' => $need->id,
            'status' => 'purchased'
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => $need->amount,
            'description' => $need->name
        ]);
    }

    public function test_can_cancel_pending_need()
    {
        $need = PendingNeed::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/finance/pending-needs/{$need->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kebutuhan dibatalkan. Saldo tersedia kembali normal.'
            ]);

        $this->assertDatabaseHas('pending_needs', [
            'id' => $need->id,
            'status' => 'cancelled'
        ]);
    }

    // ==================== TRANSFERS ====================

    public function test_can_create_transfer()
    {
        $fromAccount = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 1000000]);
        $toAccount = FinanceAccount::factory()->create(['user_id' => $this->user->id, 'balance' => 500000]);

        $data = [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 200000,
            'fee' => 2500,
            'description' => 'Transfer antar rekening',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Transfer rutin'
        ];

        $response = $this->postJson('/finance/transfer', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transfer berhasil dicatat.'
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'transfer',
            'amount' => 200000,
            'fee' => 2500,
            'finance_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id
        ]);
    }

    public function test_cannot_transfer_to_same_account()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'from_account_id' => $account->id,
            'to_account_id' => $account->id,
            'amount' => 100000,
            'transaction_date' => now()->toDateString()
        ];

        $response = $this->postJson('/finance/transfer', $data);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Akun asal dan tujuan tidak boleh sama.'
            ]);
    }

    // ==================== AUTHORIZATION TESTS ====================

    public function test_cannot_access_other_users_data()
    {
        $otherUser = User::factory()->create();
        $otherAccount = FinanceAccount::factory()->create(['user_id' => $otherUser->id]);

        // Try to update other user's account
        $response = $this->putJson("/finance/accounts/{$otherAccount->id}", [
            'name' => 'Hacked Account'
        ]);

        $response->assertStatus(404);

        // Try to delete other user's account
        $response = $this->deleteJson("/finance/accounts/{$otherAccount->id}");

        $response->assertStatus(404);
    }

    // ==================== VALIDATION TESTS ====================

    public function test_finance_account_validation()
    {
        // Test required fields
        $response = $this->postJson('/finance/accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'balance']);

        // Test invalid type
        $response = $this->postJson('/finance/accounts', [
            'name' => 'Test Account',
            'type' => 'invalid_type',
            'balance' => 1000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Test negative balance
        $response = $this->postJson('/finance/accounts', [
            'name' => 'Test Account',
            'type' => 'cash',
            'balance' => -1000
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balance']);
    }

    public function test_transaction_validation()
    {
        $account = FinanceAccount::factory()->create(['user_id' => $this->user->id]);

        // Test required fields
        $response = $this->postJson('/finance/transactions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'finance_account_id', 'amount', 'transaction_date']);

        // Test invalid type
        $response = $this->postJson('/finance/transactions', [
            'type' => 'invalid_type',
            'finance_account_id' => $account->id,
            'amount' => 1000,
            'transaction_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        // Test zero amount
        $response = $this->postJson('/finance/transactions', [
            'type' => 'income',
            'finance_account_id' => $account->id,
            'amount' => 0,
            'transaction_date' => now()->toDateString()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }
}
