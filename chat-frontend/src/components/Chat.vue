<template>
  <div class="chat-layout">

    <!-- LEFT SIDE: ROOMS -->
    <div class="rooms-panel">
      <h3>Chat Room #{{ roomId }}</h3>
      <div
        v-for="room in rooms"
        :key="room.id"
        class="room-item"
        :class="{ active: room.id == roomId }"
        @click="goToRoom(room.id)"
      >
        <div class="room-name">{{ room.name }}</div>
      </div>
    </div>

    <!-- RIGHT SIDE: CHAT -->
    <div class="chat-wrapper">

      <!-- HEADER -->
      <div class="chat-header">
        <button @click="$router.push('/rooms')" class="back-btn">← Back</button>
        <h3>Chat Room #{{ roomId }}</h3>
        <div class="online-users">
          🟢 Online:
          {{ currentRoomUsers.filter(u => u.is_online).slice(0, 3).map(u => u.name).join(', ') }}
          <span v-if="currentRoomUsers.filter(u => u.is_online).length > 3">
            +{{ currentRoomUsers.filter(u => u.is_online).length - 3 }}
          </span>
          <div class="offline-users">
            ⚫ Offline:
            {{ currentRoomUsers.filter(u => !u.is_online).slice(0, 3).map(u => u.name).join(', ') }}
            <span v-if="currentRoomUsers.filter(u => !u.is_online).length > 3">
              +{{ currentRoomUsers.filter(u => !u.is_online).length - 3 }}
            </span>
          </div>
        </div>
      </div>

      <!-- MESSAGES -->
      <div class="messages-box" ref="messageBox">
        <div
          v-for="msg in messages"
          :key="msg.id"
          :class="['message', msg.user_id === currentUserId ? 'mine' : 'theirs']"
        >
          <div class="sender-name">{{ msg.user.name }}</div>

          <div class="bubble">

  <!-- TEXT -->
  <div v-if="msg.type === 'text' || !msg.type">
    {{ msg.message }}
  </div>

  <!-- IMAGE -->
  <div
    v-else-if="msg.type === 'image'"
    class="img-thumb"
    @click="openLightbox(msg.media_url, 'image')"
  >
    <img :src="msg.media_url" class="thumb-img" />
  </div>

  <!-- VIDEO -->
  <div
    v-else-if="msg.type === 'video'"
    class="video-thumb"
    @click="openLightbox(msg.media_url, 'video')"
  >
    <video
      :src="msg.media_url"
      class="thumb-video"
      preload="metadata"
      muted
    />
    <div class="play-icon">▶</div>
  </div>

  <!-- AUDIO -->
  <div v-else-if="msg.type === 'audio'" class="audio-msg">
    <div class="waveform-player" :id="'player-' + msg.id">
      <button class="play-pause-btn" @click="toggleAudio(msg)">
        {{ playingIds.includes(msg.id) ? '⏸' : '▶' }}
      </button>
      <div class="waveform-bars">
        <span
          v-for="i in 30"
          :key="i"
          class="bar"
          :class="{ active: playingIds.includes(msg.id) }"
          :style="{ height: getBarHeight(msg.id, i) + 'px' }"
        ></span>
      </div>
      <span class="audio-duration">{{ msg._duration || '0:00' }}</span>
      <audio
        :id="'audio-' + msg.id"
        :src="msg.media_url"
        @timeupdate="onTimeUpdate(msg)"
        @ended="onAudioEnded(msg)"
        @loadedmetadata="onMetaLoaded(msg)"
        style="display:none"
      />
    </div>
  </div>

  <!-- ✅ FILE (FIXED POSITION) -->
  <div v-else-if="msg.type === 'file'" class="file-msg">
    <a :href="msg.media_url" target="_blank" class="file-box">

      <span v-if="msg.media_url.endsWith('.pdf')">📕</span>
      <span v-else-if="msg.media_url.endsWith('.doc') || msg.media_url.endsWith('.docx')">📘</span>
      <span v-else-if="msg.media_url.endsWith('.xls') || msg.media_url.endsWith('.xlsx')">📗</span>
      <span v-else>📄</span>

      {{ getFileName(msg.media_url) }}
    </a>
  </div>

