<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(\Kreait\Firebase\Contract\Messaging::class, function ($app) {
            $credentials = config('services.firebase.credentials')
                ?: env('FIREBASE_CREDENTIALS')
                ?: storage_path('app/firebase/firebase_credentials.json');

            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withProjectId(env('FIREBASE_PROJECT_ID', 'ultra-school-53719'));

            return $factory->createMessaging();
        });

        $this->app->alias(\Kreait\Firebase\Contract\Messaging::class, 'firebase.messaging');
    }
}
