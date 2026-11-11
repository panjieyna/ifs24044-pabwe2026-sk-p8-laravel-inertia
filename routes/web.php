<?php

use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:web')->group(function () {
    // =====================================================================
    // 1. UMUM (PUBLIK) — tanpa login
    // =====================================================================
    Route::get('/', [PublicPostController::class, 'index'])->name('home');
    Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
    Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

    Route::middleware('guest')->controller(AuthController::class)->group(function () {
        Route::get('/login', 'createLogin')->name('login');
        Route::post('/login', 'storeLogin')->name('login.store');
        Route::get('/register', 'createRegister')->name('register');
        Route::post('/register', 'storeRegister')->name('register.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        // =================================================================
        // 2. ANGGOTA (PENULIS) — dashboard artikel
        // =================================================================
        Route::prefix('dashboard/posts')->name('dashboard.posts.')->controller(PostController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');

            // Rute statis diletakkan sebelum rute wildcard {post}.
            Route::get('/export', 'exportExcel')->name('export');
            Route::post('/import', 'importExcel')->name('import');
            Route::post('/ai-generate', 'generateAi')->name('ai.generate');
            Route::post('/ai-save', 'saveAi')->name('ai.save');

            Route::middleware('owns.post')->where(['post' => '[0-9]+'])->group(function () {
                Route::get('/{post}', 'show')->name('show');
                Route::put('/{post}', 'update')->name('update');
                Route::delete('/{post}', 'destroy')->name('destroy');
                Route::patch('/{post}/publish', 'togglePublish')->name('publish');
                Route::post('/{post}/cover', 'updateCover')->name('cover.update');
                Route::delete('/{post}/cover', 'deleteCover')->name('cover.delete');
            });
        });

        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'edit')->name('profile.edit');
            Route::patch('/profile', 'update')->name('profile.update');
            Route::put('/profile/password', 'updatePassword')->name('profile.password');
            Route::post('/profile/photo', 'updatePhoto')->name('profile.photo.update');
            Route::delete('/profile/photo', 'deletePhoto')->name('profile.photo.delete');
        });

        // =================================================================
        // 3. ADMIN — kelola pengguna & monitoring artikel (read-only)
        // =================================================================
        Route::prefix('admin')->name('admin.')->middleware('role:admin')->where(['user' => '[0-9]+'])->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users');
            Route::get('/users/{user}/posts', [AdminPostController::class, 'userPosts'])->name('users.posts');
            Route::get('/users/{user}/posts/export', [AdminPostController::class, 'exportUserPosts'])->name('users.posts.export');
            Route::get('/posts', [AdminPostController::class, 'index'])->name('posts');
            Route::get('/posts/export', [AdminPostController::class, 'export'])->name('posts.export');
        });
    });
});
