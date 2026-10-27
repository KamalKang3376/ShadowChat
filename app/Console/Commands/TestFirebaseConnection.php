<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Throwable;

class TestFirebaseConnection extends Command
{
    protected $signature = 'firebase:test';

    protected $description = 'Test Firebase Admin SDK connection';

    public function handle(): int
    {
        try {
            $credentials = config('firebase.credentials');
            $databaseUrl = config('firebase.database_url');
            if (! file_exists($credentials)) {
                $this->error('Firebase service account file not found.');
                return self::FAILURE;
            }

            if (! $databaseUrl) {
                $this->error('Firebase database URL is missing.');
                return self::FAILURE;
            }

            $firebase = (new Factory())
                ->withServiceAccount($credentials)
                ->withDatabaseUri($databaseUrl);

            $firebase->createAuth();
            $firebase->createDatabase();

            $this->info('Firebase Admin SDK initialized successfully.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error(
                'Firebase initialization failed. Check storage/logs/laravel.log.'
            );

            return self::FAILURE;
        }
    }
}