</div>





          










          <!-- FOOTER -->
          <div class="message-footer">
            <span class="timestamp">{{ formatTime(msg.created_at) }}</span>
            <span v-if="msg.user_id === currentUserId" class="ticks">
              <span v-if="!msg.seen_by?.length" class="tick delivered">✔</span>
              <span v-else class="tick read">✔✔</span>
            </span>
          </div>

          <div v-if="msg.seen_by?.length" class="seen-by">
            Seen by: {{ msg.seen_by.map(u => u.name).join(', ') }}
          </div>

        </div>
      </div>

      <!-- INPUT AREA -->
      <div class="input-area">
        <label class="file-icon">
          📎
          <input
          type="file"
          @change="handleFile"
          accept=".pdf,.doc,.docx,.xls,.xlsx,image/*,video/*"
          hidden
          />
        </label>
        <div class="input-wrapper">
          <input
            v-model="text"
            @keyup.enter="sendMessage"
            placeholder="Type a message..."
            class="input"
          />
          <div v-if="filePreviewName" class="file-preview">
            📎 {{ filePreviewName }}
            <span class="remove-file" @click="removeFile">✖</span>
          </div>
        </div>

        <!-- VOICE BUTTON -->
        <button
          @click="toggleRecording"
          :class="['voice-btn', { recording: isRecording }]"
          :title="isRecording ? 'Stop recording' : 'Record voice message'"
        >
          {{ isRecording ? '⏹' : '🎤' }}
          <span v-if="isRecording" class="rec-timer">{{ recordingTime }}s</span>
        </button>

        <button @click="sendMessage" class="send-btn">Send</button>
      </div>

    </div>

    <!-- LIGHTBOX -->
    <div v-if="lightbox.show" class="lightbox" @click="closeLightbox">
      <img
        v-if="lightbox.type === 'image'"
        :src="lightbox.url"
        class="lightbox-img"
        @click.stop
      />
      <video
        v-if="lightbox.type === 'video'"
        :key="lightbox.url"
        controls
        autoplay
        class="lightbox-video"
        @click.stop
      >
        <source :src="lightbox.url" type="video/mp4" />
      </video>
      <button class="lightbox-close" @click="closeLightbox">✕</button>
    </div>

  </div>
</template>


<script>
import api from '../services/api'

