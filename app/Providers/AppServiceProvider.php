<?php

namespace App\Providers;

use App\Modules\Offers\Models\Offer;
use App\Modules\Embassies\Models\Embassy;
use App\Modules\Embassies\Policies\EmbassyPolicy;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Policies\ApplicationPolicy;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Policies\DocumentPolicy;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Policies\ConversationPolicy;
use App\Modules\Offers\Policies\OfferPolicy;
use App\Modules\Offers\Observers\OfferObserver;
use App\Modules\Candidates\Models\CandidateProfile;
use App\Modules\Candidates\Policies\CandidateProfilePolicy;
use App\Modules\SearchAlerts\Models\SearchAlert;
use App\Modules\SearchAlerts\Policies\SearchAlertPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('asd.telescope.enabled') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Offer::class, OfferPolicy::class);
        Offer::observe(OfferObserver::class);
        Gate::policy(Embassy::class, EmbassyPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(CandidateProfile::class, CandidateProfilePolicy::class);
        Gate::policy(SearchAlert::class, SearchAlertPolicy::class);

        // Audit fix: no rate limiting existed anywhere in the application.
        // 'public' — applied to all unauthenticated / public-facing routes.
        //   60 req/min per IP is a conservative baseline; tighten once
        //   real traffic baselines are known.
        // 'auth' — reserved for future login / register / password-reset
        //   endpoints. 10 req/min is standard brute-force protection for
        //   credential submission routes.
        RateLimiter::for('public', function (Request $request): Limit {
            return Limit::perMinute(config('asd.rate_limits.public', 60))
                ->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            return Limit::perMinute(config('asd.rate_limits.auth', 10))
                ->by($request->ip());
        });

        RateLimiter::for('search', function (Request $request): Limit {
            return Limit::perMinute(config('asd.rate_limits.search', 30))
                ->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        RateLimiter::for('uploads', function (Request $request): Limit {
            return Limit::perMinute(config('asd.rate_limits.uploads', 20))
                ->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });
    }
}
