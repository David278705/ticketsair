<?php

use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminRegistrationController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\FlightAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\PromotionController;

// =================================
// RUTAS PÚBLICAS (Sin autenticación)
// =================================
Route::post('/register', [AuthController::class,'register']);
Route::post('/login',    [AuthController::class,'login']);
Route::post('/forgot-password', [AuthController::class,'forgotPassword']);
Route::post('/reset-password', [AuthController::class,'resetPassword']);
Route::post('/check-reset-token', [AuthController::class,'checkResetToken']);

Route::get('/flights', [FlightController::class, 'index']);    // búsqueda pública
Route::get('/flights/{flight}', [FlightController::class,'show']);
Route::get('/flights/{flight}/seats', [SeatController::class,'index']); // público
Route::get('/cities', fn() => \App\Models\City::orderBy('name')->get());
Route::get('/news', [NewsController::class,'index']); // público
Route::post('/checkin/fast', [CheckinController::class,'fast']); // por código o DNI

// Rutas para completar registro de administrador
Route::post('/verify-admin-token', [AdminRegistrationController::class, 'verifyToken']);
Route::post('/complete-admin-registration', [AdminRegistrationController::class, 'completeRegistration']);

// =================================
// RUTAS AUTENTICADAS (Cualquier usuario logueado)
// =================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class,'me']);
    Route::post('/logout', [AuthController::class,'logout']);
    
    // Perfil de usuario
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    
    // Mensajería básica
    Route::get('/messages', [MessageController::class,'index']);
    Route::get('/messages/{message}', [MessageController::class,'show']);
    Route::post('/messages', [MessageController::class,'store']);
    Route::delete('/messages/{message}', [MessageController::class,'destroy']);
    Route::patch('/messages/{message}/read', [MessageController::class,'markAsRead']);
    Route::get('/messages/conversation/{user}', [MessageController::class,'getConversation']);
    Route::get('/messages/unread/count', [MessageController::class,'getUnreadCount']);
    
    // Suscripción a noticias
    Route::post('/news/subscribe', [NewsController::class,'subscribe']);
});

// =================================
// RUTAS PARA ROOT (Solo usuario root)
// =================================
Route::middleware(['auth:sanctum','role:root'])->group(function () {
    // Gestión de usuarios (solo root) - FUNCIONALIDADES RESTRINGIDAS
    Route::get('/admin/users', [UserAdminController::class,'index']);
    Route::get('/admin/users/{user}', [UserAdminController::class,'show']);
    Route::post('/admin/users/create-admin', [UserAdminController::class,'createAdmin']);
    Route::get('/admin/roles', [UserAdminController::class,'getRoles']);
    
    // Funcionalidad para que root cambie su propia contraseña
    Route::post('/admin/change-own-password', [UserAdminController::class,'updateOwnPassword']);
    
    // RUTAS RESTRINGIDAS - El root YA NO puede usar estas funcionalidades:
    // Route::put('/admin/users/{user}/credentials', [UserAdminController::class,'updateCredentials']); // DESHABILITADA
    // Route::post('/admin/users/{user}/reset-password', [UserAdminController::class,'resetPassword']); // DESHABILITADA
    
    // Funcionalidad de activar/desactivar usuarios permanece igual
    Route::patch('/admin/users/{user}/toggle-status', [UserAdminController::class,'toggleStatus']);
});

// =================================
// RUTAS PARA ADMIN (Solo administradores, NO root)
// =================================
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
    // Completar registro para admins autenticados
    Route::post('/admin/complete-registration', [AdminRegistrationController::class, 'completeAuthenticatedRegistration']);
    
    // Gestión de vuelos (solo admin)
    Route::get('/admin/flights', [FlightAdminController::class,'index']);
    Route::post('/admin/flights', [FlightAdminController::class,'store']);
    Route::put('/admin/flights/{flight}', [FlightAdminController::class,'update']);
    Route::post('/admin/flights/{flight}/cancel', [FlightAdminController::class,'cancel']);
    
    // Promociones y noticias (solo admin)
    Route::post('/flights/{flight}/promotions', [PromotionController::class,'store']);
    Route::post('/news', [NewsController::class,'store']);
    
    // Mensajería administrativa (solo admin)
    Route::get('/admin/messages', [MessageController::class,'adminMessages']);
    Route::post('/messages/{message}/reply', [MessageController::class,'reply']);
});

// =================================
// RUTAS PARA CLIENTES (Solo clientes)
// =================================
Route::middleware(['auth:sanctum','role:client'])->group(function () {
    // Gestión de reservas
    Route::get('/me/bookings', [BookingController::class,'myBookings']);
    Route::post('/bookings', [BookingController::class,'store']);     // reserva/compra
    Route::post('/bookings/{booking}/cancel', [BookingController::class,'cancel']); // ver reglas tiempo
    Route::post('/seat-change', [SeatController::class,'change']);
    
    // Mensaje a administradores
    Route::post('/messages/to-admin', [MessageController::class,'sendToAdmin']);
});

// =================================
// RUTAS PARA ADMIN O ROOT (Ambos roles)
// =================================
Route::middleware(['auth:sanctum','role:admin,root'])->group(function () {
    // Funcionalidades compartidas entre admin y root si las hay
    // (Por ahora ninguna específica)
});

// =================================
// RUTAS COMENTADAS (Para futura implementación)
// =================================
// Route::middleware(['auth:sanctum','role:client'])->group(function(){
//   Route::get('/cards', [PaymentController::class,'cards']);
//   Route::post('/cards', [PaymentController::class,'storeCard']);
//   Route::delete('/cards/{card}', [PaymentController::class,'destroyCard']);
// });

