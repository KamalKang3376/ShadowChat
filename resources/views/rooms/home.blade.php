@extends('layouts.app')

@section('title', 'Welcome | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home-room.css') }}">
@endpush

@section('content')

<div class="room-home-page">

    <a href="{{ url('/') }}" class="home-back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <div class="room-home-content">

        <h1>Welcome</h1>

        <p class="home-subtitle">
            What would you like to do?
        </p>

        <div class="room-action-card">

            <a href="{{ route('rooms.create') }}" class="room-action">

                <div class="action-icon create-icon">
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div class="action-text">
                    <h2>Create a Room</h2>

                    <p>
                        Get a random room code<br>
                        and share it with your friend.
                    </p>
                </div>

                <i class="fa-solid fa-chevron-right action-arrow"></i>

            </a>

            <a href="{{ route('rooms.join') }}" class="room-action">

                <div class="action-icon join-icon">
                    <i class="fa-solid fa-user-group"></i>
                </div>

                <div class="action-text">
                    <h2>Join a Room</h2>

                    <p>
                        Enter a room code to<br>
                        start chatting.
                    </p>
                </div>

                <i class="fa-solid fa-chevron-right action-arrow"></i>

            </a>

        </div>

        <div class="home-message">
            Same room.<br>
            <span>Different world.</span> ✨
        </div>

    </div>

    <div class="home-planet"></div>

<nav class="bottom-navigation">

    <a href="{{ route('home') }}"
       class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>

    <a href="{{ route('chat') }}"
       class="nav-item {{ request()->routeIs('chat') || request()->routeIs('rooms.chat') ? 'active' : '' }}">
        <i class="fa-regular fa-comments"></i>
        <span>Chat</span>
    </a>

    <a href="{{ route('settings') }}"
       class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
        <i class="fa-solid fa-gear"></i>
        <span>Settings</span>
    </a>

</nav>



</div>
<script>
document.querySelectorAll('.bottom-navigation .nav-item').forEach(item => {
    item.addEventListener('click', function () {
        document.querySelectorAll('.bottom-navigation .nav-item')
            .forEach(link => link.classList.remove('active'));

        this.classList.add('active');
    });
});
</script>

@endsection