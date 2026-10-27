<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Throwable;

#[Signature('app:cleanup-expired-rooms')]
#[Description('Clean up expired ShadowChat rooms and their Firebase data')]
class CleanupExpiredRooms extends Command
{
    public function handle(): int
    {
        $credentials = config('firebase.credentials');
        $databaseUrl = config('firebase.database_url');

        if (
            !is_string($credentials) ||
            !is_file($credentials) ||
            !is_string($databaseUrl) ||
            $databaseUrl === ''
        ) {
            $this->error('Firebase configuration is missing or invalid.');

            return self::FAILURE;
        }

        try {
            $database = (new Factory())
                ->withServiceAccount($credentials)
                ->withDatabaseUri($databaseUrl)
                ->createDatabase();
        } catch (Throwable $e) {
            Log::error('ShadowChat cleanup: Firebase initialization failed.', [
                'exception' => get_class($e),
            ]);

            $this->error('Could not initialize Firebase.');

            return self::FAILURE;
        }

        $expiredRooms = Room::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredRooms->isEmpty()) {
            $this->info('No expired rooms found.');

            return self::SUCCESS;
        }

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($expiredRooms as $room) {
            $roomCode = (string) $room->room_code;

            try {
                // Remove Firebase messages and presence first.
                $database
                    ->getReference("rooms/{$roomCode}")
                    ->remove();

                // Delete participant records and the room in MySQL.
                DB::transaction(function () use ($room): void {
                    $room->participants()->delete();
                    $room->delete();
                });

                $deletedCount++;

                $this->info("Expired room {$roomCode} deleted.");
            } catch (Throwable $e) {
                $failedCount++;

                Log::error('ShadowChat expired-room cleanup failed.', [
                    'room_code' => $roomCode,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);

                $this->error("Could not completely clean up room {$roomCode}.");
            }
        }

        $this->info("Cleanup finished. Deleted: {$deletedCount}; failed: {$failedCount}.");

        return $failedCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}