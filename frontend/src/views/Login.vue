<template>
  <div class="login-container">
    <div class="login-wrapper">
      <!-- Left: System Introduction -->
      <div class="intro-panel">
        <h1 class="intro-title">PDF Generator System</h1>
        <p class="intro-tagline">Batch PDF Generation & Email Distribution Platform</p>
        <div class="intro-divider"></div>
        <p class="intro-desc">
          This system is designed for <strong>batch generating PDFs</strong> from customizable templates 
          and <strong>distributing them via email</strong> — it is <em>not</em> a file format converter 
          (e.g. Word/Excel to PDF).
        </p>
        <ul class="intro-features">
          <li><span class="feature-icon">📝</span> Create reusable PDF templates with dynamic variables</li>
          <li><span class="feature-icon">📦</span> Input batch data to generate PDFs</li>
          <li><span class="feature-icon">📧</span> Send generated PDFs to recipients email</li>
          <li><span class="feature-icon">📊</span> Track generation and delivery status in real time</li>
        </ul>
        <div class="intro-example">
          <span class="example-label">Use cases:</span>
          Transcripts, Payroll slips, Certificates, Invoices, and more.
        </div>
      </div>

      <!-- Right: Login Form -->
      <div class="login-box">
        <h2>Sign In</h2>
        <p class="subtitle">Login to your account</p>
        <el-form :model="form" :rules="rules" ref="formRef">
          <el-form-item prop="username">
            <el-input
              v-model="form.username"
              placeholder="Username or Email"
              size="large"
              prefix-icon="User"
            />
          </el-form-item>
          <el-form-item prop="password">
            <el-input
              v-model="form.password"
              type="password"
              placeholder="Password"
              size="large"
              prefix-icon="Lock"
              @keyup.enter="handleLogin"
            />
          </el-form-item>
          <el-button
            type="primary"
            size="large"
            :loading="loading"
            @click="handleLogin"
            class="login-btn"
          >
            Login
          </el-button>
        </el-form>
        <div class="footer">
          <span>Don't have an account?</span>
          <el-link type="primary" @click="goToRegister">Register</el-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { login } from '../api/auth'

const router = useRouter()
const formRef = ref()
const loading = ref(false)

const form = reactive({
  username: '',
  password: ''
})

const rules = {
  username: [{ required: true, message: 'Please enter username', trigger: 'blur' }],
  password: [{ required: true, message: 'Please enter password', trigger: 'blur' }]
}

async function handleLogin() {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    loading.value = true
    try {
      const res = await login(form)
      if (res.success) {
        sessionStorage.setItem('user', JSON.stringify(res.data))
        ElMessage.success('Login successful')
        router.push('/')
      }
    } catch (error) {
      console.error('Login failed:', error)
    } finally {
      loading.value = false
    }
  })
}

function goToRegister() {
  router.push('/register')
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-wrapper {
  display: flex;
  max-width: 880px;
  width: 100%;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
  overflow: hidden;
}

/* ===== Left: Intro Panel ===== */
.intro-panel {
  flex: 1;
  padding: 40px 36px;
  background: #f8f9fc;
  border-right: 1px solid #eef0f5;
}

.intro-title {
  margin: 0 0 6px 0;
  font-size: 22px;
  color: #333;
  font-weight: 700;
}

.intro-tagline {
  margin: 0 0 16px 0;
  font-size: 13px;
  color: #764ba2;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.intro-divider {
  width: 40px;
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 2px;
  margin-bottom: 18px;
}

.intro-desc {
  font-size: 13.5px;
  line-height: 1.7;
  color: #555;
  margin: 0 0 20px 0;
}

.intro-features {
  list-style: none;
  padding: 0;
  margin: 0 0 20px 0;
}

.intro-features li {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 13px;
  color: #444;
  padding: 6px 0;
  line-height: 1.5;
}

.feature-icon {
  flex-shrink: 0;
  font-size: 15px;
}

.intro-example {
  font-size: 12.5px;
  color: #777;
  padding: 10px 12px;
  background: #eef1f8;
  border-radius: 6px;
  line-height: 1.6;
}

.example-label {
  font-weight: 600;
  color: #555;
}

/* ===== Right: Login Box ===== */
.login-box {
  width: 360px;
  flex-shrink: 0;
  padding: 40px 36px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-box h2 {
  text-align: center;
  margin: 0 0 10px 0;
  font-size: 24px;
  color: #333;
}

.subtitle {
  text-align: center;
  color: #666;
  margin: 0 0 30px 0;
}

.login-btn {
  width: 100%;
  margin-top: 10px;
}

.footer {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.footer span {
  margin-right: 8px;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .login-wrapper {
    flex-direction: column;
  }
  .intro-panel {
    border-right: none;
    border-bottom: 1px solid #eef0f5;
    padding: 24px 20px;
  }
  .login-box {
    width: 100%;
    padding: 28px 20px;
  }
}
</style>
