<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardStoreRequest;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    /**
     * Listar todas las tarjetas del usuario autenticado
     */
    public function index()
    {
        $user = Auth::user();
        
        $cards = $user->cards()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'cards' => $cards,
            'has_default' => $cards->where('is_default', true)->isNotEmpty(),
        ]);
    }

    /**
     * Almacenar una nueva tarjeta
     */
    public function store(CardStoreRequest $request)
    {
        $user = Auth::user();

        // Limpiar el número de tarjeta (remover espacios)
        $cardNumber = preg_replace('/\s+/', '', $request->card_number);

        // Detectar la marca de la tarjeta automáticamente si no se proporciona
        $brand = $request->brand ?? $this->detectCardBrand($cardNumber);

        // Extraer los últimos 4 dígitos
        $last4 = substr($cardNumber, -4);

        // Crear la tarjeta
        $card = $user->cards()->create([
            'brand' => $brand,
            'card_type' => $request->card_type ?? 'credit',
            'holder_name' => $request->card_holder_name,
            'last4' => $last4,
            'exp_month' => $request->expiry_month,
            'exp_year' => $request->expiry_year,
            'token' => hash('sha256', $cardNumber . $request->cvv . time()), // Simular token único
            'is_default' => $request->boolean('is_default', false),
        ]);

        // Si es la primera tarjeta o se marcó como predeterminada
        if ($request->boolean('is_default', false) || $user->cards()->count() === 1) {
            $card->makeDefault();
        }

        return response()->json([
            'message' => 'Tarjeta agregada exitosamente',
            'card' => $card->fresh(),
        ], 201);
    }

    /**
     * Mostrar una tarjeta específica
     */
    public function show(Card $card)
    {
        // Verificar que la tarjeta pertenezca al usuario autenticado
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json(['card' => $card]);
    }

    /**
     * Marcar una tarjeta como predeterminada
     */
    public function setDefault(Card $card)
    {
        // Verificar que la tarjeta pertenezca al usuario autenticado
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Verificar que la tarjeta no esté expirada
        if ($card->is_expired) {
            return response()->json([
                'error' => 'No se puede establecer una tarjeta expirada como predeterminada'
            ], 422);
        }

        $card->makeDefault();

        return response()->json([
            'message' => 'Tarjeta establecida como predeterminada',
            'card' => $card->fresh(),
        ]);
    }

    /**
     * Eliminar una tarjeta
     */
    public function destroy(Card $card)
    {
        // Verificar que la tarjeta pertenezca al usuario autenticado
        if ($card->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // No permitir eliminar si es la última tarjeta y hay pagos pendientes
        $user = Auth::user();
        if ($user->cards()->count() === 1) {
            // Verificar si hay pagos pendientes o bookings sin pagar
            $hasPendingPayments = $user->bookings()
                ->whereHas('payments', function($q) {
                    $q->where('status', 'pending');
                })
                ->exists();

            if ($hasPendingPayments) {
                return response()->json([
                    'error' => 'No puedes eliminar tu única tarjeta mientras tengas pagos pendientes'
                ], 422);
            }
        }

        // Si era la tarjeta predeterminada, asignar otra como predeterminada
        $wasDefault = $card->is_default;
        $card->delete();

        if ($wasDefault && $user->cards()->count() > 0) {
            $user->cards()->first()->makeDefault();
        }

        return response()->json([
            'message' => 'Tarjeta eliminada exitosamente'
        ]);
    }

    /**
     * Detectar la marca de la tarjeta basándose en el número
     *
     * @param string $cardNumber
     * @return string
     */
    private function detectCardBrand($cardNumber)
    {
        $firstDigit = substr($cardNumber, 0, 1);
        $firstTwoDigits = substr($cardNumber, 0, 2);
        $firstFourDigits = substr($cardNumber, 0, 4);

        // Visa: comienza con 4
        if ($firstDigit === '4') {
            return 'visa';
        }

        // Mastercard: comienza con 51-55 o 2221-2720
        if (in_array($firstTwoDigits, ['51', '52', '53', '54', '55']) ||
            ($firstFourDigits >= '2221' && $firstFourDigits <= '2720')) {
            return 'mastercard';
        }

        // American Express: comienza con 34 o 37
        if (in_array($firstTwoDigits, ['34', '37'])) {
            return 'amex';
        }

        // Discover: comienza con 6011, 622126-622925, 644-649, o 65
        if ($firstFourDigits === '6011' ||
            ($firstFourDigits >= '6221' && $firstFourDigits <= '6229') ||
            ($firstTwoDigits >= '64' && $firstTwoDigits <= '65')) {
            return 'discover';
        }

        return 'unknown';
    }
}