export default {
  data() {
    return {
      rooms: [],
      messages: [],
      text: '',
      file: null,
      filePreviewName: '',
      currentUserId: null,
      onlineUsers: [],
      playingIds: [],   
      lightbox: {
        show: false,
        url: null,
        type: null
      },
      isRecording: false,
      mediaRecorder: null,
      audioChunks: [],
      recordingTime: 0,
      recordingTimer: null,
      recognition: null,
      transcript: '',
      isListening: false
    }
  },

  computed: {
    roomId() {
      return this.$route.params.id
    },

    currentRoomUsers() {
      const room = this.rooms.find(r => r.id == this.roomId)
      const allUsers = room && room.users ? room.users : []
      return allUsers.map(user => ({
        ...user,
        is_online: this.onlineUsers.some(u => u.id === user.id)
      }))
    }
  },

  watch: {
    roomId() {
      this.loadMessages()
      this.markAsRead()
    }
  },

  async mounted() {
    const res = await api.get('/user')
    this.currentUserId = res.data.id

    await this.loadRooms()
    await this.loadMessages()
    await this.markAsRead()

    window.Echo.channel(`chat.${this.roomId}`)
      .listen('.message.sent', (e) => {
        console.log('📨 Message received:', e)
        this.handleIncomingMessage(e)
        if (e.user_id !== this.currentUserId) {
          this.markAsRead()
        }
      })

      const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition

        if (SpeechRecognition) {
          this.recognition = new SpeechRecognition()
          this.recognition.continuous = true
          this.recognition.interimResults = true
          this.recognition.lang = 'en-US'

          this.recognition.onresult = (event) => {
            if (!event.results) return

            let finalText = ''
            let interimText = ''

            for (let i = 0; i < event.results.length; i++) {
              const res = event.results[i]

              if (!res || !res[0]) continue

              if (res.isFinal){
                finalText += res[0].transcript + ''
              } else {
                interimText += res[0].transcript
              }
            }

             const fullText = finalText + interimText
              this.transcript = fullText
              this.text = fullText



            this.text = fullText
            console.log('Transcript:' , this.transcript)
            this.text = finalText + interimText
          }

          
        }

    window.Echo.join(`presence-chat.${this.roomId}`)
      .here((users) => { this.onlineUsers = users })
      .joining((user) => { this.onlineUsers.push(user) })
      .leaving((user) => {
        this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id)
      })
  },



  beforeUnmount() {
    window.Echo.leave(`chat.${this.roomId}`)
    window.Echo.leave(`presence-chat.${this.roomId}`)
  },

  methods: {

    // ── RECORDING ──────────────────────────────
    async toggleRecording() {
      if (this.isRecording) {
        this.stopRecording()
      } else {
        await this.startRecording()
      }
    },

async startRecording() {
  try {
    // ✅ START SPEECH (SAFE)
    if (this.recognition && !this.isListening) {
      try {
        this.transcript = ''
        this.recognition.start()
        this.isListening = true
      } catch (err) {
        console.log('⚠️ Recognition already running')
      }
    }

    // ✅ GET MIC
    const stream = await navigator.mediaDevices.getUserMedia({
      audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true
      }
    })

    this.audioChunks = []

    // ✅ MIME TYPE SAFE
    let mimeType = ''
    if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
      mimeType = 'audio/webm;codecs=opus'
    } else if (MediaRecorder.isTypeSupported('audio/webm')) {
      mimeType = 'audio/webm'
    }

    if (this.recognition && !this.isListening) {
  try {
    this.transcript = ''
    this.recognition.start()
    this.isListening = true
  } catch (err) {
    console.log('⚠️ Recognition already running')
  }
}

    const options = mimeType ? { mimeType } : {}

    this.mediaRecorder = new MediaRecorder(stream, options)

    this.mediaRecorder.ondataavailable = (e) => {
      if (e.data.size > 0) this.audioChunks.push(e.data)
    }

    this.mediaRecorder.onstop = async () => {
      const blob = new Blob(this.audioChunks, {
        type: mimeType || 'audio/webm'
      })

      await this.sendAudio(blob, 'webm')

      stream.getTracks().forEach(t => t.stop())
      clearInterval(this.recordingTimer)
      this.recordingTime = 0
    }

    this.mediaRecorder.start()
    this.isRecording = true

    this.recordingTimer = setInterval(() => {
      this.recordingTime++
    }, 1000)

  } catch (err) {
    console.error(err)
    alert('Microphone access denied or not supported')
  }
},

   stopRecording() {
  if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
    this.mediaRecorder.stop()
  }

  // ✅ STOP SPEECH CLEANLY
  if (this.recognition && this.isListening) {
    try {
      this.recognition.stop()
    } catch (e) {}
    this.isListening = false
  }

  this.isRecording = false
},

    async sendAudio(blob, ext = 'webm') {
      const formData = new FormData()
      formData.append('file', blob, `voice.${ext}`)

      // ADD TRANSCRIT

      if (this.transcript) {
        formData.append('message', this.transcript)
      }


      const socketId = window.Echo.socketId()
      try {
        await api.post(
          `/chat-rooms/${this.roomId}/messages`,
          formData,
          { headers: { 'X-Socket-ID': socketId } }
        )
      } catch (err) {
        console.log(err.response?.data)
      }
    },







  


    // ── WAVEFORM PLAYER ────────────────────────
    getBarHeight(msgId, index) {
      const seed = (msgId * 9301 + index * 49297) % 233280
      const rand = seed / 233280
      return Math.floor(rand * 28) + 8
    },




    toggleAudio(msg) {
  const audio = document.getElementById('audio-' + msg.id)
  console.log('Audio element:', audio)
  console.log('Audio src:', audio?.src)
  console.log('Audio readyState:', audio?.readyState)

  if (!audio) return

  if (this.playingIds.includes(msg.id)) {
    audio.pause()
    this.playingIds = this.playingIds.filter(id => id !== msg.id)
  } else {
    this.playingIds.forEach(id => {
      const a = document.getElementById('audio-' + id)
      if (a) a.pause()
    })
    this.playingIds = []

    audio.play()
      .then(() => console.log('✅ Playing'))
      .catch(err => console.error('❌ Play failed:', err))

    this.playingIds.push(msg.id)
  }
},


    onTimeUpdate(msg) {
      const audio = document.getElementById('audio-' + msg.id)
      if (!audio) return
      const current = audio.currentTime
      const total = audio.duration || 1
      const pct = current / total
      const bars = document.querySelectorAll(`#player-${msg.id} .bar`)
      bars.forEach((bar, i) => {
        bar.style.background = (i / bars.length) < pct ? '#075e54' : '#b2dfdb'
      })
      const mins = Math.floor(current / 60)
      const secs = Math.floor(current % 60).toString().padStart(2, '0')
      msg._duration = `${mins}:${secs}`
    },

    onAudioEnded(msg) {
      this.playingIds = this.playingIds.filter(id => id !== msg.id)
      const bars = document.querySelectorAll(`#player-${msg.id} .bar`)
      bars.forEach(bar => { bar.style.background = '#b2dfdb' })
      const audio = document.getElementById('audio-' + msg.id)
      if (audio) audio.currentTime = 0
      msg._duration = '0:00'
    },

    onMetaLoaded(msg) {
      const audio = document.getElementById('audio-' + msg.id)
      if (!audio || !audio.duration) return
      const mins = Math.floor(audio.duration / 60)
      const secs = Math.floor(audio.duration % 60).toString().padStart(2, '0')
      msg._duration = `${mins}:${secs}`
    },


    getFileName(url) {
    if (!url) return 'Download file'
    return url.split('/').pop()
    },

    // ── ROOMS / MESSAGES ───────────────────────
    goToRoom(id) {
      this.$router.push(`/chat/${id}`)
    },

    openLightbox(url, type) {
      this.lightbox = { show: true, url, type }
    },

    closeLightbox() {
      const video = document.querySelector('.lightbox-video')
      if (video) {
        video.pause()
        video.src = ''
      }
      this.lightbox = { show: false, url: null, type: null }
    },

    async loadRooms() {
      const res = await api.get('/chat-rooms')
      this.rooms = res.data
    },

    async loadMessages() {
      const res = await api.get(`/chat-rooms/${this.roomId}/messages`)
      this.messages = res.data
      this.scrollToBottom()
    },

    handleIncomingMessage(e) {
      const index = this.messages.findIndex(m => m.id === e.id)
      if (index !== -1) {
        this.messages.splice(index, 1, { ...this.messages[index], ...e })
      } else {
        this.messages.push(e)
        this.scrollToBottom()
      }
    },

    async sendMessage() {
      if (!this.text.trim() && !this.file) return
      const formData = new FormData()
      if (this.text.trim()) formData.append('message', this.text)
      if (this.file) formData.append('file', this.file)
      const socketId = window.Echo.socketId()
      try {
        await api.post(
          `/chat-rooms/${this.roomId}/messages`,
          formData,
          { headers: { 'X-Socket-ID': socketId } }
        )
        this.text = ''
        this.file = null
        this.filePreviewName = ''
      } catch (err) {
        console.log(err.response?.data)
      }
    },

    handleFile(e) {
      const file = e.target.files[0]
      if (!file) return
      this.file = file
      this.filePreviewName = file.name
      this.fileType = file.type
    },

    removeFile() {
      this.file = null
      this.filePreviewName = ''
    },

    async markAsRead() {
      try {
        await api.post(`/chat-rooms/${this.roomId}/read`)
      } catch (e) {
        console.log(e)
      }
    },

    formatTime(time) {
      if (!time) return ''
      return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const box = this.$refs.messageBox
        if (box) box.scrollTop = box.scrollHeight
      })
    }
  }
}
</script>


