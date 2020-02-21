<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMacro();
    }

    private function registerMacro()
    {
        Builder::macro('toSqlString', function () {
            $bindings = $this->getBindings();
            $sql = str_replace('?', '%s', $this->toSql());
            foreach ($bindings as $key => $value) {
                if (is_string($value)) {
                    $bindings[$key] = "'{$value}'";
                }
            }
            return sprintf($sql, ...$bindings);
        });
    }
}
