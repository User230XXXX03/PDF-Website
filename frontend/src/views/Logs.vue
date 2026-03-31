<template>
  <div class="logs-page">
    <div class="page-header">
      <h1 class="page-title">System Logs</h1>
      <el-button type="success" @click="exportLogs">
        <el-icon><Download /></el-icon>
        Export Logs
      </el-button>
    </div>

    <el-card>
      <div class="filter-bar">
        <el-select
          v-model="filterType"
          placeholder="Log Type"
          class="filter-type-select"
          clearable
          @change="loadLogs"
        >
          <el-option label="PDF Generation" value="generation" />
          <el-option label="Email Sent" value="email" />
          <el-option label="Error" value="error" />
        </el-select>

        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="to"
          start-placeholder="Start Date"
          end-placeholder="End Date"
          class="filter-date-picker"
          @change="loadLogs"
        />

        <el-input
          v-model="searchText"
          placeholder="Search log messages"
          class="filter-search-input"
          clearable
          @clear="loadLogs"
          @keyup.enter="loadLogs"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>

        <el-button type="primary" @click="loadLogs">
          <el-icon><Refresh /></el-icon>
          Refresh
        </el-button>
      </div>

      <el-table :data="logs" v-loading="loading" style="margin-top: 20px">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="type" label="Type" width="120">
          <template #default="{ row }">
            <el-tag :type="getTypeTag(row.type)">
              {{ getTypeName(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="message" label="Message" min-width="200" show-overflow-tooltip />
        <el-table-column prop="recipient_name" label="Recipient" width="120">
          <template #default="{ row }">
            <span v-if="row.recipient_name">{{ row.recipient_name }}</span>
            <span v-else style="color: #999">-</span>
          </template>
        </el-table-column>
        <el-table-column prop="recipient_email" label="Email" width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <span v-if="row.recipient_email">{{ row.recipient_email }}</span>
            <span v-else style="color: #999">-</span>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="Time" width="180" />
        <el-table-column label="Actions" width="120" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="viewDetails(row)">
              Details
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadLogs"
          @current-change="loadLogs"
        />
      </div>
    </el-card>

    <!-- Details Dialog -->
    <el-dialog
      v-model="detailsVisible"
      title="Log Details"
      width="700px"
      class="log-detail-dialog"
    >
      <el-descriptions :column="1" border>
        <el-descriptions-item label="ID">{{ currentLog.id }}</el-descriptions-item>
        <el-descriptions-item label="Type">
          <el-tag :type="getTypeTag(currentLog.type)">
            {{ getTypeName(currentLog.type) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="User ID">{{ currentLog.user_id || '-' }}</el-descriptions-item>
        <el-descriptions-item label="Batch ID">
          <el-link 
            v-if="currentLog.batch_id" 
            type="primary" 
            @click="viewBatch(currentLog.batch_id)"
          >
            <el-icon><Link /></el-icon>
            #{{ currentLog.batch_id }} (Click to view batch detail)
          </el-link>
          <el-tag v-else size="small" type="info">No associated batch</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="Record ID">{{ currentLog.record_id || '-' }}</el-descriptions-item>
        <el-descriptions-item label="Message">{{ currentLog.message }}</el-descriptions-item>
        <el-descriptions-item label="Recipient Name">{{ currentLog.recipient_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="Recipient Email">{{ currentLog.recipient_email || '-' }}</el-descriptions-item>
        <el-descriptions-item label="Time">{{ currentLog.created_at }}</el-descriptions-item>
        <el-descriptions-item label="Details Info">
          <pre v-if="currentLog.details" style="margin: 0; max-height: 300px; overflow: auto;">{{ formatDetails(currentLog.details) }}</pre>
          <span v-else>None</span>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { getLogs } from '../api/logs'

const router = useRouter()
const loading = ref(false)
const logs = ref([])
const searchText = ref('')
const filterType = ref('')
const dateRange = ref([])
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

const detailsVisible = ref(false)
const currentLog = ref({})

onMounted(() => {
  loadLogs()
})

async function loadLogs() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      page_size: pageSize.value
    }
    
    if (filterType.value) {
      params.type = filterType.value
    }
    
    if (searchText.value) {
      params.search = searchText.value
    }
    
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }
    
    const res = await getLogs(params)
    if (res.success && res.data) {
      logs.value = res.data.logs || []
      total.value = res.data.total || 0
    }
  } catch (error) {
    console.error('Failed to load logs:', error)
    ElMessage.error('Failed to load logs')
  } finally {
    loading.value = false
  }
}

function getTypeTag(type) {
  const tagMap = {
    generation: 'success',
    email: 'primary',
    error: 'danger'
  }
  return tagMap[type] || 'info'
}

function getTypeName(type) {
  const nameMap = {
    generation: 'PDF Generation',
    email: 'Email Sent',
    error: 'Error'
  }
  return nameMap[type] || type
}

function viewDetails(log) {
  currentLog.value = log
  detailsVisible.value = true
}

function viewBatch(batchId) {
  router.push(`/batches/detail/${batchId}`)
}

function formatDetails(details) {
  if (typeof details === 'string') {
    try {
      details = JSON.parse(details)
    } catch (e) {
      return details
    }
  }
  return JSON.stringify(details, null, 2)
}

async function exportLogs() {
  try {
    const params = {
      export: 'csv'
    }
    
    if (filterType.value) {
      params.type = filterType.value
    }
    
    if (searchText.value) {
      params.search = searchText.value
    }
    
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }
    
    const queryString = new URLSearchParams(params).toString()
    const url = `/api/logs.php?${queryString}`
    
    // Create hidden anchor tag to trigger download
    const a = document.createElement('a')
    a.href = url
    a.download = `logs_${new Date().getTime()}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    
    ElMessage.success('Success')
  } catch (error) {
    console.error('Failed to export logs:', error)
    ElMessage.error('Export logs failed')
  }
}
</script>

<style scoped>
.logs-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-title {
  margin: 0;
  font-size: 28px;
  color: #333;
}

.filter-bar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-type-select {
  width: 150px;
}

.filter-date-picker {
  width: 300px;
}

.filter-search-input {
  width: 250px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 768px) {
  .logs-page {
    padding: 12px;
  }

  .filter-type-select,
  .filter-date-picker,
  .filter-search-input {
    width: 100%;
  }

  .pagination {
    justify-content: center;
  }
}
</style>
