<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    private function supabaseConfig(): array
    {
        return [
            'url' => config('services.supabase.url'),
            'key' => config('services.supabase.secret_key'),
            'bucket' => config('services.supabase.bucket'),
        ];
    }

    private function storageConfigured(array $config): bool
    {
        return ! empty($config['url'])
            && ! empty($config['key'])
            && ! empty($config['bucket']);
    }

    private function objectUrl(array $config, string $path): string
    {
        return rtrim($config['url'], '/')
            . '/storage/v1/object/'
            . rawurlencode($config['bucket'])
            . '/'
            . implode('/', array_map(
                'rawurlencode',
                explode('/', $path)
            ));
    }

    public function upload(Request $request, string $roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $room) {
            return response()->json([
                'message' => 'Room not found or expired.',
            ], 404);
        }

        $membership = $request->session()->get(
            "authorized_rooms.{$roomCode}"
        );

        if (
            ! is_array($membership)
            || empty($membership['uid'])
            || empty($membership['nickname'])
        ) {
            return response()->json([
                'message' => 'You are not authorized for this room.',
            ], 403);
        }

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ]);

        $file = $validated['file'];
        $config = $this->supabaseConfig();

        if (! $this->storageConfigured($config)) {
            Log::error('Supabase attachment configuration is incomplete.', [
                'url_present' => ! empty($config['url']),
                'secret_key_present' => ! empty($config['key']),
                'bucket_present' => ! empty($config['bucket']),
            ]);

            return response()->json([
                'message' => 'File storage is not configured.',
            ], 503);
        }

        $extension = strtolower($file->extension());

        if (! $extension) {
            return response()->json([
                'message' => 'The uploaded file has an unsupported extension.',
            ], 422);
        }

        $path = $roomCode . '/' . Str::uuid() . '.' . $extension;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $config['key'],
                    'Authorization' => 'Bearer ' . $config['key'],
                    'Content-Type' => $file->getMimeType()
                        ?: 'application/octet-stream',
                    'x-upsert' => 'false',
                ])
                ->withBody(
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType() ?: 'application/octet-stream'
                )
                ->post($this->objectUrl($config, $path));

            if (! $response->successful()) {
                Log::error('Supabase attachment upload failed.', [
                    'status' => $response->status(),
                    'room_code' => $roomCode,
                    'response' => $response->json() ?? $response->body(),
                ]);

                return response()->json([
                    'message' => 'The attachment could not be uploaded.',
                ], 502);
            }

            return response()->json([
                'message' => 'Attachment uploaded.',
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Supabase attachment upload exception.', [
                'room_code' => $roomCode,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'File storage is temporarily unavailable.',
            ], 503);
        }
    }

    public function download(Request $request, string $roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $room) {
            return response()->json([
                'message' => 'Room not found or expired.',
            ], 404);
        }

        $membership = $request->session()->get(
            "authorized_rooms.{$roomCode}"
        );

        if (
            ! is_array($membership)
            || empty($membership['uid'])
            || empty($membership['nickname'])
        ) {
            return response()->json([
                'message' => 'You are not authorized for this room.',
            ], 403);
        }

        $path = $request->query('path');

        if (
            ! is_string($path)
            || ! preg_match(
                '/\A' . preg_quote($roomCode, '/') . '\/[a-f0-9-]+\.(jpg|jpeg|png|gif|webp|pdf|txt|doc|docx|xls|xlsx)\z/i',
                $path
            )
        ) {
            return response()->json([
                'message' => 'Invalid attachment path.',
            ], 400);
        }

        $config = $this->supabaseConfig();

        if (! $this->storageConfigured($config)) {
            Log::error('Supabase attachment configuration is incomplete.', [
                'url_present' => ! empty($config['url']),
                'secret_key_present' => ! empty($config['key']),
                'bucket_present' => ! empty($config['bucket']),
            ]);

            return response()->json([
                'message' => 'File storage is not configured.',
            ], 503);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $config['key'],
                    'Authorization' => 'Bearer ' . $config['key'],
                ])
                ->get($this->objectUrl($config, $path));

            if (! $response->successful()) {
                Log::warning('Supabase attachment download failed.', [
                    'status' => $response->status(),
                    'room_code' => $roomCode,
                ]);

                return response()->json([
                    'message' => 'Attachment not found or unavailable.',
                ], 404);
            }

            return response($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type')
                    ?: 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        } catch (\Throwable $e) {
            Log::error('Supabase attachment download exception.', [
                'room_code' => $roomCode,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Attachment storage is temporarily unavailable.',
            ], 503);
        }
    }
}
