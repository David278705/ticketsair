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

use App\Http\Controllers\Admin\FlightAdminController;
use App\Http\Controllers\PromotionController;

Route::post('/register', [AuthController::class,'register']);
Route::post('/login',    [AuthController::class,'login']);
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/me',      [AuthController::class,'me']);
  Route::post('/logout', [AuthController::class,'logout']);
});

Route::get('/flights', [FlightController::class, 'index']);    // búsqueda pública
Route::get('/flights/{flight}', [FlightController::class,'show']);




Route::middleware(['auth:sanctum','role:admin,root'])->group(function () {
  Route::get('/admin/flights', [FlightAdminController::class,'index']);
  Route::post('/admin/flights', [FlightAdminController::class,'store']);
  Route::put('/admin/flights/{flight}', [FlightAdminController::class,'update']);
  Route::post('/admin/flights/{flight}/cancel', [FlightAdminController::class,'cancel']);

  Route::post('/flights/{flight}/promotions', [PromotionController::class,'store']);
  Route::post('/news', [NewsController::class,'store']);
});

Route::middleware(['auth:sanctum','role:client'])->group(function () {
    Route::get('/me/bookings', [BookingController::class,'myBookings']);
    Route::post('/bookings', [BookingController::class,'store']);     // reserva/compra
    Route::post('/bookings/{booking}/cancel', [BookingController::class,'cancel']); // ver reglas tiempo
    Route::post('/seat-change', [SeatController::class,'change']);
});

// Route::middleware(['auth:sanctum','role:client'])->group(function(){
//   Route::get('/cards', [PaymentController::class,'cards']);
//   Route::post('/cards', [PaymentController::class,'storeCard']);
//   Route::delete('/cards/{card}', [PaymentController::class,'destroyCard']);
// });

Route::post('/checkin/fast', [CheckinController::class,'fast']); // por código o DNI

// Noticias y mensajería
Route::get('/news', [NewsController::class,'index']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/news/subscribe', [NewsController::class,'subscribe']);
    Route::post('/messages', [MessageController::class,'send']);
});


Route::get('/cities', fn() => \App\Models\City::orderBy('name')->get());

Route::get('/flights/{flight}/seats', [SeatController::class,'index']); // público

Route::get('/news', [NewsController::class,'index']); // público

