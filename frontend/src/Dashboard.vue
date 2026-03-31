<template>
  <div class="dashboard">
    <h1 class="page-title">Dashboard</h1>
    
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card class="stat-card" @click="goToTemplates">
          <div class="stat-content">
            <el-icon class="stat-icon" color="#409eff"><Document /></el-icon>
            <div class="stat-info">
              <div class="stat-value">{{ stats.templates_count }}</div>
              <div class="stat-label">Templates</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" @click="goToBatches">
          <div class="stat-content">
            <el-icon class="stat-icon" color="#67c23a"><Files /></el-icon>
            <div class="stat-info">
              <div class="stat-value">{{ stats.batches_count }}</div>
              <div class="stat-label">Batches</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" @click="goToBatches">
          <div class="stat-content">
            <el-icon class="stat-icon" color="#e6a23c"><Finished /></el-icon>
            <div class="stat-info">
              <div class="stat-value">{{ stats.pdfs_generated }}</div>
              <div class="stat-label">PDFs Generated</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card" @click="goToLogs">
          <div class="stat-content">
            <el-icon class="stat-icon" color="#f56c6c"><Message /></el-icon>
            <div class="stat-info">
              <div class="stat-value">{{ stats.emails_sent }}</div>
              <div class="stat-label">Emails Sent</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="12">
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
      <el-col :span="12">
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getDashboardStats } from '../api/dashboard'

const router = useRouter()
const loading = ref(false)
const stats = ref({
  templates_count: 0,
  batches_count: 0,
  pdfs_generated: 0,
  emails_sent: 0,
  recent_batches: [],
  recent_logs: [],
  is_admin: false
})

onMounted(() => {
  loadStats()
})

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
}

.page-title {
  margin: 0 0 20px 0;
  font-size: 28px;
  color: #333;
}

.stats-row {
  margin-bottom: 20px;
}

.stat-card {
  cursor: pointer;
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-icon {
  font-size: 48px;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #333;
}

.stat-label {
  font-size: 14px;
  color: #666;
  margin-top: 5px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
