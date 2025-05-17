// AI Support Chatbot for HatidGawa

class HatidGawaChatbot {
  constructor() {
    this.isOpen = false
    this.messages = []
    this.container = null
    this.chatWindow = null
    this.messagesList = null
    this.inputField = null

    // Initial welcome messages
    this.messages = [
      {
        role: "assistant",
        content: "Hello! I'm your HatidGawa support assistant. How can I help you today?",
      },
      {
        role: "assistant",
        content:
          "You can ask me about how to use the platform, post tasks, find helpers, or any other questions you might have.",
      },
    ]

    // Common questions and responses
    this.commonResponses = {
      "how to post task":
        'To post a task, log in to your account, click on "Post a Task" button, fill in the task details including title, description, budget, and location. Then submit the form to make your task available to potential helpers.',
      "how to apply":
        'To apply for a task, browse the available tasks, click on "View Details" on a task you\'re interested in, and then click the "Apply" button. You can also include a message explaining why you\'re a good fit for the task.',
      "safe zones":
        "Safe Zones are designated meeting places like barangay halls and community centers where you can safely meet with other users to discuss or complete tasks. They provide an added layer of security for both task posters and helpers.",
      payment:
        "HatidGawa currently supports cash payments upon task completion. Always agree on payment terms before starting a task. We recommend using our in-app messaging to document any payment agreements.",
      verification:
        "User verification helps build trust in our community. To get verified, go to your profile settings and submit the required identification documents. Our team will review them and update your status within 24-48 hours.",
      "rating system":
        "After completing a task, both the task poster and helper can rate each other on a scale of 1-5 stars. These ratings help build reputation and trust within the community. Always strive to provide excellent service to maintain a high rating!",
      "contact support":
        "You can contact our support team by emailing support@hatidgawa.com or by using the contact form in the Help Center. We typically respond within 24 hours on business days.",
      "delete account":
        "To delete your account, go to Settings > Privacy > Delete Account. Please note that this action is permanent and will remove all your data from our platform.",
      "change password":
        'To change your password, go to your Profile Settings, click on the Security tab, and select "Change Password". You\'ll need to enter your current password and then your new password twice to confirm.',
      "forgot password":
        'If you forgot your password, click on the "Forgot Password?" link on the login page. We\'ll send you an email with instructions to reset your password.',
    }

    this.init()
  }

  init() {
    // Create chatbot container if it doesn't exist
    if (!document.getElementById("hatidgawa-chatbot")) {
      this.createChatbotUI()
    }

    // Add event listeners
    this.addEventListeners()
  }

  createChatbotUI() {
    // Create main container
    this.container = document.createElement("div")
    this.container.id = "hatidgawa-chatbot"
    this.container.className = "chatbot-container"

    // Create chat button
    const chatButton = document.createElement("button")
    chatButton.className = "chatbot-button"
    chatButton.innerHTML = '<i class="fas fa-comments"></i>'
    chatButton.setAttribute("aria-label", "Open support chat")

    // Create chat window
    this.chatWindow = document.createElement("div")
    this.chatWindow.className = "chatbot-window"

    // Create chat header
    const chatHeader = document.createElement("div")
    chatHeader.className = "chatbot-header"
    chatHeader.innerHTML = `
      <div class="chatbot-title">
        <i class="fas fa-robot"></i>
        <span>HatidGawa Support</span>
      </div>
      <button class="chatbot-close" aria-label="Close chat">
        <i class="fas fa-times"></i>
      </button>
    `

    // Create messages container
    const chatBody = document.createElement("div")
    chatBody.className = "chatbot-body"

    this.messagesList = document.createElement("div")
    this.messagesList.className = "chatbot-messages"

    chatBody.appendChild(this.messagesList)

    // Create input area
    const chatFooter = document.createElement("div")
    chatFooter.className = "chatbot-footer"

    const chatForm = document.createElement("form")
    chatForm.className = "chatbot-form"

    this.inputField = document.createElement("input")
    this.inputField.type = "text"
    this.inputField.className = "chatbot-input"
    this.inputField.placeholder = "Type your question here..."
    this.inputField.setAttribute("aria-label", "Type your message")

    const sendButton = document.createElement("button")
    sendButton.type = "submit"
    sendButton.className = "chatbot-send"
    sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>'
    sendButton.setAttribute("aria-label", "Send message")

    chatForm.appendChild(this.inputField)
    chatForm.appendChild(sendButton)
    chatFooter.appendChild(chatForm)

    // Assemble chat window
    this.chatWindow.appendChild(chatHeader)
    this.chatWindow.appendChild(chatBody)
    this.chatWindow.appendChild(chatFooter)

    // Add everything to the container
    this.container.appendChild(chatButton)
    this.container.appendChild(this.chatWindow)

    // Add to document
    document.body.appendChild(this.container)

    // Add CSS styles
    this.addStyles()
  }

