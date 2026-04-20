<?php

namespace App\Providers;

use App\Models\JenisTagihan;
use App\Models\TagihanSiswa;
use App\Policies\JenisTagihanPolicy;
use App\Policies\TagihanSiswaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(TagihanSiswa::class, TagihanSiswaPolicy::class);
        Gate::policy(JenisTagihan::class, JenisTagihanPolicy::class);
    }
}
