<?php

use App\Modules\Offers\Controllers\PublicOfferController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../app/Modules/Embassies/routes.php';
require __DIR__.'/../app/Modules/Offers/routes.php';
require __DIR__.'/../app/Modules/Documents/routes.php';
require __DIR__.'/../app/Modules/Applications/routes.php';
require __DIR__.'/../app/Modules/Candidates/routes.php';
require __DIR__.'/../app/Modules/Messaging/routes.php';
require __DIR__.'/../app/Modules/Notifications/routes.php';
require __DIR__.'/../app/Modules/CMS/routes.php';
require __DIR__.'/../app/Modules/Search/routes.php';
require __DIR__.'/../app/Modules/SearchAlerts/routes.php';
require __DIR__.'/../app/Modules/Analytics/routes.php';
require __DIR__.'/../app/Modules/Administration/routes.php';

// Homepage and the public offers listing currently render the exact same
// content — both point at the same controller action rather than
// duplicating the query/transform logic between two route closures.
// Once a real home page (hero, stats, featured offers) is built, the '/'
// route should be updated to point at a dedicated HomeController.
// The 'public' rate limiter (60 req/min per IP) is registered in
// AppServiceProvider::boot() — audit fix for missing throttling.
Route::middleware(['locale', 'throttle:public'])->group(function (): void {
    Route::get('/', [PublicOfferController::class, 'index'])->name('home');
    Route::get('/offers', [PublicOfferController::class, 'index'])->name('offers.index');
});
