<?php
use Illuminate\Http\Request;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MyReadingController;
use App\Http\Controllers\OrderController;


Route::post('owner/login',[OwnerController::class,'login']);



Route::post('/addNewBook',[BookController::class,'store']);


// rating apis
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books/{book}/rate', [BookRatingController::class, 'storeOrUpdate']);
    Route::delete('/books/{book}/rate', [BookRatingController::class, 'destroy']);
});


// owner manipulation with books
Route::prefix('books')->group(function(){
Route::get('/', [BookController::class, 'index']);
Route::post('/', [BookController::class, 'store']);
Route::get('/{id}', [BookController::class, 'show']);
Route::get('/latest-releases', [BookController::class, 'latestReleases']);
Route::get('/best-sellers', [BookController::class, 'bestSellers']);
});


Route::prefix('categories')->group(function(){
    Route::get('/',[CategoryController::class,'index']);
});

Route::prefix('authors')->group(function(){
    Route::get('/',[AuthorController::class,'index']);
    Route::get('/{id}',[AuthorController::class,'show']);
    Route::post('/',[AuthorController::class,'store']);
});

//user authentication routes
Route::controller(UserController::class)->group(function(){
    Route::post('/login/google', [UserController::class, 'handleGoogleLogin']);
    Route::post('/register', [UserController::class, 'handleTelegramLogin']);
    Route::post('/checkRegistration', [UserController::class, 'checkRegistration']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
});

//favorite
Route::prefix('favorite')->group(function(){
    Route::get('/', [BookController::class, 'index']);
    Route::post('/', [BookController::class, 'store']);
    Route::get('/{id}', [BookController::class, 'show']);
    Route::delate('/{id}', [BookController::class, 'destroy']);
    });
    //myreading
    Route::prefix('myreading')->group(function(){
        Route::get('/', [BookController::class, 'index']);
        Route::post('/', [BookController::class, 'store']);
        Route::get('/{id}', [BookController::class, 'show']);
        Route::get('/{id}', [BookController::class, 'update']);
        Route::delate('/{id}', [BookController::class, 'destroy']);
        }); 
// orders routes
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'placeOrder']);
