<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @include('chat.style')
</head>
<body>

    <!-- Floating Button -->
    <div class="chat-float-btn" id="openChat">
        <i class="fas fa-comment-dots"></i>
    </div>

    <!-- Chat Box -->
    <div class="chat-box" id="chatBox">
        <div class="chat-wrapper hide-sidebar">

            <!-- Sidebar -->
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <span>Chat</span>
                </div>
                <div class="chatUserList">
                    <div class="userLoading">
                        @for ($i = 0; $i < 10; $i++)
                            <div class="user-skeleton">
                                <div class="avatar"></div>
                                <div class="lines">
                                    <div class="line short"></div>
                                    <div class="line long"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="chat-content">
                <div class="chat-header">
                    <i class="fas fa-arrow-left mobile-back"></i>
                    <i class="fas fa-times" id="closeChat"></i>
                </div>

                <div class="chat-messages" id="chatMessages"></div>

                <div class="typing" id="typing">
                    <i class="fas fa-spinner fa-spin"></i> mengirim...
                </div>

                <div class="chat-input">
                    <input type="hidden" id="conversationId">
                    <input type="text"
                           id="messageInput"
                           placeholder="Pilih percakapan terlebih dahulu"
                           disabled>
                    <button id="sendMessage" disabled>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>

    @include('chat.js_chat')
</body>
</html>
