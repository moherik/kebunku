<?php

namespace App\Http\Controllers;

use App\Models\Planting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display financial dashboard for the farmer.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get filter params
        $plantingId = $request->input('planting_id');
        $period = $request->input('period', 'all'); // all, month, year
        $type = $request->input('type'); // income, expense, or null for all

        // Build query
        $query = $user->transactions()->with('planting.crop');

        if ($plantingId) {
            $query->where('planting_id', $plantingId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($period === 'month') {
            $query->where('transaction_date', '>=', now()->startOfMonth());
        } elseif ($period === 'year') {
            $query->where('transaction_date', '>=', now()->startOfYear());
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn($t) => [
                ...$t->toArray(),
                'category_label' => $t->category_label,
                'category_icon' => $t->category_icon,
                'formatted_amount' => $t->formatted_amount,
            ]);

        // Summary stats
        $totalExpenses = (float) $user->transactions()->expenses()->sum('amount');
        $totalIncome = (float) $user->transactions()->incomes()->sum('amount');

        // Monthly summary (last 6 months)
        $monthlySummary = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->translatedFormat('M Y');

            $monthExpenses = (float) $user->transactions()
                ->expenses()
                ->forPeriod($monthStart, $monthEnd)
                ->sum('amount');

            $monthIncome = (float) $user->transactions()
                ->incomes()
                ->forPeriod($monthStart, $monthEnd)
                ->sum('amount');

            $monthlySummary[] = [
                'label' => $monthLabel,
                'expenses' => $monthExpenses,
                'income' => $monthIncome,
                'profit' => $monthIncome - $monthExpenses,
            ];
        }

        // Per-planting summary (active plantings)
        $plantingSummary = $user->plantings()
            ->with('crop')
            ->whereIn('status', ['growing', 'pre-order', 'ready'])
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'crop_name' => $p->crop?->full_name,
                'crop_icon' => $p->crop?->icon ?? '🌱',
                'status' => $p->status,
                'total_expenses' => $p->total_expenses,
                'total_income' => $p->total_income,
                'profit_loss' => $p->profit_loss,
            ]);

        // Get user's plantings for filter dropdown
        $plantings = $user->plantings()
            ->with('crop')
            ->orderByDesc('planted_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'label' => ($p->crop?->full_name ?? 'Tanaman') . ' (' . $p->planted_at->format('d M Y') . ')',
            ]);

        return Inertia::render('Farmer/Finances/Index', [
            'transactions' => $transactions,
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'profitLoss' => $totalIncome - $totalExpenses,
            'monthlySummary' => $monthlySummary,
            'plantingSummary' => $plantingSummary,
            'plantings' => $plantings,
            'expenseCategories' => Transaction::EXPENSE_CATEGORIES,
            'incomeCategories' => Transaction::INCOME_CATEGORIES,
            'categoryIcons' => Transaction::CATEGORY_ICONS,
            'filters' => [
                'planting_id' => $plantingId,
                'period' => $period,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'planting_id' => 'nullable|exists:plantings,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string|max:2000',
        ]);

        // Verify planting belongs to user
        if ($validated['planting_id']) {
            $planting = Planting::where('id', $validated['planting_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        $user->transactions()->create($validated);

        return redirect()
            ->back()
            ->with('success', 'Transaksi berhasil dicatat!');
    }

    /**
     * Delete a transaction.
     */
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()
            ->back()
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
