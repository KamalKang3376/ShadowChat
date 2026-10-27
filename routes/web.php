<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AttachmentController;

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('rooms.home');
})->name('home');

Route::get('/chat', function () {
    return view('chat');
})->name('chat');


Route::get('/create-room', [RoomController::class, 'create'])
    ->name('rooms.create');

Route::post('/create-room', [RoomController::class, 'store'])
    ->name('rooms.store');

Route::get('/room-created/{roomCode}', [RoomController::class, 'created'])
    ->name('rooms.created');

Route::get('/chat/{roomCode}', [ChatController::class, 'show'])
    ->name('rooms.chat');

Route::get('/chat/{roomCode}/firebase-token', [
    ChatController::class,
    'firebaseToken',
])->name('rooms.firebase-token');


Route::get('/join-room', function () {
    return view('rooms.join');
})->name('rooms.join');

Route::post('/join-room', [RoomController::class, 'join'])
    ->name('rooms.join.submit');

Route::get('/room/{roomCode}/info', [RoomController::class, 'info'])
    ->name('rooms.info');

Route::get('/settings', function () {
    return view('settings.index');
})->name('settings');

Route::view('/chat-ended', 'rooms.chat-ended')
    ->name('rooms.ended');

Route::post(
    '/chat/{roomCode}/attachments',
    [AttachmentController::class, 'upload']
)->name('chat.attachments.upload');

Route::get(
    '/chat/{roomCode}/attachments/download',
    [AttachmentController::class, 'download']
)->name('chat.attachments.download');