<?php
include_once '../database.php';//include database connection file  

// Start the session at the beginning
session_start();
// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: ../login.html");
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
    <title>Brightstart Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="styles/style.css">
    <style>

        .chat-container {
            width: 100%;
            max-width: 800px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 75vh;
        }

        .chat-header {
            background-color: #4285f4;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .group-members {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #members-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .members-list {
            background-color: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: none;
        }

        .members-list h3 {
            margin-top: 0;
            color: #555;
        }

        .members-list ul {
            list-style-type: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .members-list li {
            padding: 5px 0;
            color: #333;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.sent {
            align-self: flex-end;
            background-color: #4285f4;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.received {
            align-self: flex-start;
            background-color: #f1f1f1;
            color: #333;
            border-bottom-left-radius: 5px;
        }

        .message-info {
            font-size: 0.8rem;
            margin-bottom: 5px;
            opacity: 0.7;
        }

        .message-input {
            display: flex;
            padding: 15px;
            border-top: 1px solid #eee;
            background-color: #f9f9f9;
        }

        #message-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            resize: none;
            font-family: inherit;
            font-size: 1rem;
            min-height: 40px;
            max-height: 120px;
        }

        #send-button {
            margin-left: 10px;
            padding: 0 20px;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        #send-button:hover {
            background-color: #3367d6;
        }

        .timestamp {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 3px;
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
            <div class="menu-item" onclick="window.location.href='users.php';">
                <i class="fa-solid fa-upload"></i>
                <span>Video Upload</span>
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
            
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search courses, students, or content...">
                <i class="fas fa-search search-icon"></i>
            </div>
            
            <div class="header-actions">
                <button class="notification-btn" onclick="window.location.href='editpass.php';" title="Edit Password">
                    <i class="fa-solid fa-pencil"></i>
                </button>
                
                <div class="user-profile">
                    
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
                    <div class="group-members">
                        <span>12 members</span>
                        <button id="members-toggle">👥</button>
                    </div>
                </div>
                
                <div class="members-list" id="members-list">
                    <h3>Group Members</h3>
                    <ul>
                        <li>kodi (You)</li>
                        <li>Miss Roberta</li>
                        <li>Nii Kpani</li>
                        <li>koo Emma</li>
                        <li>Adomski</li>
                        <li>Dora</li>
                    </ul>
                </div>
                
                <div class="chat-messages" id="chat-messages">
                    <!-- Messages will appear here -->
                </div>
                
                <div class="message-input">
                    <textarea id="message-input" placeholder="Type your message here..."></textarea>
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
            


            document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chat-messages');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const membersToggle = document.getElementById('members-toggle');
            const membersList = document.getElementById('members-list');
            
            // Sample messages (in a real app, these would come from a server)
            const sampleMessages = [
                {
                    sender: "Adomski",
                    content: "Hello everyone!?",
                    timestamp: "2023-05-15T10:30:00",
                    isCurrentUser: false
                },
                {
                    sender: "Koo Emma",
                    content: "Hi Adom! at 11:59 PM.",
                    timestamp: "2023-05-15T10:35:00",
                    isCurrentUser: true
                },
                {
                    sender: "Dora",
                    content: "what y'all up to?",
                    timestamp: "2023-05-15T11:15:00",
                    isCurrentUser: false
                }
            ];
            
            // Toggle members list visibility
            membersToggle.addEventListener('click', function() {
                membersList.style.display = membersList.style.display === 'block' ? 'none' : 'block';
            });
            
            // Display sample messages
            sampleMessages.forEach(message => {
                addMessageToChat(message);
            });
            
            // Send message functionality
            function sendMessage() {
                const content = messageInput.value.trim();
                if (content) {
                    const newMessage = {
                        sender: "Alex Johnson", // In a real app, this would be the logged-in user
                        content: content,
                        timestamp: new Date().toISOString(),
                        isCurrentUser: true
                    };
                    
                    addMessageToChat(newMessage);
                    messageInput.value = '';
                    
                    // In a real app, you would send the message to the server here
                    // simulateReceivedMessage();
                }
            }
            
            // Add message to chat UI
            function addMessageToChat(message) {
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');
                messageElement.classList.add(message.isCurrentUser ? 'sent' : 'received');
                
                const messageInfo = document.createElement('div');
                messageInfo.classList.add('message-info');
                messageInfo.textContent = message.isCurrentUser ? 'You' : message.sender;
                
                const messageContent = document.createElement('div');
                messageContent.textContent = message.content;
                
                const timestamp = document.createElement('div');
                timestamp.classList.add('timestamp');
                timestamp.textContent = formatTimestamp(message.timestamp);
                
                messageElement.appendChild(messageInfo);
                messageElement.appendChild(messageContent);
                messageElement.appendChild(timestamp);
                
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Format timestamp for display
            function formatTimestamp(isoString) {
                const date = new Date(isoString);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            
            // Event listeners
            sendButton.addEventListener('click', sendMessage);
            
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Simulate receiving a message after a delay
            function simulateReceivedMessage() {
                setTimeout(() => {
                    const responses = [
                        "Thanks for the info!",
                        "I'll be there for the study group.",
                        "Can someone explain question 3 from the last assignment?",
                        "The lecture slides have been updated."
                    ];
                    
                    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                    
                    const receivedMessage = {
                        sender: sampleMessages[Math.floor(Math.random() * sampleMessages.length)].sender,
                        content: randomResponse,
                        timestamp: new Date().toISOString(),
                        isCurrentUser: false
                    };
                    
                    addMessageToChat(receivedMessage);
                }, 2000);
            }
        });
        </script>
    </div>
</body>
</html>