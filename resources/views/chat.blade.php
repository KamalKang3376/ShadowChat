@extends('layouts.app')

@section('title', 'Chats | ShadowChat')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat-list.css') }}">
@endpush

@section('content')

<div class="chat-list-page">

    <header class="chat-list-header">
        <div class="chat-brand">
            <div class="chat-brand-icon">
                <i class="fa-solid fa-comment-dots"></i>
            </div>

            <div>
                <h1>ShadowChat</h1>
                <p>Anonymous. Temporary. Safe.</p>
            </div>
        </div>

        <button type="button" class="header-icon" aria-label="Search chats"
                onclick="document.getElementById('chatSearch').focus()">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </header>

    <div class="chat-search">
        <i class="fa-solid fa-magnifying-glass"></i>

        <input type="search" id="chatSearch"
               placeholder="Search chats..."
               autocomplete="off">
    </div>

    <div class="chat-list-heading">
        <h2>Recent Chats</h2>
        <span id="chatCount">0 chats</span>
    </div>

    <div class="recent-chat-list" id="recentChatList"></div>

    <div class="chat-list-empty" id="chatEmptyState">
        <div class="empty-chat-art">
            <i class="fa-solid fa-comments"></i>
            <span class="empty-chat-dot"></span>
        </div>

        <h2>No Chats Yet</h2>

        <p>
            Start a new conversation or<br>
            join a room to chat with others.
        </p>

        <a href="{{ route('rooms.create') }}" class="create-room-button">
            <i class="fa-solid fa-plus"></i>
            Create a Room
        </a>

        <a href="{{ route('rooms.join') }}" class="join-room-button">
            <i class="fa-solid fa-user-group"></i>
            Join a Room
        </a>
    </div>

    <nav class="bottom-navigation">

        <a href="{{ route('home') }}" class="nav-item">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>

        <a href="{{ route('chat') }}" class="nav-item active">
            <i class="fa-regular fa-comments"></i>
            <span>Chat</span>
        </a>

        <a href="{{ route('settings') }}" class="nav-item">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>

    </nav>

</div>

<script>
    const chatSearch = document.getElementById('chatSearch');
    const recentChatList = document.getElementById('recentChatList');
    const chatEmptyState = document.getElementById('chatEmptyState');
    const chatCount = document.getElementById('chatCount');

    const sessionChats = @json(session('recent_chats', []));

    function loadRecentChats() {
        let localChats = [];

        try {
            localChats = JSON.parse(
                localStorage.getItem('shadowchat_recent_chats') || '[]'
            );

            if (!Array.isArray(localChats)) {
                localChats = [];
            }
        } catch (error) {
            localChats = [];
        }

        const combinedChats = [...sessionChats, ...localChats];
        const uniqueChats = [];
        const seenCodes = new Set();

        combinedChats.forEach(chat => {
            const roomCode = String(chat.roomCode || '');

            if (/^\d{6}$/.test(roomCode) && !seenCodes.has(roomCode)) {
                seenCodes.add(roomCode);
                uniqueChats.push({
                    roomCode,
                    lastMessage: String(chat.lastMessage || ''),
                    updatedAt: String(chat.updatedAt || ''),
                    onlineCount: Math.max(
                        0,
                        Number(chat.onlineCount) || 0
                    )
                });
            }
        });

        renderChats(uniqueChats);
    }

    function renderChats(chats) {
        const searchText = chatSearch.value.trim().toLowerCase();

        const filteredChats = chats.filter(chat =>
            chat.roomCode.toLowerCase().includes(searchText) ||
            chat.lastMessage.toLowerCase().includes(searchText)
        );

        recentChatList.replaceChildren();

        chatCount.textContent = filteredChats.length +
            (filteredChats.length === 1 ? ' chat' : ' chats');

        if (filteredChats.length === 0) {
            chatEmptyState.hidden = false;
            chatEmptyState.classList.toggle(
                'search-empty',
                searchText !== ''
            );

            const heading = chatEmptyState.querySelector('h2');
            const description = chatEmptyState.querySelector('p');

            if (searchText !== '') {
                heading.textContent = 'No Chats Found';
                description.textContent =
                    'Try searching with another room code.';
            } else {
                heading.textContent = 'No Chats Yet';
                description.innerHTML =
                    'Start a new conversation or<br>join a room to chat with others.';
            }

            return;
        }

        chatEmptyState.hidden = true;

        filteredChats.forEach(chat => {
            const link = document.createElement('a');
            link.className = 'recent-chat-card';
            link.href = '/chat/' + encodeURIComponent(chat.roomCode);

            const avatar = document.createElement('div');
            avatar.className = 'recent-chat-avatar';
            avatar.innerHTML = '<i class="fa-solid fa-comments"></i>';

            const details = document.createElement('div');
            details.className = 'recent-chat-details';

            const title = document.createElement('h3');
            title.textContent = 'Room ' + chat.roomCode;

            const preview = document.createElement('p');
            preview.textContent =
                chat.lastMessage || 'Tap to open this room';

            details.append(title, preview);

            const meta = document.createElement('div');
            meta.className = 'recent-chat-meta';

            const time = document.createElement('span');
            time.className = 'recent-chat-time';
            time.textContent = chat.updatedAt;

            const online = document.createElement('span');
            online.className = 'recent-chat-online';
            online.innerHTML = '<span></span>' +
                chat.onlineCount + ' online';

            meta.append(time, online);

            const arrow = document.createElement('i');
            arrow.className =
                'fa-solid fa-chevron-right recent-chat-arrow';

            link.append(avatar, details, meta, arrow);
            recentChatList.appendChild(link);
        });
    }

    chatSearch.addEventListener('input', loadRecentChats);

    loadRecentChats();
</script>



@endsection
