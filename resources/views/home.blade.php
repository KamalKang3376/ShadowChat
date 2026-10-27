@extends('layouts.app')

@section('title', 'ShadowChat | Chat Freely. Stay Private.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

<div class="welcome-page">

    <div class="welcome-content">

        <div class="shadow-logo">
            <div class="logo-bubble">
                <i class="fa-solid fa-comment-dots"></i>
            </div>
        </div>

        <h1 class="shadow-title">
            Shadow<span>Chat</span>
        </h1>

        <p class="shadow-tagline">
            Chat Freely. Stay Private.
        </p>

        <p class="shadow-description">
            A simple, secure and anonymous<br>
            way to chat with anyone.<br>
            No login. No tracking. Just real<br>
            conversations.
        </p>

        <div class="security-features">

            <div class="security-item">

                <div class="security-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <span>100% Anonymous</span>

            </div>

            <div class="security-item">

                <div class="security-icon">
                    <i class="fa-solid fa-eye-slash"></i>
                </div>

                <span>No Logs</span>

            </div>

            <div class="security-item">

                <div class="security-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <span>End-to-End Encrypted</span>

            </div>

        </div>

       <a href="{{ route('home') }}" class="get-started-btn">
            Get Started
            <i class="fa-solid fa-arrow-right"></i>
        </a>

        <p class="welcome-bottom-text">
            Private People. Brighter Conversations.
        </p>

    </div>

</div>

@endsection