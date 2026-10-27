@extends('layouts.app')

@section('title', 'Room Created | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/room-created.css') }}">
@endpush

@section('content')

<div class="room-page">

    <div class="room-container">

        <a href="{{ route('home') }}" class="room-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
        </a>

        <div class="room-card">

            <div class="room-icon">
                <i class="fa-solid fa-check"></i>
            </div>

            <h1>Room Created!</h1>

            <p class="room-description">
                Your private room is ready.
                Share the room code with the people
                you want to chat with.
            </p>

            <div class="room-code-box">

                <span class="room-code-label">
                    ROOM CODE
                </span>

                <div class="room-code" id="roomCode">
                    {{ $room->room_code }}
                </div>

                <div class="room-code-actions">

                    <button
                        type="button"
                        class="copy-code-btn"
                        id="copyCodeBtn"
                    >
                        <i class="fa-regular fa-copy"></i>
                        Copy Code
                    </button>

                    <button
                        type="button"
                        class="share-code-btn"
                        id="shareCodeBtn"
                    >
                        <i class="fa-solid fa-share-nodes"></i>
                        Share Code
                    </button>

                </div>

                <p
                    class="share-message"
                    id="shareMessage"
                    role="status"
                    aria-live="polite"
                ></p>

            </div>

            <div class="room-features">

                <div class="room-feature">
                    <i class="fa-solid fa-user-secret"></i>
                    <span>Anonymous</span>
                </div>

                <div class="room-feature">
                    <i class="fa-solid fa-clock"></i>
                    <span>Expires in 24 hours</span>
                </div>

                <div class="room-feature">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Private Room</span>
                </div>

            </div>

            <a
                href="{{ route('rooms.chat', $room->room_code) }}"
                class="create-room-btn"
            >
                <i class="fa-solid fa-comments"></i>
                Enter Room
            </a>

            <p class="room-note">
                Keep your room code private and share it only
                with trusted people.
            </p>

        </div>

    </div>

</div>

<script>
    const roomCode = @json($room->room_code);
    const joinPageUrl = @json(route('rooms.join'));

    const copyCodeBtn = document.getElementById('copyCodeBtn');
    const shareCodeBtn = document.getElementById('shareCodeBtn');
    const shareMessage = document.getElementById('shareMessage');

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

    copyCodeBtn.addEventListener('click', async function () {
        try {
            await copyText(roomCode);

            copyCodeBtn.innerHTML =
                '<i class="fa-solid fa-check"></i> Copied';

            shareMessage.textContent =
                'Room code copied successfully!';

            setTimeout(function () {
                copyCodeBtn.innerHTML =
                    '<i class="fa-regular fa-copy"></i> Copy Code';
            }, 2000);

        } catch (error) {
            shareMessage.textContent =
                'Copy failed. Your room code is: ' + roomCode;
        }
    });

    shareCodeBtn.addEventListener('click', async function () {
        const invitationText =
            'Join my ShadowChat room!\\n' +
            'Room Code: ' + roomCode + '\\n' +
            'Open ShadowChat: ' + joinPageUrl;

        try {
            if (navigator.share) {
                await navigator.share({
                    title: 'Join my ShadowChat room',
                    text: invitationText
                });

                shareMessage.textContent =
                    'Share menu opened successfully!';
            } else {
                await copyText(invitationText);

                shareMessage.textContent =
                    'Invitation copied! Send it to your friends.';
            }

        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            try {
                await copyText(invitationText);

                shareMessage.textContent =
                    'Invitation copied! Send it to your friends.';
            } catch (copyError) {
                shareMessage.textContent =
                    'Share failed. Room code: ' + roomCode;
            }
        }
    });
</script>

@endsection
