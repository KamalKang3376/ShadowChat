@extends('layouts.app')

@section('title', 'Room Info | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/room-info.css') }}">
@endpush

@section('content')
<div class="room-info-page">
    <div class="room-info-container">

        <header class="room-info-header">
            <a href="{{ route('rooms.chat', $room->room_code) }}"
               class="room-info-back"
               aria-label="Back to chat">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <h1>Room Info</h1>

            <span class="header-spacer"></span>
        </header>

        <section class="room-info-profile">
            <div class="room-info-icon">
                <i class="fa-solid fa-cube"></i>
            </div>

            <h2>Room {{ $room->room_code }}</h2>

            <div class="room-online-status">
                <span class="online-dot"></span>
                <span id="onlineCount">Connecting...</span>
            </div>
        </section>

        <section class="room-info-card">
            <button type="button" class="room-info-row" id="copyRoomCode">
                <span class="room-info-row-icon">
                    <i class="fa-solid fa-link"></i>
                </span>

                <span class="room-info-row-label">Copy Room Code</span>

                <i class="fa-regular fa-copy room-info-row-action"></i>
            </button>

            <button type="button" class="room-info-row" id="shareRoom">
                <span class="room-info-row-icon">
                    <i class="fa-solid fa-share-nodes"></i>
                </span>

                <span class="room-info-row-label">Share Room</span>

                <i class="fa-solid fa-chevron-right room-info-row-action"></i>
            </button>

            <a href="{{ route('rooms.chat', $room->room_code) }}"
               class="room-info-row">
                <span class="room-info-row-icon">
                    <i class="fa-solid fa-users"></i>
                </span>

                <span class="room-info-row-label">Participants</span>

                <span class="room-info-row-value" id="participantCount">
                    Connecting...
                </span>

                <i class="fa-solid fa-chevron-right room-info-row-action"></i>
            </a>

            <div class="room-info-row">
                <span class="room-info-row-icon">
                    <i class="fa-regular fa-clock"></i>
                </span>

                <span class="room-info-row-label">Auto Delete</span>

                <span class="room-info-row-value">After 24 hours</span>
            </div>

            <div class="room-info-row room-security-row">
                <span class="room-info-row-icon">
                    <i class="fa-solid fa-lock"></i>
                </span>

                <span class="room-info-row-text">
                    <span class="room-info-row-label">Privacy</span>
                    <small>Keep your room code private</small>
                </span>

                <i class="fa-solid fa-shield-halved room-security-icon"></i>
            </div>
        </section>

        <p class="room-info-message"
           id="roomInfoMessage"
           role="status"
           aria-live="polite"></p>

        <form action="{{ route('rooms.ended') }}"
              method="GET"
              onsubmit="return confirm('Are you sure you want to leave this room?');">
            <button type="submit" class="leave-room-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Leave Room</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <p class="room-info-footer">
            ShadowChat · Private conversations, brighter connections.
        </p>
    </div>
</div>

<script>
    const roomCode = @json($room->room_code);
    const joinPageUrl = @json(route('rooms.join'));

    const copyButton = document.getElementById('copyRoomCode');
    const shareButton = document.getElementById('shareRoom');
    const messageElement = document.getElementById('roomInfoMessage');

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return;
        }

        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';

        document.body.appendChild(textArea);
        textArea.select();

        const copied = document.execCommand('copy');
        textArea.remove();

        if (!copied) {
            throw new Error('Copy failed');
        }
    }

    copyButton.addEventListener('click', async () => {
        try {
            await copyText(roomCode);
            messageElement.textContent = 'Room code copied successfully!';
        } catch (error) {
            messageElement.textContent = 'Your room code is ' + roomCode;
        }
    });

    shareButton.addEventListener('click', async () => {
        const invitation =
            'Join my ShadowChat room!\n' +
            'Room Code: ' + roomCode + '\n' +
            'Join here: ' + joinPageUrl;

        try {
            if (navigator.share) {
                await navigator.share({
                    title: 'Join my ShadowChat room',
                    text: invitation
                });

                messageElement.textContent = 'Share menu opened.';
            } else {
                await copyText(invitation);
                messageElement.textContent = 'Room invitation copied!';
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                messageElement.textContent = 'Room code: ' + roomCode;
            }
        }
    });
</script>

<script type="module">
    import {
        initializeApp,
        getApps,
        getApp
    } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js";

    import {
        getDatabase,
        ref,
        onValue
    } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-database.js";

    import {
        getAuth,
        signInWithCustomToken
    } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-auth.js";

    const firebaseConfig = {
        apiKey: "AIzaSyDhTVWf1TBiJv7eL-Zrfxqwu5IZbeQxoCs",
        authDomain: "shadowchat-98c63.firebaseapp.com",
        databaseURL: "https://shadowchat-98c63-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "shadowchat-98c63",
        storageBucket: "shadowchat-98c63.firebasestorage.app",
        messagingSenderId: "639194484694",
        appId: "1:639194484694:web:3a1075f53ec251b88cde77"
    };

    const currentRoomCode = @json($room->room_code);

    const onlineElement = document.getElementById('onlineCount');
    const participantElement = document.getElementById('participantCount');

    async function loadRoomPresence() {
        try {
            const app = getApps().length
                ? getApp()
                : initializeApp(firebaseConfig);

            const auth = getAuth(app);

            // Ask Laravel for a room-specific Firebase custom token.
            const response = await fetch(
                `/chat/${encodeURIComponent(currentRoomCode)}/firebase-token`,
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

            const tokenData = await response.json().catch(() => ({}));

            if (!response.ok || !tokenData.token) {
                throw new Error(
                    tokenData.message ||
                    `Could not get Firebase authorization (${response.status}).`
                );
            }

            // Authenticate before accessing the protected database path.
            await signInWithCustomToken(auth, tokenData.token);

            const database = getDatabase(app);

            const presenceRef = ref(
                database,
                `rooms/${currentRoomCode}/presence`
            );

            onValue(
                presenceRef,
                (snapshot) => {
                    const presence = snapshot.val();

                    const count = presence
                        ? Object.values(presence).filter(
                            participant =>
                                participant &&
                                participant.online === true
                        ).length
                        : 0;

                    onlineElement.textContent = `${count} online`;
                    participantElement.textContent = `${count} online`;
                },
                (error) => {
                    console.error('Firebase presence read failed:', error);

                    onlineElement.textContent = 'Unable to load';
                    participantElement.textContent = 'Unavailable';

                    document.getElementById('roomInfoMessage').textContent =
                        'Could not load room participants. Please refresh the page.';
                }
            );
        } catch (error) {
            console.error('Room Info Firebase authentication failed:', error);

            onlineElement.textContent = 'Unable to load';
            participantElement.textContent = 'Unavailable';

            document.getElementById('roomInfoMessage').textContent =
                error.message || 'Could not connect to room information.';
        }
    }

    loadRoomPresence();
</script>
@endsection
