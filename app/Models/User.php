<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'onboarding_completed',
        'last_login_at',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'lifetime_access',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'last_login_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'lifetime_access' => 'boolean',
        ];
    }

    /**
     * Full name from first_name + last_name (for Cashier and any code that reads $user->name).
     */
    public function getNameAttribute(): string
    {
        return trim(trim($this->attributes['first_name'] ?? '') . ' ' . trim($this->attributes['last_name'] ?? ''));
    }

    public function financialProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FinancialProfile::class);
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function debts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function investmentAccounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }

    public function freedomCalculations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FreedomCalculation::class);
    }

    public function aiRecommendations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AIRecommendation::class);
    }

    public function progressSnapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressSnapshot::class);
    }
}
