<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

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
Route::get('/search/{term}', [PostController::class, 'search']);

// Profile Routes
Route::get('/profile/{user:username}', [UserController::class, 'profile']); // user:username tells the database to look for the user using the username column
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);

Route::middleware('cache.headers:public;max_age=20;etag')->group(function() { // Make all routes in the function use given middleware
    Route::get('/profile/{user:username}/raw', [UserController::class, 'profileRaw']);
    Route::get('/profile/{user:username}/followers/raw', [UserController::class, 'profileFollowersRaw']);
    Route::get('/profile/{user:username}/following/raw', [UserController::class, 'profileFollowingRaw']);
});

// Follow Routes
Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware('authenticated');
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware('authenticated');

// Chat Routes
Route::post('/send-chat-message', function(Request $request) {
    $formFields = $request->validate([
        'textvalue' => 'required'
    ]);

    if (!trim(strip_tags($formFields['textvalue']))) {
        return response()->noContent();
    }

    $username = auth()->user()->username;
    $text = strip_tags($request->textvalue);
    $avatar = auth()->user()->avatar;

    broadcast(new ChatMessage(['username' => $username, 'textvalue' => $text, 'avatar' => $avatar]))->toOthers();
    return response()->noContent();
})->middleware('authenticated');