  addStyles() {
    // Check if styles already exist
    if (document.getElementById("hatidgawa-chatbot-styles")) {
      return
    }

    const styleEl = document.createElement("style")
    styleEl.id = "hatidgawa-chatbot-styles"
    styleEl.textContent = `
      .chatbot-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: 'Inter', sans-serif;
      }
      
      .chatbot-button {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background-color: var(--primary);
        color: white;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
      }
      
      .chatbot-button:hover {
        background-color: var(--primary-dark);
        transform: scale(1.05);
      }
      
      .chatbot-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 350px;
        height: 500px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s ease;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
      }
      
      .chatbot-container.open .chatbot-window {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }
      
      .chatbot-header {
        padding: 15px;
        background-color: var(--primary);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      
      .chatbot-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
      }
      
      .chatbot-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 16px;
      }
      
      .chatbot-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
      }
      
      .chatbot-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }
      
      .chatbot-message {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 15px;
        margin-bottom: 5px;
        word-break: break-word;
      }
      
      .chatbot-message.assistant {
        background-color: var(--primary-light);
        color: var(--dark);
        align-self: flex-start;
        border-bottom-left-radius: 5px;
      }
      
      .chatbot-message.user {
        background-color: var(--primary);
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 5px;
      }
      
      .chatbot-footer {
        padding: 15px;
        border-top: 1px solid var(--border);
      }
      
      .chatbot-form {
        display: flex;
        gap: 10px;
      }
      
      .chatbot-input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 20px;
        outline: none;
      }
      
      .chatbot-input:focus {
        border-color: var(--primary);
      }
      
      .chatbot-send {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .chatbot-send:hover {
        background-color: var(--primary-dark);
      }
      
      .chatbot-typing {
        display: flex;
        gap: 5px;
        padding: 10px 15px;
        background-color: var(--primary-light);
        border-radius: 15px;
        align-self: flex-start;
        margin-bottom: 5px;
        width: fit-content;
      }
      
      .chatbot-typing span {
        width: 8px;
        height: 8px;
        background-color: var(--primary);
        border-radius: 50%;
        display: inline-block;
        animation: typing 1.4s infinite both;
      }
      
      .chatbot-typing span:nth-child(2) {
        animation-delay: 0.2s;
      }
      
      .chatbot-typing span:nth-child(3) {
        animation-delay: 0.4s;
      }
      
      @keyframes typing {
        0% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0); }
      }
      
      @media (max-width: 480px) {
        .chatbot-window {
          width: 300px;
          height: 450px;
          bottom: 70px;
          right: 0;
        }
      }
    `

    document.head.appendChild(styleEl)
  }

  addEventListeners() {
    // Toggle chat window
    const chatButton = this.container.querySelector(".chatbot-button")
    chatButton.addEventListener("click", () => this.toggleChat())

    // Close chat window
    const closeButton = this.container.querySelector(".chatbot-close")
    closeButton.addEventListener("click", () => this.toggleChat(false))

    // Send message
    const chatForm = this.container.querySelector(".chatbot-form")
    chatForm.addEventListener("submit", (e) => {
      e.preventDefault()
      this.sendMessage()
    })
  }

  toggleChat(forceState = null) {
    this.isOpen = forceState !== null ? forceState : !this.isOpen

    if (this.isOpen) {
      this.container.classList.add("open")
      this.renderMessages()
      this.inputField.focus()
    } else {
      this.container.classList.remove("open")
    }
  }

