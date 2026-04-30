<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_id',
        'user_id',
        'type',
        'category',
        'amount',
        'transaction_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Expense category labels in Bahasa Indonesia.
     */
    public const EXPENSE_CATEGORIES = [
        'bibit'         => 'Bibit / Benih',
        'pupuk'         => 'Pupuk',
        'pestisida'     => 'Pestisida',
        'tenaga_kerja'  => 'Tenaga Kerja',
        'sewa_alat'     => 'Sewa Alat',
        'sewa_lahan'    => 'Sewa Lahan',
        'transportasi'  => 'Transportasi',
        'kemasan'       => 'Kemasan',
        'irigasi'       => 'Irigasi / Air',
        'lainnya'       => 'Lainnya',
    ];

    /**
     * Income category labels in Bahasa Indonesia.
     */
    public const INCOME_CATEGORIES = [
        'penjualan'     => 'Penjualan Hasil Panen',
        'pre_order'     => 'Pre-Order',
        'subsidi'       => 'Subsidi / Bantuan',
        'lainnya'       => 'Lainnya',
    ];

    /**
     * Category icons.
     */
    public const CATEGORY_ICONS = [
        'bibit'         => '🌱',
        'pupuk'         => '🧪',
        'pestisida'     => '🧴',
        'tenaga_kerja'  => '👷',
        'sewa_alat'     => '🔧',
        'sewa_lahan'    => '🏞️',
        'transportasi'  => '🚚',
        'kemasan'       => '📦',
        'irigasi'       => '💧',
        'penjualan'     => '💰',
        'pre_order'     => '🛒',
        'subsidi'       => '🏛️',
        'lainnya'       => '📝',
    ];

    /**
     * Get the planting this transaction belongs to.
     */
    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * Get the user who owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Indonesian label for this category.
     */
    public function getCategoryLabelAttribute(): string
    {
        $all = array_merge(self::EXPENSE_CATEGORIES, self::INCOME_CATEGORIES);
        return $all[$this->category] ?? $this->category;
    }

    /**
     * Get the icon for this category.
     */
    public function getCategoryIconAttribute(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? '📝';
    }

    /**
     * Get formatted amount in Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Scope: only expenses.
     */
    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope: only incomes.
     */
    public function scopeIncomes($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope: transactions within a date range.
     */
    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    /**
     * Scope: transactions for a specific planting.
     */
    public function scopeForPlanting($query, int $plantingId)
    {
        return $query->where('planting_id', $plantingId);
    }
}
