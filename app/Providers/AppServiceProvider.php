<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Builder::defaultStringLength(191);

        Blade::directive('label', function ($expression) {
            return "<?php echo \\App\\Helpers\\Labels::get($expression); ?>";
        });

        // Partage le tenant (avec modules) dans TOUTES les vues authentifiées.
        // Le cache statique évite les requêtes répétées sur le même request.
        View::composer('*', function ($view) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (!$user || !$user->tenant_id) return;

            static $tenant = null;
            if ($tenant === null) {
                $tenant = $user->load('tenant.modules')->tenant;
            }

            $view->with('tenant', $tenant);
        });
    }
}
