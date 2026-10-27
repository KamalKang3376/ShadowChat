import { initializeApp } from "https://www.gstatic.com/firebasejs/13.0.0/firebase-app.js";

import {
    getDatabase,
    ref,
    push,
    serverTimestamp,
    onChildAdded,
    onValue,
    onDisconnect,
    set,
    remove
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

const roomCode = String(window.shadowChatRoom || "").trim();
const nickname = window.shadowChatNickname || "Guest";

if (!roomCode) {
    throw new Error("Chat room code is missing.");
}

const app = initializeApp(firebaseConfig);
const database = getDatabase(app);
const auth = getAuth(app);

const participantStorageKey = `shadowchat_participant_${roomCode}`;

let sessionId = sessionStorage.getItem(participantStorageKey);

if (!sessionId) {
    sessionId = crypto.randomUUID();
    sessionStorage.setItem(participantStorageKey, sessionId);
}

const messageInput = document.getElementById("messageInput");
const sendButton = document.getElementById("sendButton");
const emojiButton = document.getElementById("emojiButton");
const emojiPicker = document.getElementById("emojiPicker");
const emojiGrid = document.getElementById("emojiGrid");
const attachmentButton = document.getElementById("attachmentButton");
const fileInput = document.getElementById("fileInput");
const attachmentPreview = document.getElementById("attachmentPreview");
const attachmentName = document.getElementById("attachmentName");
const removeAttachmentButton = document.getElementById("removeAttachment");
const chatError = document.getElementById("chatError");

let selectedFile = null;
let sending = false;
let firebaseReady = false;
let presenceRegistered = false;
let chatStarted = false;
let presenceListenerStarted = false;
let connectionListenerStarted = false;

const joinedAtStorageKey = `shadowchat_joined_at_${roomCode}`;

let joinedAt = Number(
    sessionStorage.getItem(joinedAtStorageKey)
);

if (!Number.isFinite(joinedAt) || joinedAt <= 0) {
    joinedAt = Date.now();

    sessionStorage.setItem(
        joinedAtStorageKey,
        String(joinedAt)
    );
}
let serverTimeOffset = 0;
let serverTimeOffsetLoaded = false;

const allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/webp",
    "application/pdf",
    "text/plain",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/vnd.ms-excel",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
];

const MAX_FILE_SIZE = 10 * 1024 * 1024;

const emojiList = [
    "😀", "😃", "😄", "😁", "😆", "😅", "😂", "🤣",
    "😊", "😇", "🙂", "😉", "😍", "🥰", "😘", "😎",
    "🤔", "🤗", "🥳", "😭", "😢", "😡", "👍", "👎",
    "👏", "🙌", "🙏", "❤️", "💕", "🔥", "🎉", "✨",
    "💯", "✅", "💖", "🌹", "😴", "🤝", "💐"
];

function showError(message) {
    console.error(message);

    if (chatError) {
        chatError.textContent = message;
        chatError.hidden = false;
    }
}

function clearError() {
    if (chatError) {
        chatError.textContent = "";
        chatError.hidden = true;
    }
}

function getFirebaseErrorMessage(error, action) {
    console.error(`${action}:`, error);

    if (
        error?.code === "PERMISSION_DENIED" ||
        error?.code === "PERMISSION_DENIED: Permission denied"
    ) {
        return "Firebase denied access to this room. Check your Firebase token and roomCode security rules.";
    }

    if (error?.code === "NETWORK_ERROR") {
        return "Network error. Check your internet connection.";
    }

    return `${action}: ${error?.message || error?.code || "Unknown error"}`;
}

function showAttachmentPreview(file) {
    if (attachmentName) {
        attachmentName.textContent = file.name;
        attachmentName.title = file.name;
    }

    if (attachmentPreview) {
        attachmentPreview.hidden = false;
    }
}

function clearSelectedAttachment() {
    selectedFile = null;

    if (fileInput) {
        fileInput.value = "";
    }

    if (attachmentName) {
        attachmentName.textContent = "";
        attachmentName.removeAttribute("title");
    }

    if (attachmentPreview) {
        attachmentPreview.hidden = true;
    }
}

