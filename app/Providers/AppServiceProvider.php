<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if (in_array($event->connection->getDriverName(), ['mysql', 'mariadb'], true)) {
                $event->connection->statement("SET time_zone = '+06:00'");
            }
        });

        //dd("test");
//        Gate::define('hrm.employee.index', function (User $user) {
//            dd($user);
//            return $user->role_id == 2;
//        });
    }
}
