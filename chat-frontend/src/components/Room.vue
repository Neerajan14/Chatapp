<template>
  <div class="rooms-wrapper">
    <div class="rooms-header">
      <h2>💬 Chat Rooms</h2>
      <button @click="logout" class="logout-btn">Logout</button>
    </div>

    <div class="rooms-container">

      <!-- Create Room -->
      <div class="create-room">
        <input
          v-model="newRoomName"
          @keyup.enter="createRoom"
          placeholder="New room name..."
          class="input"
        />
        <button @click="createRoom" class="btn">+ Create</button>
      </div>

      <!-- Room List -->
      <div class="room-list">

        <div
          v-for="room in rooms"
          :key="room.id"
          class="room-card"
          @click="openRoom(room.id)"
        >
          <span class="room-icon">💬</span>

          <span
          class="room-name"
          :style="{ fontWeight: room.hasNewMessage ? 'bold' : 'normal' }"
          >
          {{ room.name }}
          </span>

         

          <button @click.stop="deleteRoom(room.id)" class="delete-btn">🗑️</button>

          <span class="room-arrow">→</span>
        </div>

        <div v-if="rooms.length === 0" class="no-rooms">
          No rooms yet. Create one above!
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  data() {
    return {
      rooms: [],
      newRoomName: ''
    }
  },

  async mounted() {
    await this.loadRooms()

    if (window.Echo.connector.pusher.connection.state === 'connected') {
      this.listenForMessages()
    } else {
      window.Echo.connector.pusher.connection.bind('connected', () => {
        this.listenForMessages()
      })
    }
  },

  beforeUnmount() {
    this.rooms.forEach(room => {
      window.Echo.leave(`chat.${room.id}`)
    })
  },

  // ✅ watch route — reload rooms when navigating back to /rooms
  watch: {
    '$route'(to) {
      if (to.path === '/rooms') {
        this.loadRooms()
      }
    }
  },

  methods: {
   async loadRooms() {
  try {
    const res = await api.get('/chat-rooms')

    this.rooms = res.data.map(room => ({
      ...room,
      hasNewMessage: false
    }))
  } catch (error) {
    console.log(error.response?.data)
  }
},

  listenForMessages() {
  this.rooms.forEach(room => {
    window.Echo.channel(`chat.${room.id}`)
      .listen('.message.sent', () => {

        const targetRoom = this.rooms.find(r => r.id === room.id)
        if (targetRoom) {
          targetRoom.hasNewMessage = true
        }

      })
  })
},

    async createRoom() {
      if (!this.newRoomName.trim()) return

      try {
        await api.post('/chat-rooms', { name: this.newRoomName })
        this.newRoomName = ''
        await this.loadRooms()
        this.listenForMessages()
      } catch (error) {
        console.log(error.response?.data)
      }
    },

    async deleteRoom(id) {
      if (!confirm('Delete this room and all its messages?')) return

      try {
        window.Echo.leave(`chat.${id}`)
        await api.delete(`/chat-rooms/${id}`)
        await this.loadRooms()
      } catch (error) {
        console.log(error.response?.data)
      }
    },

    openRoom(id) {
      this.$router.push(`/chat/${id}`)
    },

    logout() {
      this.rooms.forEach(room => {
        window.Echo.leave(`chat.${room.id}`)
      })
      localStorage.clear()
      this.$router.push('/login')
    }
  }
}
</script>

<style scoped>
.rooms-wrapper {
  min-height: 100vh;
  background: #e5ddd5;
  display: flex;
  flex-direction: column;
  font-family: Arial, sans-serif;
}

.rooms-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 20px;
  background: #075e54;
  color: white;
}

.rooms-container {
  flex: 1;
  background: #f0f2f5;
  border-radius: 20px 20px 0 0;
  padding: 20px;
}

.room-card {
  display: flex;
  align-items: center;
  background: white;
  padding: 14px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.2s;
}

.room-card:hover {
  background: #f7f7f7;
}

.room-icon {
  font-size: 20px;
  margin-right: 12px;
}

.room-name {
  flex: 1;
  font-size: 15px;
  font-weight: 500;
}



.logout-btn {
  background: rgba(255,255,255,0.15);
  border: none;
  color: white;
  padding: 6px 14px;
  border-radius: 20px;
  cursor: pointer;
  transition: 0.2s;
}

.logout-btn:hover {
  background: rgba(255,255,255,0.3);
}

.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0;
  transition: 0.2s;
}

.room-card:hover .delete-btn {
  opacity: 1;
}

.room-arrow {
  margin-left: 10px;
  color: #075e54;
}

.no-rooms {
  text-align: center;
  padding: 40px;
  color: #888;
}

.create-room {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.input {
  flex: 1;
  padding: 12px 14px;
  border: 1px solid #ddd;
  border-radius: 25px;
  outline: none;
  font-size: 14px;
}

.input:focus {
  border-color: #075e54;
  box-shadow: 0 0 5px rgba(7,94,84,0.2);
}

.btn {
  padding: 12px 18px;
  background: #075e54;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.2s;
}

.btn:hover {
  opacity: 0.9;
}
</style>