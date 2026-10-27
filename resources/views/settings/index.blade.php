@extends('layouts.app')

@section('title', 'Settings | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endpush

@section('content')

<div class="settings-page">

    <div class="settings-container">

        <header class="settings-header">

            <a href="{{ route('home') }}" class="settings-back"
               aria-label="Back to Home">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <h1>Settings</h1>

            <span class="settings-header-spacer"></span>

        </header>

        <div class="settings-intro">
            <div class="settings-avatar">
                <i class="fa-solid fa-user-secret"></i>
            </div>

            <h2>Personalize Your Experience</h2>

            <p>Manage your ShadowChat preferences and privacy.</p>
        </div>

        <section class="settings-section">

            <h3 class="settings-section-title">APPEARANCE</h3>

            <div class="settings-card">

                <div class="settings-row">

                    <div class="settings-row-icon purple-icon">
                        <i class="fa-solid fa-moon"></i>
                    </div>

                    <div class="settings-row-content">
                        <span class="settings-row-title">Dark Mode</span>
                        <small>Use the dark ShadowChat appearance</small>
                    </div>

                    <label class="settings-switch">
                        <input type="checkbox" id="darkModeToggle" checked>
                        <span class="settings-slider"></span>
                    </label>

                </div>

            </div>

        </section>

        <section class="settings-section">

            <h3 class="settings-section-title">PRIVACY & DATA</h3>

            <div class="settings-card">

                <div class="settings-row">

                    <div class="settings-row-icon blue-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                    <div class="settings-row-content">
                        <span class="settings-row-title">Auto Delete</span>
                        <small>Room expiration preference</small>
                    </div>

                    <span class="settings-value">24 hours</span>

                </div>

                <div class="settings-divider"></div>

                <button type="button"
                        class="settings-row settings-action"
                        id="clearDataButton">

                    <div class="settings-row-icon red-icon">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>

                    <div class="settings-row-content">
                        <span class="settings-row-title">Clear Local Data</span>
                        <small>Remove saved preferences from this browser</small>
                    </div>

                    <i class="fa-solid fa-chevron-right settings-chevron"></i>

                </button>

            </div>

            <p class="settings-hint">
                Clearing local data does not delete server-side rooms or Firebase messages.
            </p>

        </section>

        <section class="settings-section">

            <h3 class="settings-section-title">ABOUT</h3>

            <div class="settings-card">

                <div class="settings-row">

                    <div class="settings-row-icon green-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div class="settings-row-content">
                        <span class="settings-row-title">Privacy</span>
                        <small>Chat without creating an account</small>
                    </div>

                    <i class="fa-solid fa-chevron-right settings-chevron"></i>

                </div>

                <div class="settings-divider"></div>

                <div class="settings-row">

                    <div class="settings-row-icon purple-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>

                    <div class="settings-row-content">
                        <span class="settings-row-title">ShadowChat</span>
                        <small>Private conversations, brighter connections.</small>
                    </div>

                    <span class="settings-value">v1.0</span>

                </div>

            </div>

        </section>

        <p class="settings-message" id="settingsMessage"
           role="status" aria-live="polite"></p>

        <p class="settings-footer">
            Made for conversations that matter.
        </p>

    </div>

</div>

<script>
    const darkModeToggle = document.getElementById('darkModeToggle');
    const clearDataButton = document.getElementById('clearDataButton');
    const settingsMessage = document.getElementById('settingsMessage');

    const savedTheme = localStorage.getItem('shadowchat_theme');

    if (savedTheme === 'light') {
        darkModeToggle.checked = false;
        document.body.classList.add('shadowchat-light-mode');
    }

    darkModeToggle.addEventListener('change', function () {
        if (this.checked) {
            localStorage.setItem('shadowchat_theme', 'dark');
            document.body.classList.remove('shadowchat-light-mode');
            settingsMessage.textContent = 'Dark mode enabled.';
        } else {
            localStorage.setItem('shadowchat_theme', 'light');
            document.body.classList.add('shadowchat-light-mode');
            settingsMessage.textContent = 'Light mode enabled.';
        }
    });

    clearDataButton.addEventListener('click', function () {
        const confirmed = confirm(
            'Clear ShadowChat preferences and local session data from this browser? This will not delete room messages stored in Firebase.'
        );

        if (!confirmed) {
            return;
        }

        localStorage.removeItem('shadowchat_theme');

        darkModeToggle.checked = true;
        document.body.classList.remove('shadowchat-light-mode');

        settingsMessage.textContent =
            'Saved theme preference cleared. Chat session data was kept so your current room is not interrupted.';
    });
</script>

@endsection

