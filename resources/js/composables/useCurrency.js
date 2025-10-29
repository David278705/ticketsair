import { ref, computed } from 'vue';

// Tasas de cambio (COP como base)
const EXCHANGE_RATES = {
    COP: 1,
    USD: 0.00024, // 1 COP = 0.00024 USD (aproximadamente 4,200 COP por USD)
    EUR: 0.00022  // 1 COP = 0.00022 EUR (aproximadamente 4,500 COP por EUR)
};

// Símbolos de moneda
const CURRENCY_SYMBOLS = {
    COP: '$',
    USD: 'US$',
    EUR: '€'
};

// Estado global de la moneda (persiste en localStorage)
const currentCurrency = ref(localStorage.getItem('selectedCurrency') || 'COP');

export function useCurrency() {
    // Cambiar moneda
    const setCurrency = (currency) => {
        if (EXCHANGE_RATES[currency]) {
            currentCurrency.value = currency;
            localStorage.setItem('selectedCurrency', currency);
        }
    };

    // Convertir precio de COP a la moneda seleccionada
    const convertPrice = (copPrice) => {
        const rate = EXCHANGE_RATES[currentCurrency.value];
        return copPrice * rate;
    };

    // Formatear precio con símbolo de moneda
    const formatPrice = (copPrice) => {
        const convertedPrice = convertPrice(copPrice);
        const symbol = CURRENCY_SYMBOLS[currentCurrency.value];
        
        // Formatear según la moneda
        if (currentCurrency.value === 'COP') {
            // COP: sin decimales, separador de miles con punto
            return `${symbol} ${Math.round(convertedPrice).toLocaleString('es-CO')}`;
        } else {
            // USD/EUR: 2 decimales, separador de miles con coma
            return `${symbol} ${convertedPrice.toLocaleString('en-US', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            })}`;
        }
    };

    // Obtener precio convertido sin formato (útil para cálculos)
    const getConvertedPrice = (copPrice) => {
        return convertPrice(copPrice);
    };

    // Obtener todas las monedas disponibles
    const availableCurrencies = computed(() => {
        return Object.keys(EXCHANGE_RATES).map(code => ({
            code,
            symbol: CURRENCY_SYMBOLS[code],
            name: code === 'COP' ? 'Peso Colombiano' : 
                  code === 'USD' ? 'Dólar Estadounidense' : 
                  'Euro'
        }));
    });

    return {
        currentCurrency,
        setCurrency,
        convertPrice,
        formatPrice,
        getConvertedPrice,
        availableCurrencies
    };
}
