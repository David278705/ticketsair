import Swal from "sweetalert2";

export function useSweetAlert() {
    // Alerta de éxito
    const success = (title, text = "") => {
        return Swal.fire({
            icon: "success",
            title,
            text,
            confirmButtonColor: "#3b82f6",
            confirmButtonText: "Entendido",
            timer: 3000,
            timerProgressBar: true,
        });
    };

    // Alerta de error
    const error = (title, text = "") => {
        return Swal.fire({
            icon: "error",
            title,
            text,
            confirmButtonColor: "#ef4444",
            confirmButtonText: "Entendido",
        });
    };

    // Alerta de advertencia
    const warning = (title, text = "") => {
        return Swal.fire({
            icon: "warning",
            title,
            text,
            confirmButtonColor: "#f59e0b",
            confirmButtonText: "Entendido",
        });
    };

    // Alerta de información
    const info = (title, text = "") => {
        return Swal.fire({
            icon: "info",
            title,
            text,
            confirmButtonColor: "#3b82f6",
            confirmButtonText: "Entendido",
        });
    };

    // Confirmación (reemplazo de confirm())
    const confirm = async (
        title,
        text = "",
        confirmText = "Sí, continuar",
        cancelText = "Cancelar"
    ) => {
        const result = await Swal.fire({
            icon: "question",
            title,
            text,
            showCancelButton: true,
            confirmButtonColor: "#3b82f6",
            cancelButtonColor: "#6b7280",
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
        });
        return result.isConfirmed;
    };

    // Toast (notificación pequeña)
    const toast = (title, icon = "success") => {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener("mouseenter", Swal.stopTimer);
                toast.addEventListener("mouseleave", Swal.resumeTimer);
            },
        });

        return Toast.fire({
            icon,
            title,
        });
    };

    // Loading
    const loading = (title = "Procesando...", text = "Por favor espera") => {
        Swal.fire({
            title,
            text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    };

    // Cerrar loading
    const close = () => {
        Swal.close();
    };

    return {
        success,
        error,
        warning,
        info,
        confirm,
        toast,
        loading,
        close,
    };
}
