<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Repository\RepositoryInterface;
use App\Repository\LanguageRepositoryInterface;
use App\Repository\FilmRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\CriticRepositoryInterface;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\LanguageRepository;
use App\Repository\Eloquent\FilmRepository;
use App\Repository\Eloquent\CriticRepository;
use App\Repository\Eloquent\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CriticRepositoryInterface::class, CriticRepository::class);
        $this->app->bind(FilmRepositoryInterface::class, FilmRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
