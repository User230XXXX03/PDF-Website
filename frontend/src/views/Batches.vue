<template>
  <div class="batches">
    <div class="page-header">
      <h1 class="page-title">Batches</h1>
      <el-button type="primary" @click="createBatch">
        <el-icon><Plus /></el-icon>
        Create Batch
      </el-button>
    </div>

    <el-card>
      <div class="filter-bar">
        <el-select
          v-model="filterStatus"
          placeholder="Filter"
          class="filter-status"
          clearable
          @change="loadBatches"
        >
          <el-option label="Pending" value="pending" />
          <el-option label="Processing" value="processing" />
          <el-option label="Completed" value="completed" />
          <el-option label="Failed" value="failed" />
        </el-select>
      </div>

      <el-table :data="batches" v-loading="loading" style="margin-top: 20px">
        <el-table-column prop="name" label="Batch Name" min-width="200" />
        <el-table-column prop="template_name" label="Template" min-width="150" />
        <el-table-column prop="set_type" label="Type" width="150">
          <template #default="{ row }">
            <el-tag>{{ row.set_type }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="Status" width="120">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">{{ getStatusName(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Progress" width="150">
          <template #default="{ row }">
            <div>{{ row.generated_count }} / {{ row.total_count }} PDFs</div>
            <div style="font-size: 12px; color: #999">{{ row.sent_count }} Emails Sent</div>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="Created" width="180" />
        <el-table-column label="Actions" width="280" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="viewDetail(row.id)">
              Detail
            </el-button>
            <el-button size="small" @click="editBatch(row.id)">
              Edit
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              Delete
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getBatches, deleteBatch } from '../api/batch'

const router = useRouter()
const loading = ref(false)
const batches = ref([])
const filterStatus = ref('')

onMounted(() => {
  loadBatches()
})

async function loadBatches() {
  loading.value = true
  try {
    const params = {}
    if (filterStatus.value) params.status = filterStatus.value
    
    const res = await getBatches(params)
    if (res.success && res.data) {
      batches.value = res.data
    }
  } catch (error) {
    console.error('Failed to load batches:', error)
  } finally {
    loading.value = false
  }
}

function createBatch() {
  router.push('/batches/create')
}

function editBatch(id) {
  router.push(`/batches/edit/${id}`)
}

function viewDetail(id) {
  router.push(`/batches/detail/${id}`)
}

async function handleDelete(row) {
  try {
    await ElMessageBox.confirm(
      'Are you sure you want to delete batch "' + row.name + '"?',
      'Confirm',
      {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }
    )
    
    loading.value = true
    const res = await deleteBatch(row.id)
    if (res.success) {
      ElMessage.success('Batch deleted successfully')
      loadBatches()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Failed to delete batch:', error)
    }
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

function getStatusName(status) {
  const names = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    failed: 'Failed'
  }
  return names[status] || status
}
</script>

<style scoped>
.batches {
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
  gap: 15px;
  flex-wrap: wrap;
}

.filter-status {
  width: 200px;
}

@media (max-width: 768px) {
  .batches {
    padding: 12px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .filter-status {
    width: 100%;
  }
}
</style>
