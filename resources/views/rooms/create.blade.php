@extends('layouts.app')

@section('title', 'Create Room | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/room.css') }}">
@endpush

@section('content')

<div class="create-room-page">

    <a href="{{ url('/') }}" class="room-back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="create-room-content">

        <div class="create-room-icon">
            <i class="fa-solid fa-plus"></i>
        </div>

        <h1>Create a Room</h1>

        <p class="create-room-subtitle">
            Create a private temporary room<br>
            and start chatting anonymously.
        </p>

        <div class="create-room-card">

            <div class="room-option">

                <div class="option-icon">
                    <i class="fa-solid fa-user-secret"></i>
                </div>

                <div class="option-content">
                    <h3>Anonymous</h3>
                    <p>No account or personal information required.</p>
                </div>

            </div>

            <div class="room-option">

                <div class="option-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="option-content">
                    <h3>Temporary</h3>
                    <p>Room automatically expires after 24 hours.</p>
                </div>

            </div>

            <div class="room-option">

                <div class="option-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div class="option-content">
                    <h3>Private</h3>
                    <p>Share the room code only with people you trust.</p>
                </div>

            </div>

        </div>


        <form action="{{ route('rooms.store') }}" method="POST">

            @csrf

            <div class="join-input-box">

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
                Create Room
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </form>

        <p class="room-bottom-text">
            No registration • No tracking • No logs
        </p>

    </div>

</div>

@endsection