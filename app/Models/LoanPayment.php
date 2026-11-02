<?php

namespace App\Models;

use Database\Factories\LoanPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    /** @use HasFactory<LoanPaymentFactory> */
    use HasFactory;

    public const METHODS = [
        'cash' => 'Cash',
        'gcash' => 'GCash',
        'bank_transfer' => 'Bank transfer',
        'other' => 'Other',
    ];

    public const STATUSES = ['pending', 'confirmed', 'rejected'];

    protected $fillable = [
        'loan_application_id',
        'user_id',
        'reference_number',
        'amount',
        'payment_method',
        'status',
        'notes',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            if (blank($payment->payment_number)) {
                $payment->payment_number = 'PAY-'.str_pad((string) ((int) static::query()->max('id') + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->payment_method] ?? '—';
    }

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }
}
