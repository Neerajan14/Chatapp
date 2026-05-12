<template>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-logo">💬</div>
      <h2>Create Account</h2>
      <p class="subtitle">Join the chat today</p>

      <div v-if="error" class="error-msg">{{ error }}</div>
      <div v-if="success" class="success-msg">{{ success }}</div>

      <!-- NAME -->
      <input
        v-model="name"
        type="text"
        placeholder="Full Name"
        class="input"
      />

      <!-- EMAIL -->
      <input
        v-model="email"
        type="email"
        placeholder="Email"
        class="input"
      />

      <!-- PASSWORD -->
      <div class="password-box">
        <input
          v-model="password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Password"
          class="input"
        />
        <span class="toggle" @click="showPassword = !showPassword">
          {{ showPassword ? '🙈' : '👁️' }}
        </span>
      </div>

      <!-- CONFIRM PASSWORD -->
      <div class="password-box">
        <input
          v-model="password_confirmation"
          :type="showConfirmPassword ? 'text' : 'password'"
          placeholder="Confirm Password"
          class="input"
          @keyup.enter="register"
        />
        <span class="toggle" @click="showConfirmPassword = !showConfirmPassword">
          {{ showConfirmPassword ? '🙈' : '👁️' }}
        </span>
      </div>

      <!-- BUTTON -->
      <button @click="register" class="btn" :disabled="loading">
        {{ loading ? 'Creating account...' : 'Register' }}
      </button>

      <!-- SWITCH -->
      <p class="switch-text">
        Already have an account?
        <router-link to="/">Login</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import api from '@/services/api'

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',

      showPassword: false,
      showConfirmPassword: false,

      loading: false,
      error: '',
      success: ''
    }
  },

  methods: {
    async register() {
      if (!this.name || !this.email || !this.password) {
        this.error = 'Please fill in all fields.'
        return
      }

      if (this.password !== this.password_confirmation) {
        this.error = 'Passwords do not match.'
        return
      }

      this.loading = true
      this.error = ''
      this.success = ''

      try {
        const res = await api.post('/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation
        })

        localStorage.setItem('token', res.data.token)

        this.success = 'Account created successfully!'

        setTimeout(() => {
          this.$router.push('/rooms')
        }, 800)

      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed.'
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
  padding: 25px;
  background: linear-gradient(135deg, #667eea, #764ba2);
}

/* CARD */
.auth-card {
  width: 100%;
  max-width: 500px;
  background: #fff;
  padding: 60px 50px;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
  text-align: center;
  transition: 0.3s;
}

.auth-card:hover {
  transform: translateY(-3px);
}

/* LOGO */
.auth-logo {
  font-size: 64px;
  margin-bottom: 10px;
}

/* TITLE */
h2 {
  font-size: 30px;
  color: #333;
  margin-bottom: 6px;
}

/* SUBTITLE */
.subtitle {
  font-size: 15px;
  color: #888;
  margin-bottom: 30px;
}

/* INPUT */
.input {
  width: 100%;
  padding: 15px 16px;
  margin: 10px 0;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  font-size: 16px;
  outline: none;
  transition: 0.25s;
  box-sizing: border-box;
  background: #fafafa;
}

.input:focus {
  border-color: #667eea;
  background: #fff;
  box-shadow: 0 0 8px rgba(102, 126, 234, 0.25);
}

/* PASSWORD BOX */
.password-box {
  position: relative;
}

.password-box .input {
  padding-right: 45px;
}

/* TOGGLE EYE ICON */
.toggle {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  user-select: none;
  color: #777;
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
  font-size: 17px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.25s;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  opacity: 0.95;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* MESSAGES */
.error-msg {
  background: #fff0f0;
  color: #e53935;
  border: 1px solid #ffcdd2;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 15px;
}

.success-msg {
  background: #f0fff4;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
  padding: 12px;
  border-radius: 10px;
  font-size: 14px;
  margin-bottom: 15px;
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

/* MOBILE */
@media (max-width: 600px) {
  .auth-card {
    padding: 40px 25px;
    border-radius: 16px;
  }

  h2 {
    font-size: 24px;
  }
}

</style>