<?php

namespace App\Models;

use App\Models\Schedule;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'plan',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'preferences' => 'array',
    ];

    // Relationships
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function financeAccounts()
    {
        return $this->hasMany(FinanceAccount::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function investmentInstruments()
    {
        return $this->hasMany(InvestmentInstrument::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function productivityLogs()
    {
        return $this->hasMany(ProductivityLog::class);
    }

    public function projectStages()
    {
        return $this->hasMany(ProjectStage::class);
    }

    public function academicCourses()
    {
        return $this->hasMany(AcademicCourse::class);
    }

    public function pklLogs()
    {
        return $this->hasMany(PklLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function investmentPurchases()
    {
        return $this->hasMany(InvestmentPurchase::class);
    }

    // Methods
    public function getTotalAssets()
    {
        return $this->financeAccounts()->sum('balance');
    }

    public function getTotalInvestments()
    {
        return $this->investmentInstruments()->sum('total_invested');
    }

    public function getTotalDebts()
    {
        return $this->debts()->where('type', 'payable')->sum('amount');
    }

    public function getNetWorth()
    {
        return $this->getTotalAssets() + $this->getTotalInvestments() - $this->getTotalDebts();
    }
    /**
     * Relationship ke Schedule
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relationship with Subtasks
     */
    public function subtasks()
    {
        return $this->hasMany(SubTask::class);
    }


    /**
     * Get default workspace
     */
    public function getDefaultWorkspace(): ?Workspace
    {
        return $this->workspaces()->where('is_default', true)->first();
    }
}