function renderEmojiPicker() {
    if (!emojiGrid) return;

    emojiGrid.replaceChildren();

    emojiList.forEach((emoji) => {
        const button = document.createElement("button");

        button.type = "button";
        button.className = "emoji-option";
        button.textContent = emoji;
        button.setAttribute("aria-label", emoji);

        button.addEventListener("click", () => insertEmoji(emoji));

        emojiGrid.appendChild(button);
    });
}

function insertEmoji(emoji) {
    if (!messageInput) return;

    const start = messageInput.selectionStart ?? messageInput.value.length;
    const end = messageInput.selectionEnd ?? messageInput.value.length;

    messageInput.setRangeText(emoji, start, end, "end");
    messageInput.focus();

    if (emojiPicker) {
        emojiPicker.hidden = true;
    }
}

emojiButton?.addEventListener("click", (event) => {
    event.stopPropagation();

    if (emojiPicker) {
        emojiPicker.hidden = !emojiPicker.hidden;
    }
});

document.addEventListener("click", (event) => {
    if (
        emojiPicker &&
        !emojiPicker.contains(event.target) &&
        !emojiButton?.contains(event.target)
    ) {
        emojiPicker.hidden = true;
    }
});

renderEmojiPicker();

attachmentButton?.addEventListener("click", () => {
    fileInput?.click();
});

fileInput?.addEventListener("change", () => {
    clearError();

    const file = fileInput.files?.[0];

    if (!file) return;

    if (!allowedTypes.includes(file.type)) {
        clearSelectedAttachment();
        showError("Unsupported file type. Choose an image or document.");
        return;
    }

    if (file.size > MAX_FILE_SIZE) {
        clearSelectedAttachment();
        showError("The maximum file size is 10 MB.");
        return;
    }

    selectedFile = file;
    showAttachmentPreview(file);
});

removeAttachmentButton?.addEventListener("click", () => {
    clearSelectedAttachment();
    clearError();
});

async function initializeChat() {
    try {
        if (!messageInput || !sendButton) {
            throw new Error("Chat input or send button was not found.");
        }

        if (!attachmentPreview || !attachmentName || !fileInput) {
            throw new Error("Attachment preview elements are missing.");
        }

        const tokenResponse = await fetch(
            `/chat/${encodeURIComponent(roomCode)}/firebase-token`,
            {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json"
                }
            }
        );

        if (!tokenResponse.ok) {
            throw new Error(
                `Laravel authorization failed (${tokenResponse.status}).`
            );
        }

        const tokenData = await tokenResponse.json();

        if (!tokenData.token) {
            throw new Error("Laravel did not return a Firebase token.");
        }

        await signInWithCustomToken(auth, tokenData.token);

        if (!auth.currentUser) {
            throw new Error("Firebase authentication did not complete.");
        }

        startChat();
    } catch (error) {
        showError(
            getFirebaseErrorMessage(error, "Chat initialization failed")
        );
    }
}