  renderMessages() {
    this.messagesList.innerHTML = ""

    this.messages.forEach((message) => {
      const messageEl = document.createElement("div")
      messageEl.className = `chatbot-message ${message.role}`
      messageEl.textContent = message.content
      this.messagesList.appendChild(messageEl)
    })

    // Scroll to bottom
    this.scrollToBottom()
  }

  scrollToBottom() {
    const chatBody = this.container.querySelector(".chatbot-body")
    chatBody.scrollTop = chatBody.scrollHeight
  }

  sendMessage() {
    const message = this.inputField.value.trim()

    if (!message) return

    // Add user message
    this.messages.push({
      role: "user",
      content: message,
    })

    // Clear input
    this.inputField.value = ""

    // Render messages
    this.renderMessages()

    // Show typing indicator
    this.showTypingIndicator()

    // Process message and get response
    setTimeout(() => {
      this.processMessage(message)
    }, 1000)
  }

  showTypingIndicator() {
    const typingIndicator = document.createElement("div")
    typingIndicator.className = "chatbot-typing"
    typingIndicator.innerHTML = "<span></span><span></span><span></span>"
    this.messagesList.appendChild(typingIndicator)
    this.scrollToBottom()
  }

  hideTypingIndicator() {
    const typingIndicator = this.messagesList.querySelector(".chatbot-typing")
    if (typingIndicator) {
      typingIndicator.remove()
    }
  }

  processMessage(message) {
    // Hide typing indicator
    this.hideTypingIndicator()

    // Check for common questions
    const response = this.getResponse(message)

    // Add assistant response
    this.messages.push({
      role: "assistant",
      content: response,
    })

    // Render messages
    this.renderMessages()
  }

  getResponse(message) {
    // Convert message to lowercase for easier matching
    const lowerMessage = message.toLowerCase()

    // Check for greetings
    if (this.containsAny(lowerMessage, ["hello", "hi", "hey", "greetings", "good day"])) {
      return "Hello! How can I assist you with HatidGawa today?"
    }

    // Check for thanks
    if (this.containsAny(lowerMessage, ["thank", "thanks", "appreciate", "helpful"])) {
      return "You're welcome! Is there anything else I can help you with?"
    }

    // Check for goodbyes
    if (this.containsAny(lowerMessage, ["bye", "goodbye", "see you", "talk later"])) {
      return "Goodbye! Feel free to chat again if you have more questions."
    }

    // Check for common questions
    for (const [key, value] of Object.entries(this.commonResponses)) {
      if (lowerMessage.includes(key)) {
        return value
      }
    }

    // Check for specific keywords
    if (lowerMessage.includes("register") || lowerMessage.includes("sign up")) {
      return 'To register, click the "Sign Up" button at the top of the page. Fill in your details including name, email, phone number, and create a password. Once registered, you can start using HatidGawa right away!'
    }

    if (lowerMessage.includes("login") || lowerMessage.includes("log in") || lowerMessage.includes("signin")) {
      return 'To log in, click the "Log In" button at the top of the page. Enter your registered email and password. If you\'ve forgotten your password, you can click the "Forgot Password?" link to reset it.'
    }

    if (lowerMessage.includes("task")) {
      if (lowerMessage.includes("post") || lowerMessage.includes("create")) {
        return 'To post a task, log in to your account, click on "Post a Task" button, fill in the task details including title, description, budget, and location. Then submit the form to make your task available to potential helpers.'
      }
      if (lowerMessage.includes("find") || lowerMessage.includes("search")) {
        return 'To find tasks, go to the "Tasks" page where you can browse all available tasks. You can filter tasks by category, location, and price range to find ones that match your skills and preferences.'
      }
    }

    // Default response
    return "I'm not sure I understand your question. Could you rephrase it or ask about specific features like posting tasks, finding helpers, safe zones, or user verification?"
  }

  containsAny(str, keywords) {
    return keywords.some((keyword) => str.includes(keyword))
  }
}

// Initialize chatbot when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Wait a bit to ensure the page is fully loaded
  setTimeout(() => {
    window.hatidGawaChatbot = new HatidGawaChatbot()
  }, 1000)
})
