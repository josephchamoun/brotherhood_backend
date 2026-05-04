<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoneyTransaction;
use App\Models\Moneybox;
use Illuminate\Support\Facades\DB;


class MoneyTransactionController extends Controller
{



    private function canManageTransactions(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->is_global_admin) return true;

        // Amin Sandou2 in ANY section
        return $user->sectionRoles()
            ->whereNull('end_date')
            ->whereHas('role', fn($q) => $q->where('name', 'Amin Sandou2'))
            ->exists();
    }
    public function index()
    {
        return MoneyTransaction::with(['moneybox', 'event', 'user'])
            ->latest()
            ->get();
    }
    public function store(Request $request)
    {


        if (!$this->canManageTransactions()) {
        abort(403, 'Not authorized to create transactions');
    }
        $validated = $request->validate([
            'moneybox_id' => 'required|exists:moneyboxes,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $amount = $validated['type'] === 'expense'
            ? -abs($validated['amount'])
            : abs($validated['amount']);

        DB::transaction(function () use ($validated, $amount) {

            $transaction = MoneyTransaction::create([
                ...$validated,
                'amount' => $amount,
                'source' => 'manual',
                'user_id' => auth()->id(),
            ]);

            $moneybox = Moneybox::findOrFail($validated['moneybox_id']);

            $moneybox->increment('amount', $amount);
        });

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
{

    if (!$this->canManageTransactions()) {
        abort(403, 'Not authorized to create transactions');
    }
    $transaction = MoneyTransaction::findOrFail($id);

    $oldAmount = $transaction->amount;

    $validated = $request->validate([
        'amount' => 'required|numeric',
        'type' => 'required|in:income,expense',
        'description' => 'nullable|string',
    ]);

    $newAmount = $validated['type'] === 'expense'
        ? -abs($validated['amount'])
        : abs($validated['amount']);

    DB::transaction(function () use ($transaction, $validated, $oldAmount, $newAmount) {

        $moneybox = Moneybox::findOrFail($transaction->moneybox_id);

        // 1. reverse old effect
        $moneybox->increment('amount', -$oldAmount);

        // 2. apply new effect
        $moneybox->increment('amount', $newAmount);

        // 3. update transaction
        $transaction->update([
            ...$validated,
            'amount' => $newAmount,
        ]);
    });

    return response()->json(['success' => true]);
}


public function destroy($id)
{
    if (!$this->canManageTransactions()) {
        abort(403, 'Not authorized to create transactions');
    }

    $transaction = MoneyTransaction::findOrFail($id);

    DB::transaction(function () use ($transaction) {

        $moneybox = Moneybox::findOrFail($transaction->moneybox_id);

        // reverse effect
        $moneybox->decrement('amount', $transaction->amount);

        $transaction->delete();
    });

    return response()->json(['success' => true]);
}
}
