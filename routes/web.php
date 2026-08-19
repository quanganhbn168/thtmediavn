<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Frontend;
use Illuminate\Support\Facades\Route;

Route::get('/', [Frontend\HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [Frontend\SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [Frontend\SeoController::class, 'robots'])->name('robots');
Route::get('/gioi-thieu', [Frontend\AboutController::class, 'index'])->name('about');
Route::get('/gioi-thieu/{slug}', [Frontend\CompanyContentController::class, 'show'])->name('about.content.show');
Route::get('/dich-vu', [Frontend\ServiceController::class, 'index'])->name('services.index');
Route::get('/dich-vu/{slug}', [Frontend\ServiceController::class, 'resolve'])->name('services.show');
Route::get('/du-an', [Frontend\ProjectController::class, 'index'])->name('projects.index');
Route::get('/du-an/{slug}', [Frontend\ProjectController::class, 'resolve'])->name('projects.show');
Route::get('/khach-hang', [Frontend\ClientController::class, 'index'])->name('clients.index');
Route::get('/bang-gia', [Frontend\PricingController::class, 'index'])->name('pricing');
Route::get('/tin-tuc', [Frontend\PostController::class, 'index'])->name('news.index');
Route::get('/tin-tuc/{slug}', [Frontend\SlugController::class, 'show'])->name('news.show');
Route::get('/lien-he', [Frontend\ContactController::class, 'index'])->name('contact');
Route::view('/chinh-sach-bao-mat', 'frontend.policies.privacy')->name('policies.privacy');
Route::post('/lien-he', [Frontend\ContactController::class, 'submit'])->middleware('throttle:frontend-forms')->name('contact.submit');
Route::post('/dang-ky-nhan-tin', [Frontend\NewsletterController::class, 'store'])->middleware('throttle:frontend-forms')->name('newsletter.store');
Route::post('/binh-luan', [Frontend\CommentController::class, 'store'])->middleware('throttle:frontend-forms')->name('comments.store');

Route::redirect('/login', '/admin/login')->name('login');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Slug động theo domain (dùng cho các route nội dung đa hình phía ngoài)
Route::get('/{domain}/{slug}', [Frontend\SlugController::class, 'showByDomain'])->name('content.show');
