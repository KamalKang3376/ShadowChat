@extends('layouts.app')

@section('title', 'Chat Ended | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat-ended.css') }}">
@endpush

@section('content')
<div class="chat-ended-page">

    <div class="chat-ended-card">

        <div class="chat-ended-icon">
            <i class="fa-solid fa-comments"></i>
            <span class="ended-check">
                <i class="fa-solid fa-check"></i>
            </span>
        </div>

        <span class="chat-ended-label">CONVERSATION COMPLETE</span>

        <h1>Chat Ended</h1>

        <p class="chat-ended-description">
            You've left the conversation. Thanks for chatting on ShadowChat!
        </p>

        <div class="chat-ended-notice">
            <i class="fa-solid fa-shield-halved"></i>
            <p>
                Your conversation was temporary. Room and message deletion
                depends on your configured cleanup settings.
            </p>
        </div>

        <a href="{{ route('home') }}" class="chat-ended-primary">
            <i class="fa-solid fa-house"></i>
            Back to Home
        </a>

        <a href="{{ route('rooms.create') }}" class="chat-ended-secondary">
            <i class="fa-solid fa-plus"></i>
            Create a New Room
        </a>

        <div class="chat-ended-footer">
            <i class="fa-solid fa-lock"></i>
            Private conversations, brighter connections.
        </div>

    </div>

</div>
@endsection