<style scoped>
.chat-layout {
  display: flex;
  height: 100vh;
  background: #e5ddd5;
  font-family: Arial, sans-serif;
}

.rooms-panel {
  width: 280px;
  background: #075e54;
  border-right: 1px solid #ddd;
  overflow-y: auto;
  padding: 10px;
  color: white;
}

.room-item {
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
}

.room-item:hover { background: rgba(255,255,255,0.1); }
.room-item.active { background: white; color: #075e54; }

.chat-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #075e54;
  color: white;
  padding: 12px;
}

.messages-box {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-start;
}

.message {
  width: 100%;
  display: flex;
  flex-direction: column;
}

.mine {
  align-items: flex-end;
  align-self: flex-end;
  text-align: right;
}

.theirs {
  align-items: flex-start;
  align-self: flex-start;
  text-align: left;
}

.bubble {
  padding: 10px;
  border-radius: 10px;
  font-size: 14px;
  max-width: fit-content;
  word-break: break-word;
}

.mine .bubble { background: #dcf8c6; }
.theirs .bubble { background: white; }

.message-footer {
  display: flex;
  gap: 6px;
  font-size: 10px;
  color: #666;
}

.back-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}

.input-area {
  display: flex;
  gap: 10px;
  padding: 10px;
  background: #f0f0f0;
  align-items: center;
}

