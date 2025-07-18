<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookCopyController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MyReadingController;
use App\Http\Controllers\VisitReservationController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\BorrowReservationController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DigitalProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
Route::post('owner/login',[OwnerController::class,'login']);

Route::prefix('owner')->middleware(['auth:sanctum','ensure.owner'])->group(function (){
     Route::post('/visits', [VisitReservationController::class, 'store']);
     Route::get('/visits', [VisitReservationController::class, 'index']);
     Route::get('/visits/{id}', [VisitReservationController::class, 'show']);
Route::post('/visits/check', [VisitReservationController::class, 'check']);
Route::put('/visits/{id}/status', [VisitReservationController::class, 'updateStatus']);
Route::get('/borrowReservations',[BorrowReservationController::class,'index']);
Route::get('/borrowReservations/{id}',[BorrowReservationController::class,'show']);
Route::post('/borrowReservations/{id}', [BorrowController::class, 'reservationToBorrowing']);
Route::post('/walkInBorrowing', [BorrowController::class, 'walkInBorrowing']);
Route::get('/borrowings', [BorrowController::class, 'index']);
Route::post('/borrowings/return', [BorrowController::class, 'returnBorrowing']);
Route::post('/walkInPurchases',[PurchaseController::class,'walkInPurchase']);
Route::get('/purchases',[PurchaseController::class,'index']);


    Route::post('/categories', [CategoryController::class, 'store']);       // POST /api/categories
    Route::put('/categories/{id}', [CategoryController::class, 'update']);   // PUT /api/categories/{id}
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);// DELETE /api/categories/{id}
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    Route::post('/authors', [AuthorController::class, 'store']);       // POST /api/authors
    Route::put('/authors/{id}', [AuthorController::class, 'update']);   // PUT /api/authors/{id}
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);// DELETE /api/authors/{id}

    Route::prefix('wallets')->group(function () {
    Route::get('search', [WalletController::class, 'search']);
    Route::get('{wallet}', [WalletController::class, 'show']);
    Route::post('{wallet}/charge', [WalletController::class, 'charge']);
    Route::post('{wallet}/freeze', [WalletController::class, 'freeze']);
    Route::post('{wallet}/unfreeze', [WalletController::class, 'unfreeze']);
    Route::post('{wallet}/buy', [WalletController::class, 'buyWithCredits']);

});

Route::post('/checkPassword',[OwnerController::class,'checkPassword']);

Route::post('/getBookByCopyBarcode',[BookCopyController::class,'getBookByCopyBarcode']);
});


//books routs
Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index']);
    Route::get('/latest', [BookController::class, 'latestReleases']);
    Route::get('/bestsellers', [BookController::class, 'bestSellers']);
    Route::get('/search', [BookController::class, 'search']); //Search route
    Route::get('/{id}', [BookController::class, 'show']);
});
//authors routes
Route::prefix('authors')->group(function () {
    Route::get('/', [AuthorController::class, 'index']);        // GET /api/authors
    Route::get('/{id}', [AuthorController::class, 'show']);     // GET /api/authors/{id}

});
//categories routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);        // GET /api/categories
    Route::get('/{id}', [CategoryController::class, 'show']);     // GET /api/categories/{id}

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

// visits routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/visits', [VisitReservationController::class, 'store']);
    Route::delete('/visits/{id}', [VisitReservationController::class, 'cancel']);
         Route::get('/visits/{id}', [VisitReservationController::class, 'show']);
    Route::post('/visits/checkAvailableTimes', [VisitReservationController::class, 'checkAvailableTimes']);
    Route::get('/visits/myReservations',[VisitReservationController::class,'myReservations']);
});

//user wallet
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/wallet',[WalletController::class,'showUserWallet']);
});
//digital products
Route::middleware(['auth:sanctum'])->prefix('digitalProducts')->group(function () {
    Route::get('/', [DigitalProductsController::class, 'index']);        // GET /api/product
    Route::post('/', [DigitalProductsController::class, 'store']);       // POST /api/product
    Route::get('/{id}', [DigitalProductsController::class, 'show']);     // GET /api/product/{id}
    Route::put('/{id}', [DigitalProductsController::class, 'update']);   // PUT /api/product/{id}
    Route::delete('/{id}', [DigitalProductsController::class, 'destroy']);// DELETE /api/product/{id}
});
//user profile
Route::middleware(['auth:sanctum'])->prefix('profile')->group(function () {
    Route::get('/{id}', [ProfileController::class, 'show']);     // GET /api/Profile /{id}
    Route::put('/{id}', [ProfileController::class, 'update']);   // PUT /api/Profile/{id}
    Route::delete('/{id}', [ProfileController::class, 'destroy']);// DELETE /api/Profile/{id}
});

Route::middleware(['auth:sanctum'])->post('/contact', [ContactController::class, 'send']);
