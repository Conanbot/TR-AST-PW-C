<?php
namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.glo');
        Paginator::defaultSimpleView('vendor.pagination.glo');
        View::composer('*', function ($view) {
            if (Schema::hasTable('payment_methods')) {
                $view->with(
                    'activePaymentMethods',
                    \App\Models\PaymentMethod::active()->orderBy('sort_order')->get()->keyBy('code')
                );
            }
        });
        // Auto-create database geprek_glo if not exists in local Laragon environment
        if (app()->environment('local') && config('database.default') === 'mysql') {
            try {
                $host = config('database.connections.mysql.host', '127.0.0.1');
                $port = config('database.connections.mysql.port', 3306);
                $dbName = config('database.connections.mysql.database', 'geprek_glo');
                $user = config('database.connections.mysql.username', 'root');
                $pass = config('database.connections.mysql.password', '');
                $pdo = new \PDO("mysql:host={$host};port={$port}", $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT,
                    \PDO::ATTR_TIMEOUT => 2,
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            } catch (\Throwable $e) {
                // Silently skip if MySQL is not running yet
            }
        }
    }
}
