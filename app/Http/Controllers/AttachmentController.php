<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    private const MAX_FILE_SIZE = 10240;

    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx',
    ];

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const EXTENSION_MIME_TYPES = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
        'doc' => ['application/msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ];

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

    private function authorizeRoom(
        Request $request,
        string $roomCode
    ): ?\Illuminate\Http\JsonResponse {
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

        return null;
    }

    private function validateAttachment(
        Request $request
    ): UploadedFile|\Illuminate\Http\JsonResponse {
        $file = $request->file('file');

        if (! $file || ! $file->isValid()) {
            return response()->json([
                'message' => 'No valid file was received. Please select it again.',
            ], 422);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            return response()->json([
                'message' => 'The maximum attachment size is 10 MB.',
            ], 422);
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $detectedMime = strtolower(
            $file->getMimeType() ?? ''
        );

        // Log diagnostic information without logging file contents.
        Log::info('ShadowChat attachment diagnostic', [
            'original_extension' => $extension,
            'detected_mime' => $detectedMime,
            'guessed_extension' => $file->guessExtension(),
            'size' => $file->getSize(),
        ]);

        if (! in_array(
            $extension,
            self::ALLOWED_EXTENSIONS,
            true
        )) {
            return response()->json([
                'message' => 'Unsupported extension. Allowed: JPG, JPEG, PNG, GIF, WebP, PDF, TXT, DOC, DOCX, XLS and XLSX.',
            ], 422);
        }

        $allowedMimes = self::EXTENSION_MIME_TYPES[$extension] ?? [];

        if (! in_array($detectedMime, $allowedMimes, true)) {
            Log::warning('ShadowChat attachment MIME mismatch', [
                'extension' => $extension,
                'detected_mime' => $detectedMime,
            ]);

            return response()->json([
                'message' => 'The file contents do not match the selected file type. Please try another file.',
            ], 422);
        }

        return $file;
    }

    private function getStorageConfig(): ?array
    {
        $config = $this->supabaseConfig();

        if (! $this->storageConfigured($config)) {
            Log::error(
                'Supabase attachment configuration is incomplete.',
                [
                    'url_present' => ! empty($config['url']),
                    'secret_key_present' => ! empty($config['key']),
                    'bucket_present' => ! empty($config['bucket']),
                ]
            );

            return null;
        }

        return $config;
    }

    public function upload(Request $request, string $roomCode)
    {
        $authorizationError = $this->authorizeRoom(
            $request,
            $roomCode
        );

        if ($authorizationError) {
            return $authorizationError;
        }

        $file = $this->validateAttachment($request);

        if (! $file instanceof UploadedFile) {
            return $file;
        }

        $config = $this->getStorageConfig();

        if (! $config) {
            return response()->json([
                'message' => 'File storage is not configured.',
            ], 503);
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $mimeType = $file->getMimeType()
            ?: 'application/octet-stream';

        $path = $roomCode . '/' . Str::uuid()
            . '.' . $extension;

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $config['key'],
                    'Authorization' => 'Bearer ' . $config['key'],
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'false',
                ])
                ->withBody(
                    file_get_contents($file->getRealPath()),
                    $mimeType
                )
                ->post($this->objectUrl($config, $path));

            if (! $response->successful()) {
                Log::error('Supabase attachment upload failed.', [
                    'status' => $response->status(),
                    'room_code' => $roomCode,
                    'response' => $response->json()
                        ?? $response->body(),
                ]);

                return response()->json([
                    'message' => 'The attachment could not be uploaded.',
                ], 502);
            }

            return response()->json([
                'message' => 'Attachment uploaded successfully.',
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'type' => $mimeType,
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
        $authorizationError = $this->authorizeRoom(
            $request,
            $roomCode
        );

        if ($authorizationError) {
            return $authorizationError;
        }

        $path = $request->query('path');

        $extensionPattern = implode(
            '|',
            self::ALLOWED_EXTENSIONS
        );

        if (
            ! is_string($path)
            || ! preg_match(
                '/\A' . preg_quote($roomCode, '/') .
                '\/[a-f0-9-]+\.(' .
                $extensionPattern .
                ')\z/i',
                $path
            )
        ) {
            return response()->json([
                'message' => 'Invalid attachment path.',
            ], 400);
        }

        $config = $this->getStorageConfig();

        if (! $config) {
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
                Log::warning(
                    'Supabase attachment download failed.',
                    [
                        'status' => $response->status(),
                        'room_code' => $roomCode,
                    ]
                );

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
            Log::error(
                'Supabase attachment download exception.',
                [
                    'room_code' => $roomCode,
                    'message' => $e->getMessage(),
                ]
            );

            return response()->json([
                'message' => 'Attachment storage is temporarily unavailable.',
            ], 503);
        }
    }
}
