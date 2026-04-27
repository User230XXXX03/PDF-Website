<template>
  <div class="batch-detail">
    <div class="page-header">
      <h1 class="page-title">Batch Detail</h1>
      <div>
        <el-button @click="goBack">Back</el-button>
        <el-button
          type="success"
          @click="handleGenerateAll"
          :loading="generating"
          :disabled="!canGenerate"
        >
          <el-icon><Document /></el-icon>
          Generate All PDFs
        </el-button>
        <el-button
          type="primary"
          @click="handleSendAll"
          :loading="sending"
          :disabled="!canSendEmail"
        >
          <el-icon><Message /></el-icon>
          Send All Emails
        </el-button>
      </div>
    </div>

    <el-card v-loading="loading">
      <div class="batch-info">
        <el-descriptions :column="responsiveDescColumn" border>
          <el-descriptions-item label="Batch Name">{{ batch.name }}</el-descriptions-item>
          <el-descriptions-item label="Template">{{ batch.template_name }}</el-descriptions-item>
          <el-descriptions-item label="Type">
            <el-tag>{{ batch.set_type }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Status">
            <el-tag :type="getStatusType(batch.status)">{{ batch.status }}</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="Total Records">{{ batch.total_count }}</el-descriptions-item>
          <el-descriptions-item label="PDFs Generated">{{ batch.generated_count }}</el-descriptions-item>
          <el-descriptions-item label="Emails Sent">{{ batch.sent_count }}</el-descriptions-item>
          <el-descriptions-item label="Created">{{ batch.created_at }}</el-descriptions-item>
          <el-descriptions-item label="Updated">{{ batch.updated_at }}</el-descriptions-item>
        </el-descriptions>
      </div>

      <el-divider />

      <h3>Records</h3>
      
      <!-- Email validation warning -->
      <el-alert
        v-if="batch.records && batch.records.length > 0 && !allRecordsHaveEmail"
        type="warning"
        :closable="false"
        style="margin-bottom: 15px;"
      >
        <template #title>
          ⚠️ Some records are missing email addresses
        </template>
        <p>The following records are missing email addresses and cannot receive emails. Please add email addresses in the edit page:</p>
        <ul style="margin: 10px 0; padding-left: 20px;">
          <li v-for="(record, index) in recordsWithoutEmail" :key="record.id">
            Record #{{ index + 1 }}: {{ getPersonName(record) || 'Unnamed' }}
          </li>
        </ul>
      </el-alert>
      
      <el-table :data="batch.records" border style="margin-top: 15px">
        <el-table-column :label="nameColumnLabel" width="150">
          <template #default="{ row }">
            <span v-if="getPersonName(row)">{{ getPersonName(row) }}</span>
            <el-tag v-else type="warning" size="small">Not Set</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Email" width="250">
          <template #default="{ row }">
            <span v-if="getPersonEmail(row)">{{ getPersonEmail(row) }}</span>
            <el-tag v-else type="danger" size="small">⚠️ Missing Email</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Data" min-width="300">
          <template #default="{ row }">
            <div v-if="row.data">
              <el-tag
                v-for="(value, key) in row.data"
                :key="key"
                size="small"
                style="margin: 2px"
              >
                {{ key }}: {{ value }}
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="Status" width="120">
          <template #default="{ row }">
            <div>
              <el-tag v-if="row.pdf_generated" type="success" size="small">PDF ✓</el-tag>
              <el-tag v-else type="info" size="small">No PDF</el-tag>
            </div>
            <div style="margin-top: 5px">
              <el-tag v-if="row.email_sent" type="success" size="small">Email ✓</el-tag>
              <el-tag v-else type="info" size="small">No Email</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="240" fixed="right">
          <template #default="{ row }">
            <el-button
              size="small"
              @click="handleGenerateOne(row)"
              :disabled="processing[row.id]"
            >
              Generate
            </el-button>
            <el-button
              v-if="row.pdf_generated"
              type="primary"
              size="small"
              @click="handlePreview(row)"
            >
              Preview
            </el-button>
            <el-button
              v-if="row.pdf_generated"
              type="success"
              size="small"
              @click="handleDownload(row)"
            >
              Download
            </el-button>
            <el-button
              v-if="row.pdf_generated && getPersonEmail(row)"
              type="warning"
              size="small"
              @click="handleSendOne(row)"
              :disabled="processing[row.id]"
            >
              Send
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { getBatch } from '../api/batch'
import { generateBatchPDFs, generateRecordPDF, getPreviewUrl, getDownloadUrl } from '../api/pdf'
import { sendBatchEmails, sendRecordEmail } from '../api/email'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const generating = ref(false)
const sending = ref(false)
const processing = reactive({})

const batch = ref({
  records: []
})

const windowWidth = ref(window.innerWidth)

function handleResize() {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const responsiveDescColumn = computed(() => {
  if (windowWidth.value < 768) return 1
  if (windowWidth.value < 1024) return 2
  return 3
})

const canGenerate = computed(() => {
  return batch.value.total_count > 0 && !generating.value
})

// Return column name based on template type
const nameColumnLabel = computed(() => {
  const templateType = batch.value.set_type
  switch(templateType) {
    case 'Transcript':
      return 'Student Name'
    case 'Payroll':
      return 'Employee Name'
    case 'Certificate':
      return 'Recipient Name'
    default:
      return 'Name'
  }
})

// Get name variable name based on template type
function getNameVariableName() {
  const templateType = batch.value.set_type
  switch(templateType) {
    case 'Transcript':
      return 'STUDENT_NAME'
    case 'Payroll':
      return 'EMPLOYEE_NAME'
    case 'Certificate':
      return 'USER_FULL_NAME'
    default:
      // Try common name variables
      return null
  }
}

// Get email variable name based on template type
function getEmailVariableName() {
  const templateType = batch.value.set_type
  switch(templateType) {
    case 'Transcript':
      return 'STUDENT_EMAIL'
    case 'Payroll':
      return 'EMPLOYEE_EMAIL'
    case 'Certificate':
      return 'USER_EMAIL'
    default:
      return null
  }
}

// Get person name from record (dynamically based on template type)
function getPersonName(record) {
  if (!record.data) {
    return record.student_name || ''
  }
  
  const variableName = getNameVariableName()
  if (variableName && record.data[variableName]) {
    return record.data[variableName]
  }
  
  // Try other common name variables
  const possibleNames = ['STUDENT_NAME', 'EMPLOYEE_NAME', 'USER_FULL_NAME', 'NAME', 'RECIPIENT_NAME']
  for (const name of possibleNames) {
    if (record.data[name]) {
      return record.data[name]
    }
  }
  
  // Fallback to old student_name field
  return record.student_name || ''
}

// Get person email from record (dynamically based on template type)
function getPersonEmail(record) {
  if (!record.data) {
    return record.student_email || ''
  }
  
  const variableName = getEmailVariableName()
  if (variableName && record.data[variableName]) {
    return record.data[variableName]
  }
  
  // Try other common email variables
  const possibleEmails = ['STUDENT_EMAIL', 'EMPLOYEE_EMAIL', 'USER_EMAIL', 'EMAIL', 'RECIPIENT_EMAIL']
  for (const email of possibleEmails) {
    if (record.data[email]) {
      return record.data[email]
    }
  }
  
  // Fallback to old student_email field
  return record.student_email || ''
}

const canSendEmail = computed(() => {
  // Must have generated PDFs
  if (batch.value.generated_count === 0 || sending.value) {
    return false
  }
  
  // Check if all records have valid email addresses
  const records = batch.value.records || []
  if (records.length === 0) {
    return false
  }
  
  // All records must have email and PDF generated
  const allHaveValidEmail = records.every(record => {
    const email = getPersonEmail(record)
    return email && email.trim() !== '' && record.pdf_generated
  })
  
  return allHaveValidEmail
})

// Check if all records have email
const allRecordsHaveEmail = computed(() => {
  const records = batch.value.records || []
  if (records.length === 0) return true
  
  return records.every(record => {
    const email = getPersonEmail(record)
    return email && email.trim() !== ''
  })
})

// Get records without email
const recordsWithoutEmail = computed(() => {
  const records = batch.value.records || []
  return records.filter(record => {
    const email = getPersonEmail(record)
    return !email || email.trim() === ''
  })
})

onMounted(() => {
  loadBatch()
})

async function loadBatch() {
  loading.value = true
  try {
    const res = await getBatch(route.params.id)
    if (res.success && res.data) {
      batch.value = res.data
    }
  } catch (error) {
    console.error('Failed to load batch:', error)
    ElMessage.error('Failed to load batch')
  } finally {
    loading.value = false
  }
}

async function handleGenerateAll() {
  generating.value = true
  try {
    const res = await generateBatchPDFs(route.params.id)
    if (res.success) {
      ElMessage.success(`Generated ${res.data.success_count} PDFs successfully`)
      if (res.data.fail_count > 0) {
        ElMessage.warning(`${res.data.fail_count} PDFs failed to generate`)
      }
      await loadBatch()
    }
  } catch (error) {
    console.error('Failed to generate PDFs:', error)
  } finally {
    generating.value = false
  }
}

async function handleGenerateOne(record) {
  processing[record.id] = true
  try {
    const res = await generateRecordPDF(record.id)
    if (res.success) {
      ElMessage.success('PDF generated successfully')
      await loadBatch()
    }
  } catch (error) {
    console.error('Failed to generate PDF:', error)
  } finally {
    processing[record.id] = false
  }
}

async function handleSendAll() {
  sending.value = true
  try {
    const res = await sendBatchEmails(route.params.id)
    if (res.success) {
      ElMessage.success(`Sent ${res.data.success_count} emails successfully`)
      if (res.data.fail_count > 0) {
        ElMessage.warning(`${res.data.fail_count} emails failed to send`)
      }
      await loadBatch()
    }
  } catch (error) {
    console.error('Failed to send emails:', error)
  } finally {
    sending.value = false
  }
}

async function handleSendOne(record) {
  processing[record.id] = true
  try {
    const res = await sendRecordEmail(record.id)
    if (res.success) {
      ElMessage.success('Email sent successfully')
      await loadBatch()
    }
  } catch (error) {
    console.error('Failed to send email:', error)
  } finally {
    processing[record.id] = false
  }
}

function handlePreview(record) {
  window.open(getPreviewUrl(record.id), '_blank')
}

function handleDownload(record) {
  window.location.href = getDownloadUrl(record.id)
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

function goBack() {
  router.push('/batches')
}
</script>

<style scoped>
.batch-detail {
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
  color: var(--text-primary, #333);
}

.batch-info {
  margin-bottom: 20px;
}

h3 {
  margin: 20px 0 10px 0;
  color: var(--text-primary, #333);
}

@media (max-width: 768px) {
  .batch-detail {
    padding: 12px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .page-header > div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
}
</style>
