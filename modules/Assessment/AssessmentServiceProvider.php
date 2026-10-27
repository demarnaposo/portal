<?php

namespace Modules\Assessment;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Assessment\Application\AttemptStore;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Assessment\Domain\AssessmentAttempt;
use Modules\Assessment\Http\Controllers\ClaimController;
use Modules\Assessment\Http\Controllers\ScoreController;
use Modules\Assessment\Listeners\ClaimPendingAssessment;
use Modules\Identity\Contracts\Events\CustomerAuthenticated;
use Modules\Identity\Contracts\Events\CustomerRegistered;

/**
 * Attempt assessment + klaim ke akun. Scoring (bobot) tetap milik CMS, dipanggil lewat Cms\Contracts.
 * Bergantung pada Identity & Cms hanya melalui `Contracts`.
 */
final class AssessmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'assessment');
        $this->app->bind(AssessmentResults::class, AttemptStore::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        RateLimiter::for('assessment-score', fn (Request $request) => Limit::perMinute((int) config('assessment.score_rate_limit'))
            ->by($request->ip()));

        // Publik untuk landing (CORS: config/cors.php). Tanpa session/cookie.
        Route::prefix('api')->middleware(['api', 'throttle:assessment-score'])
            ->post('assessment/score', ScoreController::class)->name('api.assessment.score');

        Route::middleware('web')->name('portal.')
            ->get('assessment/claim', ClaimController::class)->name('assessment.claim');

        // model:prune default hanya memindai app/Models, jadi model modul disebut eksplisit.
        $this->callAfterResolving(Schedule::class, fn (Schedule $schedule) => $schedule
            ->command('model:prune', ['--model' => [AssessmentAttempt::class]])->daily());

        // Klaim hasil anonim ke akun saat login/daftar (+ middleware CapturePendingClaim di grup `web`, bootstrap/app.php).
        Event::listen([CustomerRegistered::class, CustomerAuthenticated::class], ClaimPendingAssessment::class);
    }
}
