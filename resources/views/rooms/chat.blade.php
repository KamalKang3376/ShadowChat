
@extends('layouts.app')

@section('title', 'Chat Room | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@section('content')

<div class="chat-page">

    <header class="chat-header">

        <a href="{{ route('home') }}" class="chat-back" aria-label="Back to home">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <div class="chat-room-info">
            <span class="chat-room-label">ROOM</span>
            <strong>{{ $room->room_code }}</strong>
        </div>

        <div class="chat-header-actions">

            <span class="online-count">
                <span class="online-dot"></span>
                1 Online
            </span>

            <a
                href="{{ route('rooms.info', ['roomCode' => $room->room_code]) }}"
                class="chat-info-btn"
                aria-label="Room Info"
                title="Room Info"
            >
                <i class="fa-solid fa-circle-info"></i>
            </a>

        </div>

    </header>

    <main class="chat-messages" aria-live="polite">

        <div class="chat-empty">

            <div class="chat-empty-icon">
                <i class="fa-solid fa-comments"></i>
            </div>

            <h2>You're in the room</h2>

            <p>
                Start the conversation.<br>
                Your messages are temporary and private.
            </p>

        </div>

    </main>

    <div class="chat-composer">

        <div id="attachmentPreview" class="attachment-preview" hidden>
            <div class="attachment-preview-info">
                <i class="fa-solid fa-paperclip"></i>
                <span id="attachmentName"></span>
            </div>

            <button
                type="button"
                id="removeAttachment"
                class="remove-attachment-btn"
                aria-label="Remove attachment"
                title="Remove attachment"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="chat-input-area">

            <button
                type="button"
                class="attachment-btn"
                id="attachmentButton"
                aria-label="Attach a file"
                title="Attach a file"
            >
                <i class="fa-solid fa-paperclip"></i>
            </button>

            <input
                type="file"
                id="fileInput"
                accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,text/plain,.doc,.docx,.xls,.xlsx"
                hidden
            >

            <div class="message-input-wrapper">

                <div
                    class="emoji-picker"
                    id="emojiPicker"
                    hidden
                >
                    <div class="emoji-picker-header">
                        <span>Choose an emoji</span>

                        <button
                            type="button"
                            id="closeEmojiPicker"
                            aria-label="Close emoji picker"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="emoji-grid" id="emojiGrid"></div>
                </div>

                <input
                    type="text"
                    id="messageInput"
                    placeholder="Type a message..."
                    autocomplete="off"
                    maxlength="5000"
                    aria-label="Type a message"
                >

                <button
                    type="button"
                    class="emoji-btn"
                    id="emojiButton"
                    aria-label="Choose emoji"
                    title="Choose emoji"
                >
                    <i class="fa-regular fa-face-smile"></i>
                </button>

            </div>

            <button
                type="button"
                class="send-btn"
                id="sendButton"
                aria-label="Send message"
                title="Send message"
            >
                <i class="fa-solid fa-paper-plane"></i>
            </button>

        </div>

        <div
            id="chatError"
            class="chat-error"
            role="alert"
            aria-live="assertive"
            hidden
        ></div>

    </div>

</div>

<script>
    window.shadowChatRoom = @json($room->room_code);
    window.shadowChatNickname = @json($nickname ?? session('nickname', 'Guest'));
</script>

<script type="module" src="{{ asset('js/chat.js') }}"></script>

<script>
    document.getElementById('closeEmojiPicker')?.addEventListener('click', () => {
        const picker = document.getElementById('emojiPicker');

        if (picker) {
            picker.hidden = true;
        }
    });
</script>

@endsection
