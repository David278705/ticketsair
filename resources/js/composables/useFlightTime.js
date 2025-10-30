/**
 * Composable para formatear tiempos relacionados con vuelos
 * @module useFlightTime
 */

/**
 * Formatea minutos a formato legible (horas y minutos)
 * @param {number} minutes - Duración en minutos
 * @param {object} options - Opciones de formato
 * @param {boolean} options.short - Formato corto (1h 30m) vs largo (1 hora 30 minutos)
 * @param {boolean} options.showZeroMinutes - Mostrar minutos cuando son 0
 * @returns {string} Duración formateada
 *
 * @example
 * formatDuration(90) // "1h 30min"
 * formatDuration(120) // "2h"
 * formatDuration(45) // "45min"
 * formatDuration(90, { short: false }) // "1 hora 30 minutos"
 */
export function formatDuration(minutes, options = {}) {
    const { short = true, showZeroMinutes = false } = options;

    if (!minutes || minutes < 0) return short ? "0min" : "0 minutos";

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    // Si es menos de 1 hora, solo mostrar minutos
    if (hours === 0) {
        return short
            ? `${mins}min`
            : `${mins} ${mins === 1 ? "minuto" : "minutos"}`;
    }

    // Si es exactamente horas completas (sin minutos)
    if (mins === 0 && !showZeroMinutes) {
        return short
            ? `${hours}h`
            : `${hours} ${hours === 1 ? "hora" : "horas"}`;
    }

    // Formato completo con horas y minutos
    if (short) {
        return mins === 0 ? `${hours}h` : `${hours}h ${mins}min`;
    } else {
        const hoursText = `${hours} ${hours === 1 ? "hora" : "horas"}`;
        const minsText =
            mins > 0 ? ` ${mins} ${mins === 1 ? "minuto" : "minutos"}` : "";
        return hoursText + minsText;
    }
}

/**
 * Formatea una fecha y hora en formato legible
 * @param {string|Date} datetime - Fecha y hora
 * @param {object} options - Opciones de formato
 * @param {boolean} options.includeTime - Incluir hora
 * @param {boolean} options.includeSeconds - Incluir segundos
 * @param {string} options.locale - Locale para el formato (default: 'es-ES')
 * @param {string} options.timezone - Zona horaria (ej: 'America/Bogota', 'America/New_York')
 * @returns {string} Fecha formateada
 *
 * @example
 * formatDateTime('2025-10-29T10:30:00') // "29/10/2025 10:30"
 * formatDateTime('2025-10-29T10:30:00', { includeTime: false }) // "29/10/2025"
 * formatDateTime('2025-10-29T10:30:00', { timezone: 'America/New_York' }) // "29/10/2025 05:30"
 */
export function formatDateTime(datetime, options = {}) {
    const {
        includeTime = true,
        includeSeconds = false,
        locale = "es-ES",
        timezone = null,
    } = options;

    if (!datetime) return "";

    const date = new Date(datetime);

    if (isNaN(date.getTime())) return "";

    const dateOptions = {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        timeZone: timezone || undefined,
    };

    if (includeTime) {
        dateOptions.hour = "2-digit";
        dateOptions.minute = "2-digit";
        if (includeSeconds) {
            dateOptions.second = "2-digit";
        }
    }

    return date.toLocaleString(locale, dateOptions);
}

/**
 * Formatea la hora de llegada con información de zona horaria
 * @param {object} arrivalInfo - Información de llegada del backend
 * @param {object} options - Opciones de formato
 * @returns {object} Objeto con información formateada
 *
 * @example
 * formatArrivalWithTimezone(flight.arrival_info)
 * // {
 * //   time: "11:30",
 * //   date: "29/10/2025",
 * //   datetime: "29/10/2025 11:30",
 * //   timezone: "COT",
 * //   isDifferentDay: false
 * // }
 */
export function formatArrivalWithTimezone(arrivalInfo, options = {}) {
    if (!arrivalInfo) return null;

    const date = new Date(arrivalInfo.datetime);

    return {
        time: date.toLocaleTimeString("es-ES", {
            hour: "2-digit",
            minute: "2-digit",
        }),
        date: date.toLocaleDateString("es-ES"),
        datetime: arrivalInfo.formatted,
        timezone: arrivalInfo.timezone_abbr || "",
        isDifferentDay: arrivalInfo.is_different_day || false,
        fullTimezone: arrivalInfo.timezone || "",
    };
}

/**
 * Calcula la hora de llegada basada en salida y duración
 * @param {string|Date} departureTime - Hora de salida
 * @param {number} durationMinutes - Duración en minutos
 * @returns {Date|null} Fecha de llegada calculada
 *
 * @example
 * calculateArrivalTime('2025-10-29T10:00:00', 90) // Date object: 2025-10-29T11:30:00
 */
export function calculateArrivalTime(departureTime, durationMinutes) {
    if (!departureTime || !durationMinutes) return null;

    const departure = new Date(departureTime);
    if (isNaN(departure.getTime())) return null;

    const arrival = new Date(departure.getTime() + durationMinutes * 60000);
    return arrival;
}

/**
 * Formatea la hora de llegada en formato legible
 * @param {string|Date} departureTime - Hora de salida
 * @param {number} durationMinutes - Duración en minutos
 * @param {object} options - Opciones de formato
 * @returns {string} Hora de llegada formateada
 *
 * @example
 * formatArrivalTime('2025-10-29T10:00:00', 90) // "11:30"
 */
export function formatArrivalTime(
    departureTime,
    durationMinutes,
    options = {}
) {
    const arrival = calculateArrivalTime(departureTime, durationMinutes);
    if (!arrival) return "";

    return formatDateTime(arrival, { ...options, includeTime: true });
}

/**
 * Obtiene el formato de duración más apropiado según los minutos
 * @param {number} minutes - Duración en minutos
 * @returns {object} Objeto con horas y minutos
 *
 * @example
 * parseDuration(90) // { hours: 1, minutes: 30, totalMinutes: 90 }
 */
export function parseDuration(minutes) {
    if (!minutes || minutes < 0) {
        return { hours: 0, minutes: 0, totalMinutes: 0 };
    }

    return {
        hours: Math.floor(minutes / 60),
        minutes: minutes % 60,
        totalMinutes: minutes,
    };
}

/**
 * Hook principal del composable
 * @returns {object} Funciones de formato disponibles
 */
export function useFlightTime() {
    return {
        formatDuration,
        formatDateTime,
        calculateArrivalTime,
        formatArrivalTime,
        parseDuration,
        formatArrivalWithTimezone,
    };
}
