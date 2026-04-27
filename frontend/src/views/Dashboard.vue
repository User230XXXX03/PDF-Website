<template>
  <div class="dashboard">

    <!-- ========== Welcome Banner ========== -->
    <div class="welcome-banner">
      <div class="welcome-left">
        <div class="welcome-greeting">
          <span class="greeting-emoji">{{ greetingEmoji }}</span>
          <div>
            <h1 class="welcome-title">{{ greeting }}, {{ username }}!</h1>
            <p class="welcome-subtitle">Welcome back to PDF Generator System — your one-stop platform for PDF generation and email distribution</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== Statistics Cards ========== -->
    <el-row :gutter="20" class="stats-row">
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card class="stat-card" @click="goToTemplates">
          <div class="stat-content">
            <div class="stat-icon-wrapper" style="background: rgba(64,158,255,0.1);">
              <el-icon class="stat-icon" color="#409eff"><Document /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.templates_count }}</div>
              <div class="stat-label">Templates</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card class="stat-card" @click="goToBatches">
          <div class="stat-content">
            <div class="stat-icon-wrapper" style="background: rgba(103,194,58,0.1);">
              <el-icon class="stat-icon" color="#67c23a"><Files /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.batches_count }}</div>
              <div class="stat-label">Batches</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card class="stat-card" @click="goToBatches">
          <div class="stat-content">
            <div class="stat-icon-wrapper" style="background: rgba(230,162,60,0.1);">
              <el-icon class="stat-icon" color="#e6a23c"><Finished /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.pdfs_generated }}</div>
              <div class="stat-label">PDFs Generated</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card class="stat-card" @click="goToLogs">
          <div class="stat-content">
            <div class="stat-icon-wrapper" style="background: rgba(245,108,108,0.1);">
              <el-icon class="stat-icon" color="#f56c6c"><Message /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.emails_sent }}</div>
              <div class="stat-label">Emails Sent</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- ========== Getting Started Guide ========== -->
    <el-card class="guide-card" v-if="showGuide">
      <template #header>
        <div class="card-header">
          <div class="guide-header-left">
            <el-icon style="color: #409eff; font-size: 20px;"><InfoFilled /></el-icon>
            <span class="guide-title">Quick Start Guide</span>
          </div>
          <el-button text @click="hideGuide">
            <el-icon><Close /></el-icon>
            Hide
          </el-button>
        </div>
      </template>
      <el-steps :active="guideStep" align-center finish-status="success" class="guide-steps">
        <el-step title="Create Template" description="Design your PDF template using HTML and placeholder variables" @click.native="navigateGuideStep(0, '/templates/create')" class="clickable-step" />
        <el-step title="Create Batch" description="Create a new batch and import CSV / Excel data files" @click.native="navigateGuideStep(1, '/batches/create')" class="clickable-step" />
        <el-step title="Generate PDFs" description="The system automatically generates PDF files based on templates and data" @click.native="navigateGuideStep(2, '/batches')" class="clickable-step" />
        <el-step title="Send Emails" description="Send the generated PDFs via email to the corresponding recipients" @click.native="navigateGuideStep(3, '/batches')" class="clickable-step" />
      </el-steps>
      <div class="guide-actions">
        <el-button v-if="guideStep > 0 && guideStep < 4" @click="guideStep--">Previous</el-button>
        <el-button type="primary" v-if="guideStep < 3" @click="guideStep++">Next</el-button>
        <el-button type="success" v-if="guideStep === 3" @click="guideCompleted">
          <el-icon><CircleCheckFilled /></el-icon>
          Got It!
        </el-button>
        <el-button v-if="guideStep === 4" @click="restoreGuide">Restart Guide</el-button>
        <el-button type="success" v-if="guideStep === 4" @click="router.push('/templates/create')">
          <el-icon><Plus /></el-icon>
          Start Your First Step
        </el-button>
        <el-button type="primary" v-if="guideStep === 4" @click="hideGuide">Close</el-button>
      </div>
    </el-card>

    <!-- ========== Show Guide Button (when hidden) ========== -->
    <div v-if="!showGuide" class="guide-toggle">
      <el-button text type="primary" @click="restoreGuide">
        <el-icon><InfoFilled /></el-icon>
        Show Quick Start Guide
      </el-button>
    </div>

    <!-- ========== Recent Batches & Logs ========== -->
    <el-row :gutter="20">
      <el-col :xs="24" :sm="24" :md="12" :lg="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>Recent Batches</span>
              <el-button type="primary" size="small" @click="goToBatches">View All</el-button>
            </div>
          </template>
          <el-table :data="stats.recent_batches" v-loading="loading">
            <el-table-column prop="name" label="Batch Name" />
            <el-table-column prop="template_name" label="Template" />
            <el-table-column v-if="stats.is_admin" prop="username" label="User" width="100" />
            <el-table-column prop="status" label="Status">
              <template #default="{ row }">
                <el-tag :type="getStatusType(row.status)">{{ row.status }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="Created" width="180" />
          </el-table>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>Recent Logs</span>
              <el-button type="primary" size="small" @click="goToLogs">View All</el-button>
            </div>
          </template>
          <el-timeline v-loading="loading">
            <el-timeline-item
              v-for="log in stats.recent_logs"
              :key="log.id"
              :timestamp="log.created_at"
              placement="top"
              :type="getLogType(log.type)"
            >
              <template v-if="stats.is_admin && log.username">
                <strong>[{{ log.username }}]</strong> {{ log.message }}
              </template>
              <template v-else>
                {{ log.message }}
              </template>
            </el-timeline-item>
          </el-timeline>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getDashboardStats } from '../api/dashboard'
