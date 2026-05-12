<template>
  <div class="auth-wrapper">
    <div class="auth-card">

      <div class="auth-logo">💬</div>
      <h2>Welcome Back</h2>
      <p class="subtitle">Sign in to continue</p>

      <div v-if="error" class="error-msg">{{ error }}</div>

      <!-- EMAIL -->
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="input"
        @keyup.enter="login"
      />

      <!-- PASSWORD -->
      <div class="password-box">
        <input
          v-model="password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Password"
          class="input"
          @keyup.enter="login"
        />

        <span class="toggle" @click="showPassword = !showPassword">
          {{ showPassword ? '🙈' : '👁️' }}
        </span>
      </div>

      <!-- BUTTON -->
      <button @click="login" class="btn" :disabled="loading">
        {{ loading ? 'Signing in...' : 'Login' }}
      </button>

      <!-- SWITCH -->
      <p class="switch-text">
        Don't have an account?
        <router-link to="/register">Register</router-link>
      </p>

    </div>
  </div>
</template>




<script>
import api from '@/services/api'

export default {
  data() {
    return {
      email: '',
      password: '',
      loading: false,
      error: '',
      showPassword: false
    }
  },

  methods: {
   async login() {
  try {
    this.loading = true
    this.error = ''

    const res = await api.post('/login', {
      email: this.email,
      password: this.password
    })

    // ✅ SAVE TOKEN
    localStorage.setItem('token', res.data.token)

    // redirect
    this.$router.push('/rooms')

  } catch (err) {
    this.error = 'Invalid email or password'
  } finally {
    this.loading = false
  }
}
 
  }
}
</script>





<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #667eea, #764ba2);
  padding: 20px;
}

/* 🔥 BIGGER CARD */
.auth-card {
  background: white;
  padding: 70px 55px;        /* bigger padding */
  border-radius: 18px;
  width: 500px;              /* bigger width */
  max-width: 95%;            /* responsive fix */
  box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  text-align: center;
}

/* LOGO */
.auth-logo {
  font-size: 55px;
  margin-bottom: 10px;
}

/* TITLE */
h2 {
  margin: 0 0 5px;
  color: #333;
  font-size: 28px;           /* bigger text */
}

.subtitle {
  color: #888;
  font-size: 15px;
  margin-bottom: 30px;
}

/* ERROR */
.error-msg {
  background: #fff0f0;
  color: #e53e3e;
  border: 1px solid #fed7d7;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 15px;
}

/* INPUT */
.input {
  width: 100%;
  padding: 14px 18px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 12px;
  outline: none;
  font-size: 16px;
  transition: 0.3s;
  box-sizing: border-box;
}

.input:focus {
  border-color: #667eea;
  box-shadow: 0 0 8px rgba(102,126,234,0.3);
}

/* PASSWORD BOX */
.password-box {
  position: relative;
}

.password-box .input {
  padding-right: 45px;
}

/* EYE ICON */
.toggle {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  color: #666;
}

.toggle:hover {
  color: #333;
}

/* BUTTON */
.btn {
  width: 100%;
  padding: 15px;
  margin-top: 18px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: bold;
  font-size: 17px;
  transition: 0.3s;
}

.btn:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-2px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* SWITCH TEXT */
.switch-text {
  margin-top: 22px;
  font-size: 14px;
  color: #666;
}

.switch-text a {
  color: #667eea;
  font-weight: bold;
  text-decoration: none;
}

.switch-text a:hover {
  text-decoration: underline;
}
</style>