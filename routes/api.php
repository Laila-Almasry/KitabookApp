<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureIsOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MyReadingController;
use App\Http\Controllers\VisitReservationController;

Route::post('owner/login',[OwnerController::class,'login']);

Route::prefix('owner')->middleware(['auth:sanctum','ensure.owner'])->group(function (){
     Route::post('/visits', [VisitReservationController::class, 'store']);
     Route::get('/visits', [VisitReservationController::class, 'index']);
Route::put('/visits/{id}/status', [VisitReservationController::class, 'updateStatus']);


});


//books routs
Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::post('/', [BookController::class, 'store']);
    Route::get('/latest', [BookController::class, 'latestReleases']);
    Route::get('/bestsellers', [BookController::class, 'bestSellers']);
    Route::get('/search', [BookController::class, 'search']); //Search route
    Route::get('/{id}', [BookController::class, 'show']);
    Route::put('/{id}', [BookController::class, 'update']);
    Route::delete('/{id}', [BookController::class, 'destroy']);
});
//authors routes
Route::prefix('authors')->group(function () {
    Route::get('/', [AuthorController::class, 'index']);        // GET /api/authors
    Route::post('/', [AuthorController::class, 'store']);       // POST /api/authors
    Route::get('/{id}', [AuthorController::class, 'show']);     // GET /api/authors/{id}
    Route::put('/{id}', [AuthorController::class, 'update']);   // PUT /api/authors/{id}
    Route::delete('/{id}', [AuthorController::class, 'destroy']);// DELETE /api/authors/{id}
});
//categories routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);        // GET /api/categories
    Route::post('/', [CategoryController::class, 'store']);       // POST /api/categories
    Route::get('/{id}', [CategoryController::class, 'show']);     // GET /api/categories/{id}
    Route::put('/{id}', [CategoryController::class, 'update']);   // PUT /api/categories/{id}
    Route::delete('/{id}', [CategoryController::class, 'destroy']);// DELETE /api/categories/{id}
});
//user authentication routes
Route::controller(UserController::class)->group(function(){
    Route::post('/login/google', [UserController::class, 'handleGoogleLogin']);
    Route::post('/register', [UserController::class, 'handleTelegramRegister']);
    Route::post('/login', [UserController::class, 'handleTelegramLogin']);
    Route::post('/verifyOtp', [UserController::class, 'verifyOtp']);
    Route::post('/checkRegistration', [UserController::class, 'checkRegistration']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
});
Route::prefix('products')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);        // GET /api/product
    Route::post('/', [CategoryController::class, 'store']);       // POST /api/product
    Route::get('/{id}', [CategoryController::class, 'show']);     // GET /api/product/{id}
    Route::put('/{id}', [CategoryController::class, 'update']);   // PUT /api/product/{id}
    Route::delete('/{id}', [CategoryController::class, 'destroy']);// DELETE /api/product/{id}
});

// orders routes
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'placeOrder']);


// user booking visits
Route::middleware('auth:sanctum')->prefix('visits')->group(function () {
    Route::post('/checkAvailableTimes', [VisitReservationController::class, 'checkAvailableTimes']);
    Route::post('/', [VisitReservationController::class, 'store']);
    Route::get('/myReservations', [VisitReservationController::class, 'myReservations']);
    Route::delete('/{id}', [VisitReservationController::class, 'cancel']);
});



// rating apis
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books/{book}/rate', [BookRatingController::class, 'storeOrUpdate']);
    Route::delete('/books/{book}/rate', [BookRatingController::class, 'destroy']);
});




//user favorites
Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);              // List user's favorites
    Route::post('/', [FavoriteController::class, 'store']);             // Add a book to favorites
    Route::delete('/{bookId}', [FavoriteController::class, 'destroy']); // Remove a book from favorites
});


//user my reading
Route::middleware('auth:sanctum')->prefix('myreading')->group(function () {
    Route::get('/', [MyReadingController::class, 'index']);
    Route::post('/', [MyReadingController::class, 'store']);
    Route::get('/{id}', [MyReadingController::class, 'show']);
    Route::put('/{id}', [MyReadingController::class, 'update']);
    Route::delete('/{id}', [MyReadingController::class, 'destroy']);
});

