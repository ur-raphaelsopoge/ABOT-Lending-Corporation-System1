<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const LOAN_TYPES = [
        'personal' => [
            'label' => 'Personal Loan',
            'description' => 'For personal needs and everyday expenses.',
        ],
        'business' => [
            'label' => 'Business Loan',
            'description' => 'To start, run, or grow a business.',
        ],
        'emergency' => [
            'label' => 'Emergency Loan',
            'description' => 'For urgent and unexpected expenses.',
        ],
    ];

    public const ID_TYPES = [
        'philsys' => 'PhilSys National ID',
        'drivers_license' => "Driver's License",
        'passport' => 'Passport',
        'umid' => 'UMID',
        'sss' => 'SSS ID',
        'prc' => 'PRC ID',
        'postal' => 'Postal ID',
    ];

    public const EMPLOYMENT_STATUSES = [
        'employed' => 'Employed',
        'self_employed' => 'Self-employed',
        'business_owner' => 'Business owner',
        'unemployed' => 'Unemployed',
        'retired' => 'Retired',
    ];

    protected $fillable = [
        'name',
        'date_of_birth',
        'phone_number',
        'email',
        'address',
        'id_type',
        'id_number',
        'employment_status',
        'monthly_income',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'loan_type',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'monthly_income' => 'decimal:2',
            'credits' => 'decimal:2',
            'balance_owed' => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (blank($user->borrower_id)) {
                $user->borrower_id = 'BRW-'.str_pad((string) ((int) static::query()->max('id') + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function loanTypeLabel(): string
    {
        return self::LOAN_TYPES[$this->loan_type]['label'] ?? '—';
    }

    public function idTypeLabel(): string
    {
        return self::ID_TYPES[$this->id_type] ?? '—';
    }

    public function employmentLabel(): string
    {
        return self::EMPLOYMENT_STATUSES[$this->employment_status] ?? '—';
    }

    public function maskedIdNumber(): string
    {
        return Str::mask((string) $this->id_number, '•', 0, -4);
    }

    public function hasActiveLoan(): bool
    {
        return (float) $this->balance_owed > 0
            || $this->loanApplications()->where('repayment_status', 'active')->exists();
    }

    public function activeLoan(): ?LoanApplication
    {
        return $this->loanApplications()
            ->where('repayment_status', 'active')
            ->latest('id')
            ->first();
    }

    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function auditEntries(): HasMany
    {
        return $this->hasMany(AuditLedger::class);
    }
}
