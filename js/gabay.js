// gabay_v2.js - Enhanced Level 2 Gabay Chatbot: full original responses merged, localStorage, survey, and accessibility

(() => {
  // DOM Elements
  const chatbotToggle = document.getElementById("chatbot-toggle");
  const chatbotContainer = document.getElementById("chatbot-container");
  const chatbotClose = document.getElementById("chatbot-close");
  const chatbotClear = document.getElementById("chatbot-clear");
  const chatbotSend = document.getElementById("chatbot-send");
  const chatbotInput = document.getElementById("chatbot-input");
  const chatbotMessages = document.getElementById("chatbot-messages");
  const typingIndicator = document.getElementById("typing-indicator");
  const chatbotAttach = document.getElementById("chatbot-attach");
  const quickTopics = document.getElementById("quick-topics");
  const quickTopicButtons = document.querySelectorAll(".quick-topic-button");

  // State
  let isOpen = false;
  let messageHistory = [];
  let showingQuickTopics = true;
  let fallbackCount = 0;
    let awaitingWeatherLocation = false;
  let awaitingWeatherConfirm = false;
  let pendingWeatherLocation = "";
  let userName = localStorage.getItem('gabay_userName') || null;
  const MESSAGE_LIMIT_FOR_SURVEY = 6;
  let awaitingTeachingQuestion = false;
  let awaitingTeachingAnswer = false;
  let teachingQuestion = "";
  let awaitingTeachingKeywords = false;
let suggestedKeywords = [];
let teachingAnswer = "";

// helper (very simple example; feel free to swap with a smarter one)
function extractKeywords(text) {
  // pick up words longer than 4 letters, dedupe
  return Array.from(
    new Set(
      text
        .toLowerCase()
        .match(/\b\w{5,}\b/g) || []
    )
  ).slice(0, 5);
}
// 4) Capture the answer, generate & suggest keywords
if (awaitingTeachingAnswer) {
  teachingAnswer = raw;
  // generate suggestions
  suggestedKeywords = extractKeywords(raw);
  awaitingTeachingAnswer = false;
  awaitingTeachingKeywords = true;
  return `The keywords I suggest are (${suggestedKeywords.join(', ')}).\nPlease type 'confirm' to accept them, or enter your own comma-separated keywords.`;
}

// 5) Capture keyword confirmation/custom keywords, then persist
if (awaitingTeachingKeywords) {
  let finalKeywords;
  if (/^confirm$/i.test(raw)) {
    finalKeywords = suggestedKeywords;
  } else {
    finalKeywords = raw.split(/\s*,\s*/).map(k => k.trim()).filter(Boolean);
  }
  // store as an object with answer + keywords
  gabayKB[teachingQuestion] = {
    answer: teachingAnswer,
    keywords: finalKeywords
  };
  localStorage.setItem('gabay_kb', JSON.stringify(gabayKB));
  awaitingTeachingKeywords = false;
  return "Salamat! Natuto na ako ng bagong kaalaman…";
}
  // Load knowledge base
  let gabayKB = JSON.parse(localStorage.getItem('gabay_kb') || '{}');

  function getLocalAIResponse(userInput) {
    const input = userInput.trim();

    // 1. Check if input matches an existing KB entry
    if (gabayKB[input]) {
      return gabayKB[input];
    }

    // 2. Teaching trigger: start teaching mode
    if (/^(teach|turuan kita)/i.test(input) && !awaitingTeachingQuestion && !awaitingTeachingAnswer) {
      awaitingTeachingQuestion = true;
      return "Okay! Ituro sa akin ang tanong na gusto mong idagdag.";
    }

    // 3. Capture the question to teach
    if (awaitingTeachingQuestion) {
      teachingQuestion = input;
      awaitingTeachingQuestion = false;
      awaitingTeachingAnswer = true;
      return `Ano ang tamang sagot sa "${teachingQuestion}"?`;
    }

    // 4. Capture the answer and store in KB
    if (awaitingTeachingAnswer) {
      gabayKB[teachingQuestion] = input;
      localStorage.setItem('gabay_kb', JSON.stringify(gabayKB));
      awaitingTeachingAnswer = false;
      teachingQuestion = "";
      return "Salamat! Natuto na ako ng bagong kaalaman!";
    }
  }
 
  // Load stored history or welcome
  function loadHistory() {
    const stored = localStorage.getItem('gabay_messageHistory');
    if (stored) {
      try {
        messageHistory = JSON.parse(stored);
        messageHistory.forEach(msg => renderMessage(msg.text, msg.isUser, msg.timestamp));
        if (messageHistory.length > 0) hideQuickTopics();
      } catch (e) {
        console.warn('History parse error', e);
        clearConversation();
      }
    } else {
      clearConversation();
    }
  }

  function saveHistory() {
    localStorage.setItem('gabay_messageHistory', JSON.stringify(messageHistory));
    if (userName) localStorage.setItem('gabay_userName', userName);
  }

  // Helpers
  function getCurrentTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function scrollToBottom() {
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
  }

  function showTypingIndicator() {
    typingIndicator.classList.remove('hidden');
    scrollToBottom();
  }

  function hideTypingIndicator() {
    typingIndicator.classList.add('hidden');
  }

  function toggleChatbot() {
    isOpen = !isOpen;
    if (isOpen) {
      chatbotContainer.classList.remove('hidden');
      setTimeout(() => { chatbotContainer.classList.add('visible'); chatbotInput.focus(); scrollToBottom(); }, 50);
    } else {
      chatbotContainer.classList.remove('visible');
      setTimeout(() => { chatbotContainer.classList.add('hidden'); }, 300);
    }
  }

  function hideQuickTopics() {
    if (showingQuickTopics) {
      quickTopics.classList.add('hidden');
      showingQuickTopics = false;
    }
  }

  function showQuickTopics() {
    if (!showingQuickTopics) {
      quickTopics.classList.remove('hidden');
      showingQuickTopics = true;
    }
  }

  function clearConversation() {
    chatbotMessages.innerHTML = `<div class="chat-day-divider"><span>Today</span></div>`;
    messageHistory = [];
    saveHistory();
    showQuickTopics();
    addBotMessage(`Kumusta! 👋 Ako si Gabay, nandito ako para tumulong. Anong maitutulong ko sa'yong ngayon?`);
  }

  function renderMessage(text, isUser, timestamp) {
    const time = new Date(timestamp || Date.now());
    const container = document.createElement('div'); container.className = 'message-container';
    const msgEl = document.createElement('div');
    msgEl.className = `chatbot-message ${isUser ? 'chatbot-message-sent' : 'chatbot-message-received'}`;
    msgEl.setAttribute('aria-label', isUser ? 'Your message' : 'Bot message');
    const content = document.createElement('div'); content.className = 'message-content'; content.textContent = text;
    const timeEl = document.createElement('div'); timeEl.className = 'message-time'; timeEl.textContent = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    msgEl.appendChild(content); msgEl.appendChild(timeEl);
    container.appendChild(msgEl);
    chatbotMessages.appendChild(container);
    scrollToBottom();
  }

  function addMessage(text, isUser = false) {
    const timestamp = new Date().toISOString();
    messageHistory.push({ text, isUser, timestamp });
    saveHistory();
    renderMessage(text, isUser, timestamp);
    if (messageHistory.filter(m => m.isUser).length === MESSAGE_LIMIT_FOR_SURVEY) showSurvey();
    hideQuickTopics();
  }

  function addUserMessage(text) { addMessage(text, true); }
  function addBotMessage(text) { addMessage(text, false); }

  function sendMessage() {
    const text = chatbotInput.value.trim(); if (!text) return;
    addUserMessage(text);
    chatbotInput.value = '';
    showTypingIndicator();
    setTimeout(() => { hideTypingIndicator(); addBotMessage(getLocalAIResponse(text)); }, Math.random() * 800 + 800);
  }

  // Feedback survey
  function showSurvey() {
  if (document.getElementById('survey-container')) return;
  
  const survey = document.createElement('div');
  survey.id = 'survey-container';
  survey.className = 'survey';
  survey.innerHTML = `
    <div class="survey-text">Natulungan ba kita nang maayos? 😊</div>
    <div class="survey-buttons">
      <button id="survey-yes">Oo</button>
      <button id="survey-no">Hindi</button>
    </div>
  `;
  chatbotMessages.appendChild(survey);
  
  document.getElementById('survey-yes').onclick = () => {
    survey.remove();
    showFeedbackPopup(true);
  };
  
  document.getElementById('survey-no').onclick = () => {
    survey.remove();
    showFeedbackPopup(false);
  };
  
  scrollToBottom();
}

function showFeedbackPopup(isHappy) {
  const popup = document.createElement('div');
  popup.className = 'feedback-popup';
  popup.innerHTML = `
    <div class="emoji ${isHappy ? 'happy-emoji' : 'sad-emoji'}">
      ${isHappy ? '😊' : '😢'}
    </div>
    <div class="feedback-message">
      ${isHappy ? 'Yay! Masaya ako na nakatulong ako sayo!' : 'Awww... Pasensya na! Gagalingan ko pa.'}
    </div>
  `;
  chatbotContainer.appendChild(popup);
  
  setTimeout(() => {
    popup.remove();
  }, 2500);
}

  // Quick topics
  function handleQuickTopic(topic) {
    const mappings = {
      track: ['Paano ko ma-track ang transaction ko?', 'Para ma-track ang transaction mo, pumunta sa Dashboard > My Tasks at i-refresh kung kailangan. 😊'],
      "how-to-use": ['Paano gamitin ang website?',
         `Para gamitin ang HatidGawa website:
1. Mag-sign up o mag-login.
2. I-explore ang “Browse Tasks” para makita ang available na trabaho.
3. Mag-post sa “Create Task” para maghanap ng helpers—kung wala sa “Browse Tasks” ang hinahanap mo, i-post ang detalye ng task, hintayin ang mag-apply, at i-review/approve ang profile ng pipiliin mong helper.
4. I-monitor sa “My Dashboard” ang status ng iyong task (Pending → Accepted → Completed) at mga chat notifications.
5. Gamitin ang Menu para sa Settings, Support, at Emergency Button.`
      ],
      "how-to-order": ['Paano mag-order sa HatidGawa?',
        `Madali lang mag-order:
1. Mag-login
2. Browse Tasks at mag-apply o mag-post ng Task
3. Hintayin ang confirmation at gamitin ang Magic Word
4. I-monitor ang progress sa My Dashboard`
      ],
      contact: ['Paano makipag-ugnayan sa support?', 'Email: support@hatidgawa.com\nHotline: (02) 8123-4567\nMobile: 0917-123-4567'],
      issue: ['May problema ako sa order ko.', 'Pasensya na sa abala! Ibigay ang order number, detalye ng problema, at petsa nangyari.'],
    };
    const [q, a] = mappings[topic] || ['Kailangan ko ng tulong.', 'Ano pong tulong ang kailangan mo?'];
    addUserMessage(q);
    showTypingIndicator();
    setTimeout(() => { hideTypingIndicator(); addBotMessage(a); }, Math.random() * 800 + 800);
  }
  
  
  // Full original AI response logic with enhancements
  function getLocalAIResponse(userInput) {
    const raw = userInput.trim();
const input = raw.toLowerCase();

// 1. LIST command: show all taught Q&A
    if (/^(show list memory|show gabay knowledge)$/i.test(raw)) {
      const entries = Object.entries(gabayKB);
      if (entries.length === 0) {
        return "Wala pa akong natutunang tanong. Gamitin ang 'teach' para magdagdag ng bagong kaalaman.";
      }
      let msg = "Mga natutunang tanong ko:\n";
      entries.forEach(([q, a], i) => {
        msg += `${i+1}. ${q} → ${a}\n`;
      });
      return msg.trim();
    }

    // 2. FORGET command: remove a specific Q&A
    const forgetMatch = raw.match(/^forget\s+(.+)/i);
    if (forgetMatch) {
      const q = forgetMatch[1];
      if (gabayKB[q]) {
        delete gabayKB[q];
        localStorage.setItem('gabay_kb', JSON.stringify(gabayKB));
        return `Ok, nakalimutan ko na ang sagot para sa "${q}".`;
      } else {
        return `Hindi ko nakita ang tanong na "${q}" sa memory ko.`;
      }
    }

// ─── TEACHING MODE ──────────────────────────────────────────
// 1) If we’ve already got this in KB, use it:
if (gabayKB[raw]) {
  return gabayKB[raw];
}
// 2) Kick off teaching on “teach” or “turuan kita”
if (/(teach|turuan kita)$/i.test(raw) && !awaitingTeachingQuestion && !awaitingTeachingAnswer) {
  awaitingTeachingQuestion = true;
  return "Okay! Ituro sa akin ang tanong na gusto mong idagdag.";
}
// 3) Capture the question
if (awaitingTeachingQuestion) {
  teachingQuestion = raw;
  awaitingTeachingQuestion = false;
  awaitingTeachingAnswer = true;
  return `Ano ang tamang sagot sa "${teachingQuestion}"?`;
}
// 4) Capture the answer, persist, and confirm
if (awaitingTeachingAnswer) {
  gabayKB[teachingQuestion] = raw;
  localStorage.setItem('gabay_kb', JSON.stringify(gabayKB));
  awaitingTeachingAnswer = false;
  teachingQuestion = "";
  return "Salamat! Natuto na ako ng bagong kaalaman!";
}
// ─────────────────────────────────────────────────────────────

// ─── ORIGINAL LOGIC (UNCHANGED) ────────────────────────────
const replies = [];
let detectedName = null;

// --- Insert this logic into your message handler BEFORE calling getLocalAIResponse() ---
const weatherTrigger = /\b(weather|panahon|tignan panahon|check weather|ano ang panahon)\b/i;

if (weatherTrigger.test(input) && !awaitingWeatherLocation && !awaitingWeatherConfirm) {
  // User asked for weather but no location yet
  awaitingWeatherLocation = true;
  addBotMessage("Saan mo gustong malaman ang panahon? Paki-specify ng lokasyon (e.g., Manila, Cebu).");
  return;
}

if (awaitingWeatherLocation) {
  // Treat this input as the location
  pendingWeatherLocation = input.trim();
  awaitingWeatherLocation = false;
  awaitingWeatherConfirm = true;
  addBotMessage(`Gusto mo bang makita ang current weather sa **${pendingWeatherLocation}**? (Oo/Hindi)`);
  return;
}

if (awaitingWeatherConfirm) {
  if (/\b(oo|yes)\b/i.test(input)) {
    // User confirmed — fetch the weather
    awaitingWeatherConfirm = false;
    fetchAndSendWeather(pendingWeatherLocation);
  } else {
    // User declined
    awaitingWeatherConfirm = false;
    pendingWeatherLocation = "";
    addBotMessage("Sige, hindi ko na ipro-proceed. Sabihin mo lang kung kailangan mo ulit ng weather info.");
  }
  return;
}

// --- Helper: call a real weather API and send result ---
async function fetchAndSendWeather(location) {
  try {
    const apiKey = "587e976f2b044a595c1700f52712b9c7";
    const res = await fetch(
      `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(location)}&units=metric&appid=${apiKey}`
    );
    const data = await res.json();
    if (data.cod === 200) {
      const desc = data.weather[0].description;
      const temp = data.main.temp.toFixed(1);
      const feels = data.main.feels_like.toFixed(1);
      const humidity = data.main.humidity;
      addBotMessage(
        `Sa ${location} ngayon: ${desc}, temperatura ${temp}°C (feels like ${feels}°C), humidity ${humidity}%.`
      );
    } else {
      addBotMessage(`Pasensya, hindi ko mahanap ang weather para sa “${location}”.`);
    }
  } catch (err) {
    console.error(err);
    addBotMessage("May problema sa pagkuha ng weather data. Paki-subukan muli mamaya.");
  }
}

    // Name introduction
    const introMatch = input.match(/\b(?:ako nga pala si|ako si)\s+([A-Za-z]+)/i);
    if (introMatch) {
      userName = introMatch[1].charAt(0).toUpperCase() + introMatch[1].slice(1);
      detectedName = userName;
      replies.push(`Nice to meet you, ${userName}!`);
    }

    // Repeat request
    const isRepeat = /\b(ano ulit|paki ulit|paki ulitin|ulit|di ko maintindihan|hindi ko maintindihan)\b/.test(input);
    // Polite request
    const isPolite = /\b(can you|pwede mo bang|pwede mo|paki|please)\b/.test(input);

    // Greetings
    if (/\b(hello|hi|kumusta|uy)\b/.test(input)) {
      const greetings = [
        `Uy, kumusta ka diyan${detectedName? ' ' + detectedName: ''}? Ano'ng maitutulong ko ngayon?`,
        'Hi! Kumusta? Nandito ako para tumulong sa’yo.',
        'Kamusta! Paano kita matutulungan ngayon?',
        'Hello! Mabilis lang akong tumugon, ano ang kailangan mo?',
        'Hey there! Anong balita at paano kita matutulungan?'
      ];
      replies.push(greetings[Math.floor(Math.random() * greetings.length)]);
    }

    // Bot identity
    if (/\b(pangalan mo|sino ka|your name|what is this ai name)\b/.test(input)) {
      const identities = [
        'Ako si Gabay—AI support chatbot ng HatidGawa. Pwede mo rin akong tawaging Gab!',
        'Gab ang pangalan ko, kaagapay mo sa lahat ng tasks mo dito.',
        'Gabay ang AI assistant ng HatidGawa. Ready akong mag-guide anytime!',
        'Tawagin mo akong Gabay, iyong ka-partner sa HatidGawa support.'
      ];
      replies.push(identities[Math.floor(Math.random() * identities.length)]);
    }

// Getting Started
if (/\b(sign\s?up|register|get\s?started|how to join|paano mag-umpisa|saan magsisimula)\b/i.test(input)) {
  const gettingStarted = [
    "Para makapag-umpisa sa HatidGawa: 1️⃣ Mag-click ng ‘Sign Up’, 2️⃣ Ilagay ang iyong pangalan, zone, at contact, 3️⃣ I-verify ang code sa email o SMS. Tapos, ready ka nang mag-browse o mag-post ng tasks.",
    "HatidGawa helps you find or post local tasks. Mag-sign up gamit ang email/mobile mo, kumpirmahin ang iyong account, at maaari ka nang maghanap o mag-post ng errands.",
    "Mag-register sa pamamagitan ng ‘Sign Up’ form, tapos i-verify ang iyong email/SMS code. Pagkatapos, makikita mo agad ang ‘Browse Tasks’ at ‘Create Task’.",
    "Upang magsimula: Signup > Email/SMS verification > Explore Tasks > Create o Apply. Simpleng proseso lang!",
    "I-click ang ‘Sign Up’, punan ang form, i-verify ang code, at makikita mo na ang Dashboard kung saan ka puwedeng mag-post o mag-apply."
  ];
  replies.push(gettingStarted[Math.floor(Math.random() * gettingStarted.length)]);
}

// Forgot Password
if (/\b(forgot\s?password|nakalimutan\s?password|reset\s?password|reset link|resend password)\b/i.test(input)) {
  const forgotPassword = [
    "Kung nakalimutan mo ang password, pindutin ang ‘Forgot Password’, ilagay ang registered email mo, at magpapadala kami ng reset link agad.",
    "Upang i-reset ang password: click ‘Forgot Password’, input ang email, at sundin ang instructions sa email na matatanggap mo.",
    "Walang problema! Request mo lang ang reset link sa login page, at papadalhan ka namin ng instructions sa email mo.",
    "Pindutin ang ‘Reset Password’ sa login screen, ilagay ang iyong email, at i-check ang inbox o spam folder para sa link.",
    "Hindi mo makita ang reset email? Sabihin mo lang, rere-resend natin ang link para makapag-login ka ulit."
  ];
  replies.push(forgotPassword[Math.floor(Math.random() * forgotPassword.length)]);
}

// Update Profile
if (/\b(update\s?profile|i-update ang profile|change profile|palitan larawan|edit profile|settings)\b/i.test(input)) {
  const updateProfile = [
    "Para i-update ang profile: puntahan ang ‘My Profile’, i-click ang ‘Edit Profile’, palitan ang nais mong fields, at i-save ang changes.",
    "Pwede mong baguhin ang iyong profile photo, skill tags, at zone sa Settings > Profile. Pagkatapos i-edit, huwag kalimutang pindutin ang ‘Save’.",
    "Go to Account > Profile, tap the pencil icon, gawin ang edits (larawan, pangalan, skills), at i-save para ma-update kaagad.",
    "Step 1: My Profile > Step 2: Edit > Step 3: Adjust info > Step 4: Save. Simple lang!",
    "Need help? Sabihin mo kung anong part ng profile ang gusto mong palitan at gagabayan kita."
  ];
  replies.push(updateProfile[Math.floor(Math.random() * updateProfile.length)]);
}

// Barangay Verification
if (/\b(verified\s?badge|verification|barangay\s?verification|paano mag-verify|ID\s?check)\b/i.test(input)) {
  const barangayVerification = [
    "Para maging verified: magpunta sa barangay office, magdala ng valid government ID (e.g., passport, driver’s license), at kumpletuhin ang verification form. Makakatanggap ka ng badge pagkatapos ma-approve.",
    "Verification gives you a Verified Badge at priority notifications. Dalhin lang ang iyong ID at registration slip sa barangay office para ma-activate.",
    "Dalhin ang valid ID sa barangay office, i-fill out ang application form, at hintayin ang confirmation SMS/email. Verified ka na within 1–2 araw.",
    "Bring a proof of address and valid ID to the barangay hall, tapos ipa-verify ang account mo para makakuha ng badge.",
    "Need clarification? Contact your barangay admin o email support@hatidgawa.com para sa detalye."
  ];
  replies.push(barangayVerification[Math.floor(Math.random() * barangayVerification.length)]);
}

// Post Task
if (/\b(post\s?task|mag-?post|how to post task|paano mag-post|create\s?task)\b/i.test(input)) {
  const postTask = [
    "Para mag-post ng task: 1️⃣ Click ‘Create Task’, 2️⃣ Ilagay ang title, description, category, urgency, at optional payment, 3️⃣ Piliin ang Home Service o Safe Zone, 4️⃣ Click ‘Post’.",
    "Hit ‘Create Task’, punan ang mga detalye (description, bayad, urgency), piliin ang meeting type, at pindutin ang ‘Post’. Doon mo makikita ang applicants.",
    "Steps: Create Task > Fill details > Select location type > Post. Matapos ma-post, makikita sa Browse Tasks ang iyong listing.",
    "Simply go to ‘Create Task’, complete all fields, then tap ‘Post’. Maaari mong i-edit o i-delete ang task anytime bago ma-confirm.",
    "Need guidance? Sabihin mo ang title at description ng task mo, tutulungan kitang ilagay sa tamang fields."
  ];
  replies.push(postTask[Math.floor(Math.random() * postTask.length)]);
}

// Apply Task
if (/\b(apply\s?task|mag-?apply|how to apply|paano mag-apply)\b/i.test(input)) {
  const applyTask = [
    "Para mag-apply: piliin ang task sa Browse, i-click ang ‘Apply’, at hintayin ang confirmation ng Task Poster. Makikita mo ang status sa My Dashboard.",
    "Hit ‘Apply’ sa task listing. Kapag na-approve, lalabas ang Magic Word at makikita mo ang address (kung Home Service) o meeting point (Safe Zone).",
    "Apply by tapping the ‘Apply’ button—pwede mong i-monitor ang iyong applications sa ‘My Applications’ tab.",
    "Steps: Browse Tasks > Apply > Wait for confirmation > Receive Magic Word. Simple lang.",
    "May tanong tungkol sa application? Sabihin mo lang, ipapaliwanag ko ang process step by step."
  ];
  replies.push(applyTask[Math.floor(Math.random() * applyTask.length)]);
}

// Magic Word
if (/\b(magic\s?word|anong magic word|resend magic word|generate magic word)\b/i.test(input)) {
  const magicWord = [
    "Magic Word is your identity proof. Ipakita ito sa helper/requester when you meet para makumpirma ang pagkakakilanlan.",
    "Hindi pa dumating ang Magic Word? Click ‘Resend Magic Word’ sa task details, at susubukan naming mag-send muli.",
    "Generate both ends of the Magic Word sa app bago mag-meet para siguradong pareho kayo ng word.",
    "Use the exact Magic Word provided—kung hindi mag-match, hindi ka papayagang pumasok sa address.",
    "Trouble? Sabihin mo kung pareho kayong nag-generate ng word, tutulungan kitang i-resend."
  ];
  replies.push(magicWord[Math.floor(Math.random() * magicWord.length)]);
}

// Safe Zone
if (/\b(safe\s?zone|meeting\s?point|public\s?safe\s?zone|suggest safe zone)\b/i.test(input)) {
  const safeZone = [
    "Safe Zones are barangay-approved public meeting points. Makikita mo ang list at mapa sa task creation page.",
    "To choose a Safe Zone: select ‘Public Meeting Point’ at pick the nearest location from the map provided.",
    "Want to suggest a new Safe Zone? Submit your proposal sa Support > Safe Zone Suggestions.",
    "Siguraduhing well-lit at accessible ang Safe Zone na pipiliin mo para sa safety ng lahat.",
    "Questions about Safe Zone? Sabihin mo kung saan bahagi ka ng barangay, tutulungan kitang piliin."
  ];
  replies.push(safeZone[Math.floor(Math.random() * safeZone.length)]);
}

// Emergency
if (/\b(emergency|urgent|alert|panic|press\s?button)\b/i.test(input)) {
  const emergency = [
    "Press the red Emergency Button kung may urgent na sitwasyon. Aalerto agad ang barangay admin at security.",
    "Accidental alert? I-click ang ‘Cancel’ sa popup window within 5 seconds para hindi ma-forward.",
    "Pag na-trigger, makakatanggap ka ng confirmation SMS at tatawagan ka ng admin for follow-up.",
    "Use the Emergency Button only for true emergencies. For non-critical issues, contact support.",
    "After alert, stay safe at maghintay ng instructions mula sa admin o on-duty personnel."
  ];
  replies.push(emergency[Math.floor(Math.random() * emergency.length)]);
}

// Ratings & Leaderboard
if (/\b(rating|review|leaderboard|top\s?20|rate)\b/i.test(input)) {
  const ratingsLeaderboard = [
    "Pagkatapos ng task, mag-rate gamit ang 1–5 stars at optional feedback para makatulong sa ibang users.",
    "High ratings boost your visibility. Strive for the Top 20 leaderboard at makakuha ng special badge.",
    "Kung unfair ang rating mo, maaari kang mag-request ng review sa barangay admin mula sa task details.",
    "Your honest reviews help maintain trust sa community—rate fairly pagkatapos ng bawat task.",
    "To view the leaderboard, click ‘Leaderboard’ sa menu—makikita mo doon ang Top 20 verified users."
  ];
  replies.push(ratingsLeaderboard[Math.floor(Math.random() * ratingsLeaderboard.length)]);
}

// Payments
if (/\b(payment|bayad|cash|digital payment|settle)\b/i.test(input)) {
  const payments = [
    "Payments are settled in person pagkatapos ng task. Agree on cash o digital transfer bago kayo magsimula.",
    "HatidGawa doesn’t process payments—mag-settle kayo ng helper at requester face-to-face pagkatapos ng trabaho.",
    "Mark your task as ‘Volunteer’ kung gusto mo ng no-payment service at purely bayanihan spirit.",
    "Common payment methods: cash, GCash. Siguraduhing pareho kayong okay sa agreed method.",
    "Payment dispute? Contact barangay admin or support@hatidgawa.com para sa mediation."
  ];
  replies.push(payments[Math.floor(Math.random() * payments.length)]);
}

// Chat & Notifications
if (/\b(chat|notification|message|in-app chat|SMS|email)\b/i.test(input)) {
  const chatNotifications = [
    "Ang chat mag-o-open once confirmed ang task. Maaari kang mag-send ng text, images, o location pins.",
    "Chat history auto-deletes after 7 days, kaya i-save agad ang important details bago mawala.",
    "Hindi ka nakakatanggap ng notifications? Punta sa Settings > Notifications at i-enable ang SMS/email alerts.",
    "Ensure hindi naka-Do Not Disturb ang phone mo para tuloy-tuloy ang alerts.",
    "Pwede mong i-snooze notifications for 1 hour sa Settings kung kailangan mong magpahinga."
  ];
  replies.push(chatNotifications[Math.floor(Math.random() * chatNotifications.length)]);
}

// Troubleshooting
if (/\b(error|bug|won't load|di gumagana|issue|problem)\b/i.test(input)) {
  const troubleshooting = [
    "App/website won’t load? Clear your browser cache o i-update ang browser mo sa latest version.",
    "Supported browsers: Chrome, Firefox, Edge, Safari. Siguraduhing up-to-date ang iyong OS at browser.",
    "May error code? I-screenshot ang code at i-send sa support@hatidgawa.com kasama ang description.",
    "Try refreshing the page o restarting the app. Kung di pa rin, sabihin mo sa akin ang exact error.",
    "Need step-by-step help? Ibigay mo ang iyong device at browser info, tutulungan kita."
  ];
  replies.push(troubleshooting[Math.floor(Math.random() * troubleshooting.length)]);
}

// Admin Tasks
if (/\b(admin|approve|reject|safe\s?zones\s?admin|moderator|manage users)\b/i.test(input)) {
  const adminTasks = [
    "As an admin, puntahan ang ‘User Verification’ tab para i-approve o i-reject ang flagged registrations.",
    "Manage Safe Zones sa ‘Safe Zones’ > ‘Add/Edit Location’ menu—update o magdagdag ng bagong meeting point.",
    "View real-time emergency alerts sa ‘Emergency Dashboard’ para sa mabilis na response.",
    "Moderate reported chats sa ‘Reports’ section at resolve user disputes ayon sa barangay rules.",
    "Need help with admin tools? Sabihin mo kung anong feature ang gusto mong i-navigate."
  ];
  replies.push(adminTasks[Math.floor(Math.random() * adminTasks.length)]);
}

// Privacy & Terms
if (/\b(privacy policy|terms|data|cookies|delete\s?account|data retention)\b/i.test(input)) {
  const privacyTerms = [
    "Basahin ang Privacy Policy sa footer link para malaman kung paano namin ginagamit at pinoprotektahan ang data mo.",
    "Para i-delete ang account, puntahan ang Settings > Data & Privacy > Delete Account at sundin ang steps.",
    "Ginagamit namin ang cookies para sa mas smooth na experience—pwede mong i-disable ito sa browser settings.",
    "Data retention policy: nire-retain namin data habang aktibo ka sa platform at ayon sa legal requirements.",
    "Have questions about data? Email privacy@hatidgawa.com para sa iyong inquiries."
  ];
  replies.push(privacyTerms[Math.floor(Math.random() * privacyTerms.length)]);
}
    // How to use
    if (/(how to use|paano.*gamitin).*(website|site)/.test(input)) {
      const howToUse = [
   `Para gamitin ang HatidGawa website:
1. Mag-sign up o mag-login.
2. I-explore ang “Browse Tasks” para makita ang available na trabaho.
3. Mag-post sa “Create Task” para maghanap ng helpers—kung wala sa “Browse Tasks” ang hinahanap mo, i-post ang detalye ng task, hintayin ang mag-apply, at i-review/approve ang profile ng pipiliin mong helper.
4. I-monitor sa “My Dashboard” ang status ng iyong task (Pending → Accepted → Completed) at mga chat notifications.
5. Gamitin ang Menu para sa Settings, Support, at Emergency Button.`
];
      replies.push(howToUse[Math.floor(Math.random() * howToUse.length)]);
    }

    // What AI helps
    if (/(paano ito nakakatulong|pano ito nakakatulong|pano to nakakatulong|pano nakakatulong|paano ito makakatulong|ano ang maidudulot)/i.test(input)) {
      const bring = [
        'This AI helps you navigate HatidGawa—mula signup hanggang pag-track ng status.',
        'Ang AI na ito ang iyong guide: task creation, matching, at support inquiries.',
        'It provides instant help para sa lahat ng HatidGawa features—tasks, applications, at verification.'
      ];
      replies.push(bring[Math.floor(Math.random() * bring.length)]);
    }

    // Ordering tasks
    if (/(how to order|paano.*order|paano.*mag-order)/.test(input)) {
      const ordering = [
        `Para mag-order:\n1. Mag-login\n2. Browse Tasks at Apply\n3. Create Task kung requester\n4. Monitor Dashboard`,
        'Madali lang mag-order: Login, Browse & Apply, Create Task, Dashboard updates',
        'Steps: 1. Login 2. Browse & select Task 3. Create Task kung requester 4. Dashboard'
      ];
      replies.push(ordering[Math.floor(Math.random() * ordering.length)]);
    }

    // Tracking
    if (/(track|order status|transaction ko)/.test(input)) {
      const tracking = [
        'Para malaman ang status, puntahan ang My Tasks sa Dashboard.',
        'Dashboard > My Tasks—dun makikita ang lahat ng updates.',
        'Open My Dashboard at piliin My Tasks, refresh kung kailangan.'
      ];
      replies.push(tracking[Math.floor(Math.random() * tracking.length)]);
    }

    // Contact support
    if (/(contact|support|customer service)/.test(input)) {
      const contacts = [
        'Email: support@hatidgawa.com Tel: (02) 8123-4567 / 0917-123-4567 Available 24/7!',
        'Para sa tulong: support@hatidgawa.com o tawag sa (02) 8123-4567.',
        'Need help? Email support@hatidgawa.com or call 0917-123-4567.'
      ];
      replies.push(contacts[Math.floor(Math.random() * contacts.length)]);
    }

    // Issue/problem
    if (/(issue|problem|problema|error)/.test(input)) {
      const issues = [
        'Pasensya sa abala! Paki-send ng order/transaction number, detalye, at date.',
        'Sorry sa issue! Ibigay ang order number, description, at petsa nangyari.',
        'Na-encounter mo ba? Send order ID, issue details, at date para ma-resolve.'
      ];
      replies.push(issues[Math.floor(Math.random() * issues.length)]);
    }

    // Thank you
    if (/(thank|salamat)/.test(input)) {
      const thanks = [
        'Walang anuman! Sabihin mo lang kung may iba ka pang kailangan.',
        'My pleasure! Nandito lang ako.',
        'Ikaw ang priority ko—any time!'
      ];
      replies.push(thanks[Math.floor(Math.random() * thanks.length)]);
    }

    // Help general
    if (/(help|tulong)/.test(input)) {
      const helps = [
        'Nandito ako para tumulong—tracking, order, at iba pa.',
        'Sabihin mo kung ano`ng kailangan mo: tasks, account, support.',
        'Ready akong sagutin lahat ng tanong mo!'
      ];
      replies.push(helps[Math.floor(Math.random() * helps.length)]);
    }

    // Live agent
    if (/\bagent\b/.test(input)) {
      replies.push('Sige, iro-redirect kita sa live agent. Sandali lang...');
    }

    // Prefix for repeats or polite
    let prefix = '';
    if (isRepeat) prefix = 'Pasensya, paki-klaro muli:';
    else if (isPolite) prefix = 'Sure! ';

    if (replies.length > 0) {
      return `${prefix}${replies.join('\n\n')}\n\nMay iba ka pa bang tanong?`;
    }

    if (replies.length > 0) {
  // reset counter on successful match
    fallbackCount = 0;
  return `${prefix}${replies.join('\n\n')}\n\nMay iba ka pa bang tanong?`;
}

// fallback logic
fallbackCount++;
if (fallbackCount >= 3) {
  fallbackCount = 0;
  return "Pasensya, hindi ko maintindihan ang tanong na ito. Limitado ang saklaw ko. Paki-subukan muli o i-type ang 'help' para sa listahan ng commands.";
}

return "Salamat sa iyong paliwanag. Maaari mo bang ipaliwanag nang kaunti pa?";
}
  // Events
  chatbotToggle.addEventListener('click', toggleChatbot);
  chatbotClose.addEventListener('click', toggleChatbot);
  chatbotClear.addEventListener('click', clearConversation);
  chatbotSend.addEventListener('click', sendMessage);
  chatbotInput.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });
  chatbotInput.addEventListener('input', () => { chatbotSend.disabled = chatbotInput.value.trim() === ''; });
  quickTopicButtons.forEach(btn => btn.addEventListener('click', () => handleQuickTopic(btn.dataset.topic)));
  chatbotAttach.addEventListener('click', () => addBotMessage('Paumanhin, hindi pa available ang feature na ito. Abangan ang susunod na update!'));
  document.addEventListener('click', e => {
    if (isOpen && !chatbotContainer.contains(e.target) && !chatbotToggle.contains(e.target)) toggleChatbot();
  });

  // Init
  chatbotSend.disabled = true;
  setTimeout(loadHistory, 200);
})();
