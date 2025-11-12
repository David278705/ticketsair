<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletRechargeRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Obtener el saldo y las transacciones recientes del usuario
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $transactions = $user->walletTransactions()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'balance' => $user->wallet_balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Recargar saldo del wallet
     */
    public function recharge(WalletRechargeRequest $request)
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            // Obtener la tarjeta si se proporcionó
            $card = null;
            if ($request->card_id) {
                $card = $user->cards()->find($request->card_id);
                if (!$card) {
                    return response()->json([
                        'error' => 'La tarjeta seleccionada no te pertenece'
                    ], 403);
                }
            }

            // Crear la transacción de recarga
            $transaction = WalletTransaction::createTransaction(
                $user->id,
                'recharge',
                $request->amount,
                $request->description ?? 'Recarga de saldo',
                null,
                [
                    'card_id' => $request->card_id,
                    'card_last4' => $card ? $card->last4 : null,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'COP' // Siempre en COP
            );

            DB::commit();

            return response()->json([
                'message' => 'Recarga realizada exitosamente',
                'transaction' => $transaction,
                'new_balance' => $transaction->balance_after,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error en recarga de wallet: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar la recarga',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas del wallet
     */
    public function statistics()
    {
        $user = Auth::user();

        $totalIncome = $user->walletTransactions()
            ->whereIn('type', ['recharge', 'refund', 'bonus'])
            ->sum('amount');

        $totalExpenses = $user->walletTransactions()
            ->whereIn('type', ['payment', 'purchase', 'adjustment'])
            ->sum('amount');

        $totalTransactions = $user->walletTransactions()->count();

        return response()->json([
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'totalTransactions' => $totalTransactions,
        ]);
    }

    /**
     * Obtener historial completo de transacciones filtradas
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type'); // recharge, purchase, refund, adjustment

        $query = $user->walletTransactions()->orderByDesc('created_at');

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }
}
