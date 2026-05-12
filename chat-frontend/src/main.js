import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import App from './App.vue'
import UserLogin from './components/UserLogin.vue'
import Register from './components/Register.vue'
import Room from './components/Room.vue'
import Chat from './components/Chat.vue'
import api from './services/api'
import './services/echo.js'  // ← this handles Echo, nothing else needed

// ---------------- ROUTER ----------------
const router = createRouter({
    history: createWebHashHistory(),
    routes: [
        { path: '/', component: UserLogin },
        { path: '/login', component: UserLogin },
        { path: '/register', component: Register },
        { path: '/rooms', component: Room },
        { path: '/chat/:id', component: Chat, props: true }
    ]
})

// ---------------- APP ----------------
const app = createApp(App)

const token = localStorage.getItem('token')
if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

app.use(router)
app.mount('#app')