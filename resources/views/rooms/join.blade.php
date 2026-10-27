@extends('layouts.app')

@section('title', 'Join Room | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/room.css') }}">
@endpush

@section('content')

<div class="create-room-page">

    <a href="{{ route('home') }}" class="room-back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="create-room-content">

        <div class="create-room-icon">
            <i class="fa-solid fa-user-group"></i>
        </div>

        <h1>Join a Room</h1>

        <p class="create-room-subtitle">
            Enter the room code shared with you<br>
            and choose a nickname to start chatting.
        </p>

        <form action="{{ route('rooms.join.submit') }}" method="POST">

            @csrf

            <div class="join-input-box">

                <label for="room_code">
                    ROOM CODE
                </label>

                <input
                    type="text"
                    id="room_code"
                    name="room_code"
                    maxlength="6"
                    minlength="6"
                    inputmode="numeric"
                    placeholder="000000"
                    autocomplete="off"
                    value="{{ old('room_code') }}"
                    required
                >

                @error('room_code')
                    <span class="input-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>

            <div class="join-input-box nickname-box">

                <label for="nickname">
                    YOUR NICKNAME
                </label>

                <input
                    type="text"
                    id="nickname"
                    name="nickname"
                    maxlength="30"
                    placeholder="Enter your nickname"
                    autocomplete="off"
                    value="{{ old('nickname') }}"
                    required
                >

                @error('nickname')
                    <span class="input-error">
                        {{ $message }}
                    </span>
                @enderror

            </div>

            <button type="submit" class="neon-button">
                Join Room
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </form>

        <p class="room-bottom-text">
            No registration • No tracking • No logs
        </p>

    </div>

</div>

@endsection