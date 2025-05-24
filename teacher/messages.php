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
        /* Simple WhatsApp-inspired Chat Styling */
        .content {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .chat-container {
            width: 100%;
            height: 80vh;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Chat Header */
        .chat-header {
            background: #075e54;
            color: white;
            padding: 20px 24px;
            border-bottom: 1px solid #128c7e;
        }

        .chat-header h2 {
            margin: 0;
            font-size: 19px;
            font-weight: 500;
        }

        /* Chat Messages Container */
        .chat-messages {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            background: #e5ddd5;
        }

        /* Custom Scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        /* Message Styling */
        .message {
            margin-bottom: 8px;
            display: flex;
            flex-direction: column;
            clear: both;
        }

        .message.sent {
            align-items: flex-end;
        }

        .message.received {
            align-items: flex-start;
        }

        /* Message Info */
        .message-info {
            font-size: 12px;
            color: #667781;
            margin-bottom: 4px;
            padding: 0 8px;
            font-weight: 500;
        }

        .message.sent .message-info {
            color: #667781;
        }

        .role-badge {
            background: #128c7e;
            color: white;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 8px;
        }

        /* Message Content */
        .message > div:nth-child(2) {
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 65%;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
            font-size: 14px;
            line-height: 1.4;
        }

        .message.sent > div:nth-child(2) {
            background: #dcf8c6;
            color: #303030;
        }

        /* Message Timestamp */
        .timestamp {
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            text-align: right;
        }

        .message.received .timestamp {
            text-align: left;
        }

        /* Date Group Styling */
        .chat-date-group {
            text-align: center;
            margin: 16px 0 8px 0;
            color: #667781;
            font-size: 12px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 12px;
            border-radius: 8px;
            display: inline-block;
            margin-left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Unread Message Styling */
        .message.unread-message > div:nth-child(2) {
            border-left: 3px solid #25d366;
            background: #f0f9f0;
        }

        .message.sent.unread-message > div:nth-child(2) {
            background: #dcf8c6;
            border-left: 3px solid #128c7e;
        }

        /* Unread Indicator */
        .unread-indicator {
            background: #25d366;
            color: white;
            text-align: center;
            padding: 6px 16px;
            margin: 12px auto;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            max-width: fit-content;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Scroll Button */
        .scroll-btn {
            position: fixed;
            bottom: 100px;
            right: 24px;
            background: #25d366;
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .scroll-btn:hover {
            background: #128c7e;
        }

        /* Message Input Section */
        .message-input {
            padding: 12px 16px;
            background: #f0f2f5;
            border-top: 1px solid #e9edef;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .message-input textarea {
            flex: 1;
            border: 1px solid #d1d7db;
            border-radius: 24px;
            padding: 12px 16px;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            outline: none;
            background: white;
            max-height: 100px;
            min-height: 20px;
            line-height: 1.4;
        }

        .message-input textarea:focus {
            border-color: #128c7e;
        }

        .message-input textarea::placeholder {
            color: #667781;
        }

        .message-input button {
            background: #128c7e;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
        }

        .message-input button:hover {
            background: #075e54;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                padding: 0;
            }
            
            .chat-container {
                height: 80vh;
                border-radius: 0;
                max-width: 100%;
            }
            
            .chat-header {
                padding: 16px 20px;
            }
            
            .chat-messages {
                padding: 8px;
            }
            
            .message > div:nth-child(2) {
                max-width: 80%;
                font-size: 14px;
            }
            
            .message-input {
                padding: 8px 12px;
            }
            
            .scroll-btn {
                bottom: 80px;
                right: 16px;
                width: 44px;
                height: 44px;
            }
        }

        @media (max-width: 480px) {
            .message > div:nth-child(2) {
                max-width: 85%;
                padding: 6px 10px;
                font-size: 13px;
            }
            
            .message-input textarea {
                padding: 10px 14px;
                font-size: 14px;
            }
            
            .message-input button {
                width: 40px;
                height: 40px;
            }
            
            .scroll-btn {
                width: 40px;
                height: 40px;
                font-size: 11px;
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
                <span>Modules</span>
            </div>
            <div class="menu-item" onclick="window.location.href='videoupload.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Upload Files</span>
            </div>
            
            <div class="menu-item active" onclick="window.location.href='messages.php';">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
              
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

                function getRoleFromSender(sender) {
                    const match = sender.match(/\((.*?)\)$/);
                    return match ? match[1] : '';
                }

                function loadMessages() {
                    fetch('fetch_messages.php')
                        .then(response => response.json())
                        .then(data => {
                            chatMessages.innerHTML = '';
                            let lastDate = '';
                            let foundLastRead = false;
                            let newUnreadCount = 0;
                            let lastReadElement = null;

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
                                
                                if (msg.id === lastReadMessageId) {
                                    foundLastRead = true;
                                    lastReadElement = addMessageToChat({
                                        id: msg.id,
                                        sender: `${msg.name} (${msg.role})`,
                                        content: msg.message,
                                        timestamp: msg.created_at,
                                        isCurrentUser: isCurrentUser,
                                        isUnread: false
                                    });
                                } else {
                                    addMessageToChat({
                                        id: msg.id,
                                        sender: `${msg.name} (${msg.role})`,
                                        content: msg.message,
                                        timestamp: msg.created_at,
                                        isCurrentUser: isCurrentUser,
                                        isUnread: isUnread
                                    });
                                }

                                if (isUnread) {
                                    newUnreadCount++;
                                }
                            });

                            // Add unread indicator if there are unread messages
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
                            updateScrollButton();

                            // Position to last read message on first load only
                            if (lastReadMessageId === null) {
                                // First visit - mark all messages as read
                                if (data.length > 0) {
                                    lastReadMessageId = data[data.length - 1].id;
                                    saveLastReadMessageId(lastReadMessageId);
                                }
                            } else if (lastReadElement && !foundLastRead) {
                                // If last read message not found, scroll to last read position
                                if (lastReadElement) {
                                    lastReadElement.scrollIntoView({ block: 'center', behavior: 'instant' });
                                }
                            }
                        });
                }

                function addMessageToChat(message) {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('message');
                    messageElement.classList.add(message.isCurrentUser ? 'sent' : 'received');
                    messageElement.setAttribute('data-message-id', message.id);
                    
                    if (message.isUnread) {
                        messageElement.classList.add('unread-message');
                    }

                    const messageInfo = document.createElement('div');
                    messageInfo.classList.add('message-info');

                    if (message.isCurrentUser) {
                        messageInfo.textContent = "You";
                    } else {
                        const nameOnly = message.sender.replace(/\s\((.*?)\)/, '');
                        const role = getRoleFromSender(message.sender);
                        messageInfo.innerHTML = `${nameOnly} <span class="role-badge">${role}</span>`;
                    }

                    const messageContent = document.createElement('div');
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
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                function scrollToBottom() {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }

                function updateScrollButton() {
                    const nearBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 100;
                    
                    if (unreadCount > 0 && !nearBottom) {
                        scrollBtn.textContent = `${unreadCount}`;
                        scrollBtn.style.display = 'block';
                    } else if (!nearBottom) {
                        scrollBtn.textContent = '↓';
                        scrollBtn.style.display = 'block';
                    } else {
                        scrollBtn.style.display = 'none';
                    }
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
                        updateScrollButton();
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
                            loadMessages();
                            // No auto-scroll after sending
                        }
                    });
                }

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

                chatMessages.addEventListener('scroll', () => {
                    updateScrollButton();
                    clearTimeout(chatMessages.scrollTimeout);
                    chatMessages.scrollTimeout = setTimeout(markMessagesAsRead, 500);
                });

                // Initialize - load messages without any auto-scrolling
                getLastReadMessageId().then(id => {
                    lastReadMessageId = id;
                    setInterval(loadMessages, 3000);
                    loadMessages();
                });
            });
        </script>
    </div>
</body>
</html>