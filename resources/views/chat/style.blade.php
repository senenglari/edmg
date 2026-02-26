<style>
    body { font-family: Arial, sans-serif; }

    /* Floating Button */
    .chat-float-btn {
        position: fixed;
        right: 20px;
        bottom: 20px;
        width: 56px;
        height: 56px;
        background: #03ac0e;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,.25);
        z-index: 990;
    }

    .chat-float-btn i {
        font-size: 22px;
    }

    .chat-box {
        position: fixed;
        right: 20px;
        bottom: 0;
        width: 640px;
        height: 580px;
        background: #fff;
        border-radius: 6px 6px 0 0;
        box-shadow: 0 0 9px rgba(0,0,0,0.12);
        overflow: hidden;
        z-index: 1011;
        border: 1px solid #e0e0e0;

        /* animasi */
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        pointer-events: none;
    }

    /* visible state */
    .chat-box.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    /* =========================
       TABLET (<= 1024px)
    ========================= */
    @media (max-width: 1024px) {
        .chat-box {
            width: calc(100% - 40px);
            height: 75vh;
            right: 20px;
            bottom: 10px;
        }
    }

    /* =========================
       MOBILE (<= 768px)
    ========================= */
    @media (max-width: 768px) {
        .chat-box {
            width: 80%;
            height: 70%;
            right: 0;
            bottom: 0;
            border-radius: 0;
            box-shadow: none;
            border: none;
            transform: translateY(100%);
        }

        .chat-box.show {
            transform: translateY(0);
        }
    }

    /* =========================
       SMALL MOBILE (<= 480px)
    ========================= */
    @media (max-width: 480px) {
        .chat-box {
            font-size: 14px;
        }
    }

    .chat-wrapper {
        display: flex;
        height: 100%;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 240px;
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
    }

    .chat-sidebar-header {
        padding: 12px;
        font-weight: bold;
        border-bottom: 1px solid #eee;
        color: black;
        font-size: 16px;
    }

    .chat-search {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .chat-search input {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
    }

    .chat-item {
        padding: 12px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
    }

    .chat-item:hover, .chat-item.active {
        color: black;
        background: #ffcc00;
    }

    /* Chat content */
    .chat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: 15px 12px;
        border-bottom: 1px solid #eee;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        color: black;
    }

    .chat-header i {
        cursor: pointer;
        color: #999;
    }

    .chat-header i:hover {
        color: #333;
    }

    .chat-messages {
        flex: 1;
        padding: 15px;
        background: #fafafa;
        overflow-y: auto;
    }

    .message {
        margin-bottom: 12px;
        max-width: 100%;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .message.me {
        background: #dcf8c6;
        margin-left: auto;
    }

    .message.other {
        background: #fff;
        border: 1px solid #eee;
        text-align: right;
    }

    .typing {
        font-size: 12px;
        color: #888;
        margin: 0 15px 5px;
        display: none;
    }

    .chat-input {
        border-top: 1px solid #eee;
        padding: 10px;
        display: flex;
        gap: 8px;
    }

    .chat-input input {
        flex: 1;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .chat-input button {
        background: #03ac0e;
        color: #fff;
        border: none;
        padding: 0 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .chat-item {
        display: flex;
        gap: 10px;
        padding: 12px;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
        align-items: center;
    }

    .chat-avatar {
        width: 40px;
        height: 40px;
        background: #e8f7ee;
        color: #03ac0e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .chat-info strong {
        display: block;
        font-size: 14px;
    }

    .chat-info small {
        font-size: 12px;
        color: #777;
    }

    /* hidden ke bawah */
    .chat-float-btn.hide {
        opacity: 0;
        transform: translateY(60px);
        pointer-events: none;
    }

    .chat-input input:disabled {
        background: #f3f3f3;
        cursor: not-allowed;
    }

    .chat-input button:disabled {
        background: #a5d6a7;
        cursor: not-allowed;
    }

    .chatUserList {
        overflow-y: auto;
        height: 100%;
        position: relative;
    }

    .loading-center {
        text-align: center;
        padding: 20px;
        color: #666;
        position: relative;
        height: 100%;
    }

    .spinner {
        width: 28px;
        height: 28px;
        border: 3px solid #ddd;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        margin: 0 auto 8px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .user-skeleton {
        display: flex;
        gap: 10px;
        padding: 10px;
        align-items: center;
    }

    .user-skeleton .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e0e0e0;
    }

    .user-skeleton .lines {
        flex: 1;
    }

    .user-skeleton .line {
        height: 10px;
        background: #e0e0e0;
        margin-bottom: 6px;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .user-skeleton .line.short {
        width: 40%;
    }

    .user-skeleton .line.long {
        width: 70%;
    }

    /* shimmer animation */
    .user-skeleton {
        position: relative;
    }
    .user-skeleton .line::after,
    .user-skeleton .avatar::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        height: 100%;
        width: 100%;
        background: linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.5),
                transparent
        );
        animation: shimmer 1.4s infinite;
    }

    @keyframes shimmer {
        100% {
            left: 100%;
        }
    }

    .userLoading {
        position: relative;
    }

    .chat-sidebar-header {
        display: flex;
        align-items: center;
        padding: 12px;
        gap: 10px;
        font-weight: bold;
        border-bottom: 1px solid #eee;
    }

    .chat-reload {
        cursor: pointer;
        font-size: 16px;
        color: #666;
    }

    .chat-reload:hover {
        color: green;
    }

    /* =========================
        MOBILE: HIDE SIDEBAR CHAT
    ========================= */
    @media (max-width: 768px) {

        .chat-wrapper {
            flex-direction: column;
        }

        /* chat content full screen */
        .chat-content {
            width: 100%;
            flex: 1;
        }

        /* header lebih rapih di mobile */
        .chat-header {
            padding: 14px;
            font-size: 15px;
        }

        /* input lebih besar */
        .chat-input input {
            font-size: 14px;
        }
    }

    @media (max-width: 768px) {
        .chat-wrapper.hide-sidebar .chat-sidebar {
            display: none;
        }

        .chat-wrapper {
            display: block;
        }

        .chat-wrapper .chat-sidebar {
            width: 90%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
    }

    .mobile-back {
        display: none;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .mobile-back {
            display: inline-block;
        }
    }



</style>