import { getCurrentUser } from '../api/auth'

const router = useRouter()
const loading = ref(false)
const username = ref('User')
const guideStep = ref(0)
const showGuide = ref(true)

const stats = ref({
  templates_count: 0,
  batches_count: 0,
  pdfs_generated: 0,
  emails_sent: 0,
  recent_batches: [],
  recent_logs: [],
  is_admin: false
})

// ---------- Greeting Logic ----------
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 6) return 'Good evening'
  if (hour < 12) return 'Good morning'
  if (hour < 14) return 'Good afternoon'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
})

const greetingEmoji = computed(() => {
  const hour = new Date().getHours()
  if (hour < 6) return '🌙'
  if (hour < 12) return '🌅'
  if (hour < 14) return '☀️'
  if (hour < 18) return '🌤️'
  return '🌆'
})

// ---------- Guide Visibility ----------
function hideGuide() {
  showGuide.value = false
  localStorage.setItem('dashboard_guide_hidden', 'true')
}

function restoreGuide() {
  showGuide.value = true
  guideStep.value = 0
  localStorage.removeItem('dashboard_guide_hidden')
}

function guideCompleted() {
  guideStep.value = 4 // Mark as completed
}

function navigateGuideStep(step, path) {
  guideStep.value = step
  router.push(path)
}

// ---------- Lifecycle ----------
onMounted(async () => {
  // Restore guide visibility preference
  if (localStorage.getItem('dashboard_guide_hidden') === 'true') {
    showGuide.value = false
  }
  await Promise.all([loadUserInfo(), loadStats()])
})

async function loadUserInfo() {
  try {
    const res = await getCurrentUser()
    if (res.success && res.data) {
      username.value = res.data.username
    }
  } catch (error) {
    // Fallback: try sessionStorage
    try {
      const user = JSON.parse(sessionStorage.getItem('user'))
      if (user?.username) username.value = user.username
    } catch (e) { /* ignore */ }
  }
}

async function loadStats() {
  loading.value = true
  try {
    const res = await getDashboardStats()
    if (res.success && res.data) {
      stats.value = res.data
    }
  } catch (error) {
    console.error('Failed to load stats:', error)
  } finally {
    loading.value = false
  }
}

function getStatusType(status) {
  const types = {
    pending: 'info',
    processing: 'warning',
    completed: 'success',
    failed: 'danger'
  }
  return types[status] || 'info'
}

function getLogType(type) {
  const types = {
    generation: 'success',
    email: 'primary',
    error: 'danger'
  }
  return types[type] || 'info'
}

function goToTemplates() {
  router.push('/templates')
}

function goToBatches() {
  router.push('/batches')
}

function goToLogs() {
  router.push('/logs')
}
</script>

<style scoped>
.dashboard {
  padding: 20px;
  margin: 0 auto;
}

/* ========== Welcome Banner ========== */
.welcome-banner {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 28px 32px;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.welcome-left {
  flex: 1;
}

.welcome-greeting {
  display: flex;
  align-items: center;
  gap: 16px;
}

.greeting-emoji {
  font-size: 40px;
  line-height: 1;
}

.welcome-title {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.welcome-subtitle {
  margin: 6px 0 0 0;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.5;
}

.welcome-date {
  margin: 16px 0 0 0;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  gap: 6px;
}

.welcome-right {
  margin-left: 32px;
}

/* ========== Quick Actions ========== */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.quick-action-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
  font-size: 14px;
}

.quick-action-item:hover {
  background: rgba(255, 255, 255, 0.25);
}

.qa-icon {
  font-size: 22px;
  color: white !important;
}

/* ========== Statistics Cards ========== */
.stats-row {
  margin-bottom: 24px;
}

.stat-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  border: none;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon {
  font-size: 28px;
}

.stat-value {
  font-size: 30px;
  font-weight: 700;
  color: var(--text-primary, #1a1a2e);
  line-height: 1.2;
}

.stat-label {
  font-size: 13px;
  color: var(--text-muted, #999);
  margin-top: 4px;
}

/* ========== Getting Started Guide ========== */
.guide-card {
  margin-bottom: 24px;
  border-radius: 12px;
  border: 1px solid var(--border-color, #e4e7ed);
}

.guide-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.guide-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary, #333);
}

.guide-steps {
  margin: 16px 0 24px 0;
}

.clickable-step {
  cursor: pointer;
}

.clickable-step:hover :deep(.el-step__title) {
  color: #409eff !important;
}

.clickable-step:hover :deep(.el-step__description) {
  color: #409eff !important;
}

.guide-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  padding-top: 8px;
  border-top: 1px solid var(--border-color, #f0f0f0);
}

.guide-toggle {
  margin-bottom: 20px;
  text-align: left;
}

/* ========== Common ========== */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

@media (max-width: 768px) {
  .dashboard {
    padding: 12px;
  }

  .welcome-banner {
    padding: 18px 16px;
    flex-direction: column;
    align-items: flex-start;
  }

  .welcome-greeting {
    gap: 10px;
  }

  .greeting-emoji {
    font-size: 28px;
  }

  .welcome-title {
    font-size: 18px;
  }

  .welcome-subtitle {
    font-size: 13px;
  }

  .welcome-right {
    margin-left: 0;
    margin-top: 12px;
  }

  .quick-actions {
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .stat-icon-wrapper {
    width: 44px;
    height: 44px;
  }

  .stat-icon {
    font-size: 22px;
  }

  .stat-value {
    font-size: 22px;
  }

  .guide-actions {
    flex-wrap: wrap;
  }
}
</style>
