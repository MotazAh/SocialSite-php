<?php

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admins-only', function() {
    return 'Only for admins';
})->middleware('can:visitAdminPages');

// User Routes
Route::get('/', [UserController::class, 'showCorrectHomePage'])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware('authenticated');
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware('authenticated');
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware('authenticated');

// Blog Routes
Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware('authenticated');
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware('authenticated');
Route::get('/post/{post}', [PostController::class, 'viewPost']);
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'updatePost'])->middleware('can:update,post');

// Profile Routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']); // user:username tells the database to look for the user using the username column