<template>
  <div class="settings">
    <h1 class="page-title">Settings</h1>

    <el-card>
      <template #header>
        <span>Email Configuration</span>
      </template>

      <el-form :model="form" :rules="rules" ref="formRef" label-width="180px">
        <el-form-item label="SMTP Host" prop="smtp_host">
          <el-input v-model="form.smtp_host" placeholder="e.g. smtp.163.com" />
        </el-form-item>

        <el-form-item label="SMTP Port" prop="smtp_port">
          <el-input-number v-model="form.smtp_port" :min="1" :max="65535" />
          <div class="hint">Common ports: 25 (SMTP), 465 (SSL), 587 (TLS)</div>
        </el-form-item>

        <el-form-item label="Use SSL/TLS">
          <el-switch
            v-model="form.smtp_secure"
            active-text="Enabled"
            inactive-text="Disabled"
          />
          <div class="hint">Enable for port 465, disable for port 25/587</div>
        </el-form-item>

        <el-form-item label="SMTP Username" prop="smtp_username">
          <el-input v-model="form.smtp_username" placeholder="Your email address" />
          <div class="hint">Usually your full email address</div>
        </el-form-item>

        <el-form-item label="SMTP Password">
          <el-input
            v-model="form.smtp_password"
            type="password"
            placeholder="Enter password or authorization code"
            show-password
          />
          <div class="hint" style="color: #f56c6c; font-weight: bold;">
            Use authorization code, NOT your login password!
          </div>
          <div class="hint">
            Most email providers require an app-specific password or authorization code.
          </div>
        </el-form-item>

        <el-form-item label="From Email" prop="from_email">
          <el-input v-model="form.from_email" placeholder="sender@example.com" />
        </el-form-item>

        <el-form-item label="From Name" prop="from_name">
          <el-input v-model="form.from_name" placeholder="PDF Generator System" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleSave" :loading="loading">
            Save Settings
          </el-button>
          <el-button @click="loadSettings">Reset</el-button>
        </el-form-item>
      </el-form>

      <el-alert
        title="Email Configuration Help"
        type="info"
        :closable="false"
        style="margin-top: 20px"
      >
        <div>
          <p><strong>For Gmail:</strong></p>
          <ul>
            <li><strong>SMTP Host:</strong> smtp.gmail.com</li>
            <li><strong>SMTP Port:</strong> 587 (TLS) or 465 (SSL)</li>
            <li><strong>SSL/TLS:</strong> Enabled</li>
            <li><strong>SMTP Username:</strong> your Gmail address</li>
            <li><strong>SMTP Password:</strong> App Password (⚠️ NOT your login password!)</li>
          </ul>
          <p style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <strong>How to get a Gmail App Password:</strong><br>
            1. Enable "2-Step Verification" in your Google Account<br>
            2. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a><br>
            3. Enter a name (e.g. "PDF Generator") and click "Create"<br>
            4. Copy the generated 16-character password into the SMTP Password field above
          </p>

          <p style="margin-top: 10px"><strong>For Outlook/Hotmail:</strong></p>
          <ul>
            <li>SMTP Host: smtp.office365.com</li>
            <li>SMTP Port: 587</li>
          </ul>

          <p style="margin-top: 10px"><strong>For QQ Mailbox:</strong></p>
          <ul>
            <li>SMTP Host: smtp.qq.com</li>
            <li>SMTP Port: 465 (SSL) or 587 (TLS)</li>
            <li>Need to enable SMTP service and use authorization code</li>
          </ul>
        </div>
      </el-alert>
    </el-card>

    <el-card style="margin-top: 20px">
      <template #header>
        <span>User Profile</span>
      </template>

      <el-descriptions :column="1" border>
        <el-descriptions-item label="Username">{{ userInfo.username }}</el-descriptions-item>
        <el-descriptions-item label="Email">{{ userInfo.email }}</el-descriptions-item>
        <el-descriptions-item label="Joined">{{ userInfo.created_at }}</el-descriptions-item>
      </el-descriptions>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getEmailSettings, updateEmailSettings } from '../api/email'
import { getCurrentUser } from '../api/auth'
const formRef = ref()
const loading = ref(false)

const form = reactive({
  smtp_host: '',
  smtp_port: 587,
  smtp_secure: false,
  smtp_username: '',
  smtp_password: '',
  from_email: '',
  from_name: ''
})

const userInfo = ref({
  username: '',
  email: '',
  created_at: ''
})

const rules = {
  smtp_host: [{ required: true, message: 'SMTP Host is required', trigger: 'blur' }],
  smtp_port: [{ required: true, message: 'SMTP Port is required', trigger: 'blur' }],
  smtp_username: [{ required: true, message: 'SMTP Username is required', trigger: 'blur' }],
  from_email: [
    { required: true, message: 'From Email is required', trigger: 'blur' },
    { type: 'email', message: 'Please enter a valid email', trigger: 'blur' }
  ],
  from_name: [{ required: true, message: 'From Name is required', trigger: 'blur' }]
}

onMounted(async () => {
  await loadUserInfo()
  await loadSettings()
})

async function loadUserInfo() {
  try {
    const res = await getCurrentUser()
    if (res.success && res.data) {
      userInfo.value = res.data
    }
  } catch (error) {
    console.error('Failed to load user info:', error)
  }
}

async function loadSettings() {
  loading.value = true
  try {
    const res = await getEmailSettings()
    if (res.success && res.data && res.data.id) {
      Object.assign(form, res.data)
      form.smtp_password = '' // Don't display password
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    loading.value = true
    try {
      const data = { ...form }
      // If password is empty, don't send it (for update)
      if (!data.smtp_password) {
        delete data.smtp_password
      }
      
      const res = await updateEmailSettings(data)
      if (res.success) {
        ElMessage.success('Settings saved successfully')
        await loadSettings()
      }
    } catch (error) {
      console.error('Failed to save settings:', error)
      ElMessage.error('Failed to save settings')
    } finally {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.settings {
  padding: 20px;
}

.page-title {
  margin: 0 0 20px 0;
  font-size: 28px;
  color: #333;
}

.hint {
  margin-top: 5px;
  font-size: 12px;
  color: #999;
}

@media (max-width: 768px) {
  .settings {
    padding: 12px;
  }

  :deep(.el-form-item__label) {
    float: none;
    display: block;
    text-align: left;
    padding: 0 0 6px 0;
  }

  :deep(.el-form-item__content) {
    margin-left: 0 !important;
  }
}
</style>
