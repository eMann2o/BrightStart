<?php
include_once '../database.php';//include database connection file  

// Start the session at the beginning
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: ../index.html");
    exit();
  }
  


  try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query to fetch data from the table
    $stmt = $db->prepare("SELECT * FROM users"); // Replace 'employees' with your table name
    $stmt->execute();

    // Fetch all data from the query
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging</title>
    <link rel="shortcut icon" href="../logo.png" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>
                .content {
            height: 86vh;
            display: flex;
            flex-direction: column;
        }

        .chat-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #f2f2f7;
            position: relative;
        }

        .chat-header {
            padding: 12px 16px 8px;
            background: #f9f9f9;
            color: #000;
            border-bottom: 0.5px solid #d1d1d6;
            flex-shrink: 0;
            padding-top: env(safe-area-inset-top, 12px);
        }

        .chat-header h2 {
            font-size: 17px;
            font-weight: 600;
            text-align: center;
            color: #000;
            margin: 0;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 8px 16px 16px;
            background: #f2f2f7;
            scroll-behavior: smooth;
        }

        .chat-messages::-webkit-scrollbar {
            display: none;
        }

        .chat-date-group {
            text-align: center;
            margin: 20px auto 12px auto;
            color: #8e8e93;
            font-size: 13px;
            font-weight: 400;
            background: rgba(0, 0, 0, 0.05);
            padding: 4px 12px;
            border-radius: 12px;
            display: inline-block;
            width: auto;
            clear: both;
        }

        .unread-indicator {
            text-align: center;
            margin: 16px auto;
            padding: 6px 16px;
            background: #007aff;
            color: white;
            font-size: 13px;
            font-weight: 500;
            border-radius: 16px;
            display: inline-block;
            width: auto;
            clear: both;
        }

        .message {
            margin-bottom: 8px;
            max-width: 80%;
            clear: both;
            display: block;
        }

        .message.sent {
            float: right;
            margin-left: 20%;
        }

        .message.received {
            float: left;
            margin-right: 20%;
        }

        .message-info {
            font-size: 12px;
            margin-bottom: 4px;
            color: #8e8e93;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            clear: both;
        }

        .message.sent .message-info {
            justify-content: flex-end;
        }

        .message.received .message-info {
            justify-content: flex-start;
        }

        /* Role badges with different colors */
        .role-badge {
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: white;
        }

        .role-badge.teacher {
            background: #ff3b30;
        }

        .role-badge.student {
            background: #007aff;
        }

        .role-badge.ta {
            background: #ff9500;
        }

        .role-badge.admin {
            background: #af52de;
        }

        .role-badge.moderator {
            background: #34c759;
        }

        .role-badge.siso {
            background: #32d74b;
        }

        .role-badge.headteacher {
            background: #5856d6;
        }

        .role-badge.district-director {
            background: #af52de;
        }

        .role-badge.regional-director {
            background: #ff2d92;
        }

        .message-bubble {
            padding: 8px 12px;
            border-radius: 18px;
            word-wrap: break-word;
            line-height: 1.35;
            font-size: 16px;
            position: relative;
            max-width: 100%;
            display: block;
            clear: both;
        }

        .message.sent .message-bubble {
            background: #007aff;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-bubble {
            background: white;
            color: #000;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .message.unread-message .message-bubble {
            background: #fff8e1;
            border-left: 3px solid #ff9500;
        }

        .message.unread-message.sent .message-bubble {
            background: #007aff;
            color: white;
            border-left: none;
        }

        .timestamp {
            font-size: 11px;
            color: #8e8e93;
            margin-top: 4px;
            font-weight: 400;
            clear: both;
            display: block;
        }

        .message.sent .timestamp {
            text-align: right;
        }

        .message.received .timestamp {
            text-align: left;
        }

        .scroll-btn {
            position: absolute;
            bottom: 90px;
            right: 16px;
            width: 36px;
            height: 36px;
            border-radius: 18px;
            background: white;
            color: #007aff;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }

        .scroll-btn:hover {
            background: #f8f8f8;
            transform: scale(1.05);
        }

        .message-input {
            display: flex;
            padding: 8px 16px;
            background: #f9f9f9;
            border-top: 0.5px solid #d1d1d6;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
            padding-bottom: env(safe-area-inset-bottom, 8px);
        }

        #message-input {
            flex: 1;
            padding: 8px 16px;
            border: 1px solid #d1d1d6;
            border-radius: 20px;
            resize: none;
            font-family: inherit;
            font-size: 16px;
            line-height: 1.35;
            max-height: 100px;
            min-height: 36px;
            background: white;
            color: #000;
            transition: all 0.2s ease;
        }

        #message-input:focus {
            outline: none;
            border-color: #007aff;
            box-shadow: 0 0 0 1px #007aff;
        }

        #message-input::placeholder {
            color: #8e8e93;
        }

        #send-button {
            width: 50px;
            height: 50px;
            border-radius: 25px;
            background: #007aff;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        #send-button:hover {
            background: #0056cc;
        }

        #send-button:active {
            transform: scale(0.95);
        }

        /* Message grouping - consecutive messages from same user */
        .message {
            position: relative;
        }

        /* Grouped message styling */
        .message.first-in-group .message-info {
            display: flex;
        }

        .message.middle-in-group .message-info,
        .message.last-in-group .message-info {
            display: none;
        }

        .message.middle-in-group,
        .message.last-in-group {
            margin-top: 2px;
        }

        /* Bubble border radius for grouped messages */
        /* First message in group */
        .message.sent.first-in-group .message-bubble {
            border-bottom-right-radius: 6px;
            border-top-right-radius: 18px;
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .message.received.first-in-group .message-bubble {
            border-bottom-left-radius: 6px;
            border-top-right-radius: 18px;
            border-top-left-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        /* Middle messages in group */
        .message.sent.middle-in-group .message-bubble {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .message.received.middle-in-group .message-bubble {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        /* Last message in group */
        .message.sent.last-in-group .message-bubble {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 4px;
            border-top-left-radius: 18px;
            border-bottom-left-radius: 18px;
        }

        .message.received.last-in-group .message-bubble {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 4px;
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
        }

        /* Single message (not grouped) */
        .message.single-message .message-bubble {
            border-radius: 18px;
        }

        .message.sent.single-message .message-bubble {
            border-bottom-right-radius: 4px;
        }

        .message.received.single-message .message-bubble {
            border-bottom-left-radius: 4px;
        }

        /* Timestamp only shows on last message in group */
        .message.first-in-group .timestamp,
        .message.middle-in-group .timestamp {
            display: none;
        }

        .message.last-in-group .timestamp,
        .message.single-message .timestamp {
            display: block;
        }

        /* Clear floats after each message group */
        .message::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Clear floats in chat container */
        .chat-messages::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Center alignment for date groups and unread indicators */
        .chat-date-group,
        .unread-indicator {
            display: block;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            width: fit-content;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .chat-header {
                padding: 8px 16px 6px;
            }

            .chat-messages {
                padding: 6px 12px 12px;
            }

            .message {
                max-width: 85%;
            }

            .message.sent {
                margin-left: 15%;
            }

            .message.received {
                margin-right: 15%;
            }

            .message-input {
                padding: 6px 12px;
                gap: 6px;
            }

            .scroll-btn {
                bottom: 70px;
                right: 12px;
                width: 32px;
                height: 32px;
            }
        }

        @media (max-width: 480px) {
            .message {
                max-width: 90%;
            }

            .message.sent {
                margin-left: 10%;
            }

            .message.received {
                margin-right: 10%;
            }

            .message-bubble {
                font-size: 15px;
                padding: 7px 11px;
            }
        }

        /* Support for iPhone X and newer */
        @supports (padding-top: env(safe-area-inset-top)) {
            .chat-header {
                padding-top: calc(env(safe-area-inset-top) + 12px);
            }
            
            .message-input {
                padding-bottom: calc(env(safe-area-inset-bottom) + 8px);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Bright<span>Start</span></h2>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item" onclick="window.location.href='dashboard.php';">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" onclick="window.location.href='courses.php';">
                <i class="fas fa-book"></i>
                <span>Courses</span>
            </div>
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
            </div>
            
            <div class="menu-item active" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
            </div>
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </div>
            
            
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-actions">
                <button class="notification-btn" onclick="window.location.href='editpass.php';" title="Edit Password">
                    <i class="fa-solid fa-pencil"></i>
                </button>
                
                <div class="user-profile" onclick="window.location.href='profile.php';">
                    
                    <div class="user-info">
                        <div class="user-name"><?php
                        $name = isset($_SESSION['name']) ? $_SESSION['name'] : "Unknown User";
                        echo htmlspecialchars($name);
                        ?> </div>
                        <div class="user-role"><?php
                        $role = isset($_SESSION['role']) ? $_SESSION['role'] : "Unknown User";
                        echo htmlspecialchars($role);
                        ?> </div>
                    </div>
                    <div class="user-avatar" onclick="window.location.href='logout.php';"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="chat-container">
                <div class="chat-header">
                    <h2>LMS Group Chat</h2>
                </div>

                <div class="chat-messages" id="chat-messages"></div>

                <button id="scroll-to-bottom" class="scroll-btn">⬇ Scroll to Bottom</button>

                <div class="message-input">
                    <textarea id="message-input" placeholder="Type your message..."></textarea>
                    <button id="send-button">Send</button>
                </div>
            </div>
        </section>
        

        <script>
            

            // Sidebar toggle functionality
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('collapsed');
                document.querySelector('.main-content').classList.toggle('expanded');
            });
            

            document.addEventListener('DOMContentLoaded', function () {
                const chatMessages = document.getElementById('chat-messages');
                const messageInput = document.getElementById('message-input');
                const sendButton = document.getElementById('send-button');
                const scrollBtn = document.getElementById('scroll-to-bottom');

                const currentUserEmail = <?= json_encode($_SESSION['email']) ?>;
                let lastReadMessageId = null;
                let unreadCount = 0;
                let hasUnreadMessages = false;
                let isInitialLoad = true;
                let lastMessageCount = 0; // Track message count to detect new messages
                let currentScrollHeight = 0; // Track scroll height for smooth updates

                // Auto-resize textarea
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });

                // Load last read message ID from server
                function getLastReadMessageId() {
                    return fetch('get_last_read.php')
                        .then(response => response.json())
                        .then(data => data.last_read_message_id)
                        .catch(() => null);
                }

                // Save last read message ID to server
                function saveLastReadMessageId(messageId) {
                    fetch('save_last_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ last_read_message_id: messageId })
                    });
                }

                function formatDateGroup(dateStr) {
                    const today = new Date().toDateString();
                    const yesterday = new Date(Date.now() - 86400000).toDateString();
                    if (dateStr === today) return "Today";
                    if (dateStr === yesterday) return "Yesterday";
                    return dateStr;
                }

                function getRoleClass(role) {
                    const roleMap = {
                        'admin': 'admin',
                        'siso': 'siso',
                        'headteacher': 'headteacher',
                        'teacher': 'teacher',
                        'district director': 'district-director',
                        'regional director': 'regional-director'
                    };
                    return roleMap[role.toLowerCase()] || 'teacher';
                }

                function loadMessages() {
                    // Store current state before loading
                    const previousScrollTop = chatMessages.scrollTop;
                    const previousScrollHeight = chatMessages.scrollHeight;
                    const wasNearBottom = previousScrollHeight - previousScrollTop <= chatMessages.clientHeight + 100;
                    
                    fetch('fetch_messages.php')
                        .then(response => response.json())
                        .then(data => {
                            // Check if we have new messages
                            const hasNewMessages = data.length > lastMessageCount;
                            lastMessageCount = data.length;

                            // Only rebuild DOM if this is initial load or there are actual changes
                            if (isInitialLoad || hasNewMessages) {
                                // Store the ID of the message currently at the top of viewport
                                let topVisibleMessageId = null;
                                if (!isInitialLoad) {
                                    const messages = chatMessages.querySelectorAll('.message[data-message-id]');
                                    const chatRect = chatMessages.getBoundingClientRect();
                                    
                                    for (let msg of messages) {
                                        const msgRect = msg.getBoundingClientRect();
                                        if (msgRect.top >= chatRect.top) {
                                            topVisibleMessageId = parseInt(msg.getAttribute('data-message-id'));
                                            break;
                                        }
                                    }
                                }

                                // Temporarily hide scrollbar to prevent flashing
                                chatMessages.style.overflow = 'hidden';
                                
                                chatMessages.innerHTML = '';
                                let lastDate = '';
                                let foundLastRead = false;
                                let newUnreadCount = 0;
                                let lastReadElement = null;
                                let topVisibleElement = null;

                                data.forEach((msg, index) => {
                                    const messageDate = new Date(msg.created_at).toDateString();
                                    if (messageDate !== lastDate) {
                                        lastDate = messageDate;
                                        const dateLabel = document.createElement('div');
                                        dateLabel.classList.add('chat-date-group');
                                        dateLabel.textContent = formatDateGroup(messageDate);
                                        chatMessages.appendChild(dateLabel);
                                    }

                                    const isCurrentUser = msg.sender_email === currentUserEmail;
                                    const isUnread = lastReadMessageId && msg.id > lastReadMessageId && !isCurrentUser;
                                    
                                    const messageElement = addMessageToChat({
                                        id: msg.id,
                                        sender: `${msg.name}`,
                                        role: msg.role,
                                        content: msg.message,
                                        timestamp: msg.created_at,
                                        isCurrentUser: isCurrentUser,
                                        isUnread: isUnread,
                                        senderEmail: msg.sender_email
                                    });

                                    // Track elements for scroll restoration
                                    if (msg.id === lastReadMessageId) {
                                        foundLastRead = true;
                                        lastReadElement = messageElement;
                                    }
                                    
                                    if (msg.id === topVisibleMessageId) {
                                        topVisibleElement = messageElement;
                                    }

                                    if (isUnread) {
                                        newUnreadCount++;
                                    }
                                });

                                // Add unread indicator
                                if (newUnreadCount > 0) {
                                    const unreadIndicator = document.createElement('div');
                                    unreadIndicator.classList.add('unread-indicator');
                                    unreadIndicator.id = 'unread-indicator';
                                    unreadIndicator.textContent = `${newUnreadCount} new message${newUnreadCount > 1 ? 's' : ''}`;
                                    
                                    const firstUnreadMessage = chatMessages.querySelector('.message.unread-message');
                                    if (firstUnreadMessage) {
                                        chatMessages.insertBefore(unreadIndicator, firstUnreadMessage);
                                    }
                                }

                                unreadCount = newUnreadCount;
                                hasUnreadMessages = newUnreadCount > 0;
                                groupMessages();

                                // Restore scrollbar
                                chatMessages.style.overflow = '';

                                // Handle scroll positioning with smooth restoration
                                requestAnimationFrame(() => {
                                    if (isInitialLoad) {
                                        handleInitialScroll(data, lastReadElement, foundLastRead);
                                    } else {
                                        handleScrollRestoration(wasNearBottom, topVisibleElement, hasNewMessages);
                                    }
                                    
                                    updateScrollButton();
                                    isInitialLoad = false;
                                });
                            } else {
                                // No new messages, just update button state
                                updateScrollButton();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading messages:', error);
                            // Restore scrollbar on error
                            chatMessages.style.overflow = '';
                        });
                }

                function handleInitialScroll(data, lastReadElement, foundLastRead) {
                    if (lastReadMessageId === null) {
                        // First visit - mark all messages as read and scroll to bottom
                        if (data.length > 0) {
                            lastReadMessageId = data[data.length - 1].id;
                            saveLastReadMessageId(lastReadMessageId);
                        }
                        scrollToBottom();
                    } else if (lastReadElement && foundLastRead) {
                        // Scroll to last read message smoothly
                        lastReadElement.scrollIntoView({ block: 'center', behavior: 'auto' });
                    } else {
                        // Fallback to bottom
                        scrollToBottom();
                    }
                }

                function handleScrollRestoration(wasNearBottom, topVisibleElement, hasNewMessages) {
                    if (hasNewMessages && wasNearBottom) {
                        // User was at bottom and there are new messages - scroll to bottom
                        scrollToBottom();
                    } else if (topVisibleElement) {
                        // Restore to the same visible message to prevent jumping
                        topVisibleElement.scrollIntoView({ block: 'start', behavior: 'auto' });
                    } else if (wasNearBottom) {
                        // Fallback to bottom if we can't find the reference element
                        scrollToBottom();
                    }
                    // If user wasn't near bottom and we don't have new messages, 
                    // the scroll position should naturally stay the same
                }

                function addMessageToChat(message) {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('message');
                    messageElement.classList.add(message.isCurrentUser ? 'sent' : 'received');
                    messageElement.setAttribute('data-message-id', message.id);
                    messageElement.setAttribute('data-sender', message.senderEmail || 'current-user');
                    
                    if (message.isUnread) {
                        messageElement.classList.add('unread-message');
                    }

                    const messageInfo = document.createElement('div');
                    messageInfo.classList.add('message-info');

                    if (message.isCurrentUser) {
                        messageInfo.textContent = "You";
                    } else {
                        const roleClass = getRoleClass(message.role);
                        messageInfo.innerHTML = `${message.sender} <span class="role-badge ${roleClass}">${message.role}</span>`;
                    }

                    const messageContent = document.createElement('div');
                    messageContent.classList.add('message-bubble');
                    messageContent.textContent = message.content;

                    const timestamp = document.createElement('div');
                    timestamp.classList.add('timestamp');
                    timestamp.textContent = formatTimestamp(message.timestamp);

                    messageElement.appendChild(messageInfo);
                    messageElement.appendChild(messageContent);
                    messageElement.appendChild(timestamp);

                    chatMessages.appendChild(messageElement);
                    return messageElement;
                }

                function formatTimestamp(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                }

                function scrollToBottom() {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }

                function updateScrollButton() {
                    const nearBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 100;
                    
                    if (hasUnreadMessages) {
                        scrollBtn.textContent = `${unreadCount}`;
                        scrollBtn.style.display = 'flex';
                    } else if (!nearBottom) {
                        scrollBtn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
                        scrollBtn.style.display = 'flex';
                    } else {
                        scrollBtn.style.display = 'none';
                    }
                }

                function groupMessages() {
                    const messages = chatMessages.querySelectorAll('.message');
                    
                    messages.forEach((msg, index) => {
                        const currentSender = msg.getAttribute('data-sender') || 'current-user';
                        const isSent = msg.classList.contains('sent');
                        const senderKey = isSent ? 'current-user' : currentSender;
                        
                        // Get previous and next message senders
                        const prevMsg = messages[index - 1];
                        const nextMsg = messages[index + 1];
                        
                        const prevSender = prevMsg ? (prevMsg.classList.contains('sent') ? 'current-user' : prevMsg.getAttribute('data-sender')) : null;
                        const nextSender = nextMsg ? (nextMsg.classList.contains('sent') ? 'current-user' : nextMsg.getAttribute('data-sender')) : null;
                        
                        // Clear all grouping classes
                        msg.classList.remove('single-message', 'first-in-group', 'middle-in-group', 'last-in-group');
                        
                        // Check if message is part of a group
                        const isPartOfPrevGroup = prevSender === senderKey && !msg.classList.contains('unread-message') && !prevMsg?.classList.contains('unread-message');
                        const isPartOfNextGroup = nextSender === senderKey && !msg.classList.contains('unread-message') && !nextMsg?.classList.contains('unread-message');
                        
                        if (isPartOfPrevGroup && isPartOfNextGroup) {
                            // Middle of group
                            msg.classList.add('middle-in-group');
                        } else if (isPartOfPrevGroup && !isPartOfNextGroup) {
                            // Last in group
                            msg.classList.add('last-in-group');
                        } else if (!isPartOfPrevGroup && isPartOfNextGroup) {
                            // First in group
                            msg.classList.add('first-in-group');
                        } else {
                            // Single message
                            msg.classList.add('single-message');
                        }
                    });
                }

                function markMessagesAsRead() {
                    const visibleMessages = Array.from(chatMessages.querySelectorAll('.message[data-message-id]'));
                    const chatRect = chatMessages.getBoundingClientRect();
                    
                    let lastVisibleMessageId = null;
                    
                    visibleMessages.forEach(msgEl => {
                        const msgRect = msgEl.getBoundingClientRect();
                        const isVisible = msgRect.top >= chatRect.top && msgRect.bottom <= chatRect.bottom;
                        
                        if (isVisible) {
                            const messageId = parseInt(msgEl.getAttribute('data-message-id'));
                            if (messageId > (lastVisibleMessageId || 0)) {
                                lastVisibleMessageId = messageId;
                            }
                            msgEl.classList.remove('unread-message');
                        }
                    });

                    if (lastVisibleMessageId && lastVisibleMessageId > (lastReadMessageId || 0)) {
                        lastReadMessageId = lastVisibleMessageId;
                        saveLastReadMessageId(lastReadMessageId);
                        
                        const indicator = document.getElementById('unread-indicator');
                        if (indicator) {
                            indicator.remove();
                        }
                        
                        unreadCount = 0;
                        hasUnreadMessages = false;
                        updateScrollButton();
                        groupMessages();
                    }
                }

                function sendMessage() {
                    const content = messageInput.value.trim();
                    if (!content) return;

                    const formData = new FormData();
                    formData.append('message', content);

                                    fetch('send_message.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            messageInput.value = '';
                            messageInput.style.height = 'auto';
                            
                            hasUnreadMessages = false;
                            unreadCount = 0;
                            
                            // Force immediate reload for sent messages and scroll to bottom
                            setTimeout(() => {
                                loadMessages();
                                // Ensure we scroll to bottom after the message is loaded
                                setTimeout(scrollToBottom, 200);
                            }, 100);
                        }
                    });
                }

                // Event listeners
                sendButton.addEventListener('click', sendMessage);
                
                messageInput.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });

                scrollBtn.addEventListener('click', () => {
                    scrollToBottom();
                    setTimeout(markMessagesAsRead, 100);
                });

                // Optimized scroll handler with debouncing
                let scrollTimeout;
                chatMessages.addEventListener('scroll', () => {
                    updateScrollButton();
                    
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        const nearBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 50;
                        if (nearBottom && hasUnreadMessages) {
                            markMessagesAsRead();
                        }
                    }, 500); // Reduced timeout for better responsiveness
                });

                // Initialize
                getLastReadMessageId().then(id => {
                    lastReadMessageId = id;
                    loadMessages();
                    // Set up polling with longer interval to reduce server load
                    setInterval(loadMessages, 5000); // Increased from 3000ms
                });
            });
        </script>
    </div>
</body>
</html>