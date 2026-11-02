<?php

namespace App\Models;

use Database\Factories\LoanApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanApplication extends Model
{
    /** @use HasFactory<LoanApplicationFactory> */
    use HasFactory;

    public const MIN_AMOUNT = 1000;

    public const MAX_AMOUNT = 50000;

    public const ANNUAL_RATE = 8;

    public const TERMS = [3, 6, 9, 12, 18, 24, 36];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    protected $fillable = [
        'user_id',
        'loan_type',
        'amount',
        'approved_amount',
        'term_months',
        'purpose',
        'status',
        'admin_remarks',
        'reviewed_by',
        'reviewed_at',
        'interest_rate',
        'total_interest',
        'total_payable',
        'monthly_amortization',
        'outstanding_balance',
        'repayment_status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'total_interest' => 'decimal:2',
            'total_payable' => 'decimal:2',
            'monthly_amortization' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $application): void {
            if (blank($application->application_number)) {
                $application->application_number = 'LA-'.str_pad((string) ((int) static::query()->max('id') + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public static function calculate(float $principal, int $termMonths): array
    {
        $interest = round($principal * self::ANNUAL_RATE / 100 * $termMonths / 12, 2);
        $total = round($principal + $interest, 2);

        return [
            'principal' => round($principal, 2),
            'interest' => $interest,
            'total' => $total,
            'monthly' => round($total / $termMonths, 2),
        ];
    }

    public function principal(): float
    {
        return (float) ($this->approved_amount ?? $this->amount);
    }

    public function breakdown(): array
    {
        return self::calculate($this->principal(), (int) $this->term_months);
    }

    public function applyBreakdown(): void
    {
        $breakdown = $this->breakdown();

        $this->interest_rate = self::ANNUAL_RATE;
        $this->total_interest = $breakdown['interest'];
        $this->total_payable = $breakdown['total'];
        $this->monthly_amortization = $breakdown['monthly'];
    }

    public function loanTypeLabel(): string
    {
        return User::LOAN_TYPES[$this->loan_type]['label'] ?? '—';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }
}
