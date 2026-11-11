<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DisclaimerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/preview/{post}', [BlogController::class, 'preview'])->name('blog.preview')->middleware(['auth', 'signed']);
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
Route::get('/locations/{state:slug}', [LocationController::class, 'state'])->name('locations.state');
Route::get('/locations/{state:slug}/{city:slug}', [LocationController::class, 'city'])->name('locations.city')->scopeBindings();

Route::get('/disclaimer', DisclaimerController::class)->name('disclaimer');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:leads');
Route::get('/contact/thank-you', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::get('/sitemap.xml', [SeoController::class, 'sitemapIndex'])->name('sitemap');
Route::get('/sitemap-{type}.xml', [SeoController::class, 'sitemap'])->name('sitemap.child')->whereIn('type', SeoController::SITEMAPS);
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');
Route::get('/llms-full.txt', [SeoController::class, 'llmsFull'])->name('llms.full');

// Filament's panel guard sends unauthenticated users here.
Route::redirect('/login', '/admin/login')->name('login');

// CMS pages (About, legal pages, the paid call landing page and custom pages). Must stay last.
Route::get('/{page:slug}', PageController::class)->name('pages.show')->where('page', '[a-z0-9-]+');