.file-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.input-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.input {
  padding: 10px;
  border-radius: 20px;
  border: none;
}

.online-users {
  font-size: 12px;
  color: #b2ffb2;
  margin-top: 4px;
}

.offline-users {
  font-size: 12px;
  color: #ccc;
  margin-top: 2px;
}

.file-preview {
  margin-top: 5px;
  font-size: 12px;
  background: white;
  padding: 4px 8px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.remove-file { cursor: pointer; color: red; }

.send-btn {
  background: #075e54;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
}

/* LIGHTBOX */
.lightbox {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  cursor: zoom-out;
}

.lightbox-img {
  max-width: 90vw; max-height: 90vh;
  border-radius: 10px;
  cursor: default;
}

.lightbox-video {
  max-width: 90vw; max-height: 90vh;
  border-radius: 10px;
  cursor: default;
}

.lightbox-close {
  position: fixed;
  top: 20px; right: 24px;
  background: rgba(255,255,255,0.15);
  border: none; color: white;
  font-size: 22px;
  width: 40px; height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-close:hover { background: rgba(255,255,255,0.3); }

/* IMAGE THUMB */
.img-thumb {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.thumb-img {
  max-width: 220px;
  max-height: 500px;
  border-radius: 10px;
  display: block;
  object-fit: cover;
}



.img-thumb:hover .zoom-icon { opacity: 1; }

/* VIDEO THUMB */
.video-thumb {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.thumb-video {
  max-width: 250px;
  max-height: 500px;
  border-radius: 10px;
  display: block;
  object-fit: cover;
  pointer-events: none;
}

.play-icon {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(0,0,0,0.6);
  color: white;
  font-size: 24px;
  width: 52px; height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
  transition: background 0.2s;
}

.video-thumb:hover .play-icon { background: rgba(0,0,0,0.85); }

/* AUDIO */
.audio-msg {
  padding: 4px 0;
  min-width: 220px;
}

.waveform-player {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 20px;
  background: transparent;
}

.play-pause-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #075e54;
  color: white;
  border: none;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.waveform-bars {
  display: flex;
  align-items: center;
  gap: 2px;
  flex: 1;
}

.bar {
  width: 3px;
  border-radius: 3px;
  background: #b2dfdb;
  display: inline-block;
  transition: background 0.1s;
}

.bar.active {
  animation: bounce 0.6s ease-in-out infinite alternate;
}

.audio-duration {
  font-size: 11px;
  color: #555;
  min-width: 28px;
  text-align: right;
}

/* VOICE BUTTON */
.voice-btn {
  width: 42px; height: 42px;
  border-radius: 50%;
  border: none;
  background: white;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
  transition: background 0.2s;
}

.voice-btn.recording {
  background: #ff4444;
  animation: pulse 1s infinite;
}

.rec-timer {
  position: absolute;
  bottom: -16px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 10px;
  color: #ff4444;
  white-space: nowrap;
}


.file-msg {
  padding: 6px 0;
}

.file-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  padding: 8px 12px;
  border-radius: 10px;
  text-decoration: none;
  color: #333;
  font-size: 13px;
  max-width: 250px;
  border: 1px solid #ddd;
}

.mine .file-box {
  background: #dcf8c6;
}




@keyframes bounce {
  from { transform: scaleY(0.6); }
  to   { transform: scaleY(1); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}
</style>