function startChat() {
    if (chatStarted) return;

    if (!auth.currentUser) {
        showError("Firebase authentication is required before joining the room.");
        return;
    }

    chatStarted = true;

    const presenceRef = ref(
        database,
        `rooms/${roomCode}/presence/${sessionId}`
    );

    const presenceRoomRef = ref(
        database,
        `rooms/${roomCode}/presence`
    );

    const connectionRef = ref(database, ".info/connected");
    const offsetRef = ref(database, ".info/serverTimeOffset");
    const messagesRef = ref(database, `rooms/${roomCode}/messages`);

    // Synchronize this participant's clock with Firebase server time.
    onValue(
        offsetRef,
        (snapshot) => {
            const offset = snapshot.val();

            if (typeof offset === "number") {
                serverTimeOffset = offset;
                serverTimeOffsetLoaded = true;
            }
        },
        (error) => {
            console.error("Could not synchronize server time:", error);
        }
    );

    // Establish the cutoff before listening for messages.
    // Messages with timestamps older than this cutoff are hidden.
    

    function isMessageFromBeforeJoin(data) {
        if (typeof data.timestamp !== "number") {
            // If Firebase has not resolved the timestamp yet, do not
            // incorrectly classify it as an old message.
            return false;
        }

        if (joinedAt === null) {
            return false;
        }

        const cutoff = serverTimeOffsetLoaded
            ? joinedAt + serverTimeOffset
            : joinedAt;

        return data.timestamp < cutoff;
    }

    async function registerPresence() {
        try {
            await onDisconnect(presenceRef).remove();

            await set(presenceRef, {
                online: true,
                nickname,
                joinedAt: serverTimestamp()
            });

            presenceRegistered = true;
            firebaseReady = true;
            clearError();

            console.log("Firebase presence registered.");
        } catch (error) {
            presenceRegistered = false;
            firebaseReady = false;

            showError(
                getFirebaseErrorMessage(error, "Firebase presence error")
            );
        }
    }

    if (!connectionListenerStarted) {
        connectionListenerStarted = true;

        onValue(
            connectionRef,
            async (snapshot) => {
                if (snapshot.val() !== true) {
                    firebaseReady = false;
                    presenceRegistered = false;
                    console.log("Waiting for Firebase connection...");
                    return;
                }

                await registerPresence();
            },
            (error) => {
                firebaseReady = false;

                showError(
                    getFirebaseErrorMessage(error, "Firebase connection error")
                );
            }
        );
    }

    if (!presenceListenerStarted) {
        presenceListenerStarted = true;

        onValue(
            presenceRoomRef,
            (snapshot) => {
                let onlineCount = 0;

                snapshot.forEach((userSnapshot) => {
                    if (userSnapshot.val()?.online === true) {
                        onlineCount++;
                    }
                });

                const onlineCountElement =
                    document.querySelector(".online-count");

                if (onlineCountElement) {
                    onlineCountElement.replaceChildren();

                    const dot = document.createElement("span");
                    dot.className = "online-dot";

                    onlineCountElement.append(
                        dot,
                        document.createTextNode(` ${onlineCount} Online`)
                    );
                }
            },
            (error) => {
                console.error("Presence count read failed:", error);

                const onlineCountElement =
                    document.querySelector(".online-count");

                if (onlineCountElement) {
                    onlineCountElement.textContent = "Participants unavailable";
                }

                showError(
                    getFirebaseErrorMessage(error, "Presence count error")
                );
            }
        );
    }

    async function uploadAttachment(file) {
        if (!allowedTypes.includes(file.type)) {
            throw new Error("This file type is not allowed.");
        }

        if (file.size > MAX_FILE_SIZE) {
            throw new Error("File size must be 10 MB or less.");
        }

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.getAttribute("content");

        if (!csrfToken) {
            throw new Error("CSRF token is missing from the page layout.");
        }

        const formData = new FormData();
        formData.append("file", file);

        const response = await fetch(
            `/chat/${encodeURIComponent(roomCode)}/attachments`,
            {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json"
                },
                credentials: "same-origin",
                body: formData
            }
        );

        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422 && result.errors) {
                const firstError = Object.values(result.errors).flat()[0];
                throw new Error(firstError || "The attachment is invalid.");
            }

            throw new Error(
                result.message ||
                `Attachment upload failed (${response.status}).`
            );
        }

        if (!result.path || !result.name || !result.type) {
            throw new Error("The server returned incomplete attachment details.");
        }

        return {
            name: result.name,
            type: result.type,
            size: result.size,
            path: result.path,
            storage: "supabase"
        };
    }

    async function sendMessage() {
        const text = messageInput.value.trim();
        const file = selectedFile;

        if ((!text && !file) || sending) return;

        if (!firebaseReady || !auth.currentUser) {
            showError("Connecting to chat. Please check Firebase authentication.");
            return;
        }

        sending = true;
        sendButton.disabled = true;
        clearError();

        try {
            let attachment = null;

            if (file) {
                attachment = await uploadAttachment(file);
            }

            const messageData = {
                sender: nickname,
                senderId: sessionId,
                text,
                timestamp: serverTimestamp()
            };

            if (attachment) {
                messageData.attachment = attachment;
            }

            await push(messagesRef, messageData);

            messageInput.value = "";
            clearSelectedAttachment();
            messageInput.focus();
        } catch (error) {
            showError(getFirebaseErrorMessage(error, "Message send failed"));
        } finally {
            sending = false;
            sendButton.disabled = false;
        }
    }

    sendButton.addEventListener("click", sendMessage);

    messageInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    onChildAdded(
        messagesRef,
        (snapshot) => {
            const data = snapshot.val();

            if (!data) return;

            // Hide messages that were sent before this participant joined.
            if (isMessageFromBeforeJoin(data)) {
                return;
            }

            const messagesContainer =
                document.querySelector(".chat-messages");

            if (!messagesContainer) {
                console.error("Chat messages container was not found.");
                return;
            }

            messagesContainer.querySelector(".chat-empty")?.remove();

            const messageRow = document.createElement("div");
            messageRow.className = "message-row";

            messageRow.classList.add(
                data.senderId === sessionId ? "own-message" : "other-message"
            );

            const messageContent = document.createElement("div");
            messageContent.className = "message-bubble";

            const senderElement = document.createElement("div");
            senderElement.className = "message-sender";
            senderElement.textContent = data.sender || "Guest";
            messageContent.appendChild(senderElement);

            if (data.text) {
                const textElement = document.createElement("div");
                textElement.className = "message-text";
                textElement.textContent = data.text;
                messageContent.appendChild(textElement);
            }

            if (data.attachment) {
                const attachment = data.attachment;
                let downloadUrl = null;

                if (attachment.storage === "supabase" && attachment.path) {
                    downloadUrl =
                        `/chat/${encodeURIComponent(roomCode)}/attachments/download?path=${encodeURIComponent(attachment.path)}`;
                } else if (attachment.url) {
                    downloadUrl = attachment.url;
                }

                if (downloadUrl && attachment.type?.startsWith("image/")) {
                    const image = document.createElement("img");

                    image.src = downloadUrl;
                    image.alt = attachment.name || "Shared image";
                    image.className = "chat-attachment-image";
                    image.loading = "lazy";

                    image.addEventListener("error", () => {
                        const errorText = document.createElement("div");
                        errorText.className = "attachment-load-error";
                        errorText.textContent = "Image could not be loaded.";
                        image.replaceWith(errorText);
                    });

                    messageContent.appendChild(image);
                } else if (downloadUrl) {
                    const link = document.createElement("a");

                    link.href = downloadUrl;
                    link.textContent = attachment.name || "Download attachment";
                    link.target = "_blank";
                    link.rel = "noopener noreferrer";
                    link.className = "chat-attachment-link";

                    messageContent.appendChild(link);
                } else {
                    const errorText = document.createElement("div");
                    errorText.className = "attachment-load-error";
                    errorText.textContent = "Attachment information is missing.";
                    messageContent.appendChild(errorText);
                }
            }

            const timeElement = document.createElement("div");
            timeElement.className = "message-time";

            if (typeof data.timestamp === "number") {
                timeElement.textContent = new Date(
                    data.timestamp
                ).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit"
                });
            }

            messageContent.appendChild(timeElement);
            messageRow.appendChild(messageContent);
            messagesContainer.appendChild(messageRow);

            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        },
        (error) => {
            showError(
                getFirebaseErrorMessage(error, "Could not load room messages")
            );
        }
    );

    window.addEventListener("pagehide", () => {
        if (presenceRegistered && auth.currentUser) {
            remove(presenceRef).catch((error) => {
                console.error("Presence cleanup failed:", error);
            });
        }
    });
}

initializeChat();
