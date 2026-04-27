<template>
  <div class="batch-editor">
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Edit Batch' : 'Create Batch' }}</h1>
      <div>
        <el-button @click="goBack">Cancel</el-button>
        <el-button type="primary" @click="handleSave" :loading="loading">
          Save
        </el-button>
      </div>
    </div>

    <el-card>
      <el-form :model="form" :rules="rules" ref="formRef" label-width="120px">
        <el-form-item label="Batch Name" prop="name">
          <el-input v-model="form.name" placeholder="Batch Name" />
        </el-form-item>

        <el-form-item label="Template" prop="template_id">
          <el-select
            v-model="form.template_id"
            placeholder="Select template"
            style="width: 100%"
            @change="handleTemplateChange"
          >
            <el-option
              v-for="template in templates"
              :key="template.id"
              :label="`${template.name} (${template.set_type})`"
              :value="template.id"
            />
          </el-select>
        </el-form-item>

        <el-divider content-position="left">Email Configuration</el-divider>
        
        <el-alert type="info" :closable="false" style="margin-bottom: 15px;">
          <p><strong>💡 Instructions:</strong></p>
          <ul style="margin: 5px 0; padding-left: 20px;">
            <li><strong>Personalized Sending:</strong> System will automatically get each person's email from the email field in each data record</li>
            <li><strong>Subject/Content:</strong> Supports template variables like {{STUDENT_NAME}}, {{CLASS_NAME}}, etc.</li>
          </ul>
        </el-alert>


        <el-form-item label="Email Subject" prop="email_config.subject">
          <div class="email-field-group">
            <div class="email-field-row">
              <el-input
                v-model="form.email_config.subject"
                placeholder="e.g.: Your Student Transcript - {{STUDENT_NAME}}"
                class="email-main-input"
              />
              <el-select
                v-model="selectedVariableForSubject"
                placeholder="Insert Variable"
                @change="insertVariable('subject', $event)"
                clearable
                class="email-variable-select"
                :disabled="!selectedTemplate"
              >
                <el-option
                  v-for="variable in availableVariables"
                  :key="variable"
                  :label="`{{${variable}}}`"
                  :value="variable"
                />
              </el-select>
            </div>
            <div class="field-hint">
              💡 Tip: You can type manually or use the dropdown on the right to insert template variables
            </div>
          </div>
        </el-form-item>

        <el-form-item label="Email Body" prop="email_config.body">
          <div class="email-field-group">
            <div class="email-field-row email-field-row--body">
              <el-input
                v-model="form.email_config.body"
                type="textarea"
                :rows="6"
                placeholder="e.g.: Dear {{STUDENT_NAME}},&#10;&#10;Please find your transcript for {{SEMESTER}} attached.&#10;&#10;Best regards"
                class="email-main-input email-body-input"
              />
              <el-select
                v-model="selectedVariableForBody"
                placeholder="Insert Variable"
                @change="insertVariable('body', $event)"
                clearable
                class="email-variable-select"
                :disabled="!selectedTemplate"
              >
                <el-option
                  v-for="variable in availableVariables"
                  :key="variable"
                  :label="`{{${variable}}}`"
                  :value="variable"
                />
              </el-select>
            </div>
            <div class="field-hint">
              💡 Tip: The generated PDF will be automatically attached to the email. You can type manually or use the dropdown to insert variables
            </div>
          </div>
        </el-form-item>

        <el-divider content-position="left">Data Records</el-divider>

        <el-form-item label="Records" v-if="selectedTemplate">
          <div class="records-section">
            <div class="records-toolbar">
              <div class="toolbar-left">
                <el-button size="small" @click="addRecord">
                  <el-icon><Plus /></el-icon>
                  Add Record
                </el-button>
                <el-button type="primary" size="small" @click="downloadTemplateFile('excel')">
                  Download Excel Template
                </el-button>
                <el-button type="primary" size="small" @click="downloadTemplateFile('csv')">
                  Download CSV Template
                </el-button>
                <el-button type="success" size="small" @click="handleImportClick('excel')">
                  Import Excel
                </el-button>
                <el-button type="success" size="small" @click="handleImportClick('csv')">
                  Import CSV
                </el-button>
                <el-button size="small" type="success" @click="generateTestData">
                  <el-icon><MagicStick /></el-icon>
                  Generate Test Data(Transcript)
                </el-button>
                <el-button size="small" type="warning" @click="clearAllRecords" :disabled="form.records.length === 0">
                  <el-icon><Delete /></el-icon>
                  Clear All
                </el-button>
              </div>
              <span class="record-count">{{ form.records.length + ' records' }}</span>
            </div>
            <input
              ref="fileInputRef"
              type="file"
              accept=".xlsx,.xls,.csv"
              style="display: none"
              @change="handleFileImport"
            />

            <!-- Data Records Table (unified display) -->
            <el-table :data="form.records" border style="margin-top: 10px">
              <!-- Index Column -->
              <el-table-column label="#" type="index" width="50" fixed="left" />
              
              <!-- Recipient Email Column (always visible if template has no email variable) -->
              <el-table-column
                v-if="!hasEmailVariable"
                label="Recipient Email"
                min-width="200"
              >
                <template #default="{ row }">
                  <el-input
                    v-model="row._recipient_email"
                    placeholder="Enter recipient email"
                    size="small"
                  />
                </template>
              </el-table-column>

              <!-- Data Field Columns -->
              <el-table-column
                v-for="variable in selectedTemplate.variables"
                :key="variable"
                :label="variable"
                min-width="150"
              >
                <template #default="{ row }">
                  <el-input
                    v-model="row[variable]"
                    :placeholder="`Enter ${variable}`"
                    size="small"
                  />
                </template>
              </el-table-column>
              
              <!-- Actions Column -->
              <el-table-column label="Actions" width="80" fixed="right">
                <template #default="{ $index }">
                  <el-button
                    type="danger"
                    size="small"
                    @click="removeRecord($index)"
                  >
                    Delete
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { MagicStick, Delete } from '@element-plus/icons-vue'
import { getBatch, createBatch, updateBatch } from '../api/batch'
import { getTemplates } from '../api/template'
import * as XLSX from 'xlsx'
import Papa from 'papaparse'

const router = useRouter()
const route = useRoute()
const formRef = ref()
const loading = ref(false)
const templates = ref([])
const selectedVariableForSubject = ref(null)
const selectedVariableForBody = ref(null)
const fileInputRef = ref(null)
const importFileType = ref(null)

const isEdit = computed(() => !!route.params.id)

const form = reactive({
  name: '',
  template_id: null,
  records: [],
  email_config: {
    subject: '',
    body: ''
  }
})

const rules = {
  name: [{ required: true, message: 'Please enter batch name', trigger: 'blur' }],
  template_id: [{ required: true, message: 'Please select template', trigger: 'change' }]
}

const selectedTemplate = computed(() => {
  return templates.value.find(t => t.id === form.template_id)
})

// Record-level sending: all template types read recipient data from each record
const emailVariableNames = ['STUDENT_EMAIL', 'EMPLOYEE_EMAIL', 'USER_EMAIL', 'RECIPIENT_EMAIL', 'EMAIL']
const nameVariableNames = ['STUDENT_NAME', 'EMPLOYEE_NAME', 'USER_FULL_NAME', 'RECIPIENT_NAME', 'NAME']

function getRecordValue(record, variableNames) {
  for (const key of variableNames) {
    const value = record[key]
    if (value !== undefined && value !== null) {
      const text = String(value).trim()
      if (text) return text
    }
  }
  return ''
}

function getRecordEmail(record) {
  return getRecordValue(record, emailVariableNames)
}

function getRecordName(record) {
  return getRecordValue(record, nameVariableNames)
}

// Check if template variables already include an email field
const hasEmailVariable = computed(() => {
  if (!selectedTemplate.value || !selectedTemplate.value.variables) return false
  return selectedTemplate.value.variables.some(v =>
    v.toUpperCase().includes('EMAIL')
  )
})

// Get available template variables - only show variables suitable for emails
const availableVariables = computed(() => {
  if (!selectedTemplate.value || !selectedTemplate.value.variables) return []
  
  // Define variables suitable for emails (general info, not detailed data)
  const emailSuitablePatterns = [
    // General
    'ISSUE_DATE',
    // Student transcript related
    'CLASS_NAME',
    'SEMESTER', 
    'STUDENT_NAME',
    'STUDENT_ID',
    'STUDENT_EMAIL',
    // Payroll related
    'EMPLOYEE_NAME',
    'EMPLOYEE_EMAIL',
    // Certificate related
    'USER_FULL_NAME',
    'USER_EMAIL',
    'RECIPIENT_NAME',
    'RECIPIENT_EMAIL',
    'EMAIL'
  ]
  
  return selectedTemplate.value.variables.filter(variable => {
    return emailSuitablePatterns.includes(variable)
  })
})

function insertVariable(field, variable) {
  if (!variable) return
  const varText = `{{${variable}}}`
  const key = field === 'subject' ? 'subject' : 'body'
  form.email_config[key] = form.email_config[key] ? form.email_config[key] + ' ' + varText : varText
  if (field === 'subject') selectedVariableForSubject.value = null
  else selectedVariableForBody.value = null
}

onMounted(async () => {
  await loadTemplates()
  if (isEdit.value) {
    await loadBatch()
  }
})

async function loadTemplates() {
  try {
    const res = await getTemplates({ all: 'true' })
    if (res.success && res.data) {
      // Parse template variables field (from JSON string to array)
      templates.value = res.data.map(template => {
        if (template.variables && typeof template.variables === 'string') {
          try {
            template.variables = JSON.parse(template.variables)
          } catch (e) {
            console.error('Failed to parse variables:', e)
            template.variables = []
          }
        }
        if (!Array.isArray(template.variables)) {
          template.variables = []
        }
        return template
      })
    }
  } catch (error) {
    console.error('Failed to load templates:', error)
  }
}

async function loadBatch() {
  loading.value = true
  try {
    const res = await getBatch(route.params.id)
    if (res.success && res.data) {
      form.name = res.data.name
      form.template_id = res.data.template_id
      form.records = res.data.records || []
      
      // Load email configuration
      form.email_config.subject = res.data.email_subject || ''
      form.email_config.body = res.data.email_body || ''
      
      // Convert data object to flat structure
      form.records = form.records.map(record => {
        const flat = { ...record.data, id: record.id }
        // Restore recipient email from student_email column if template has no email variable
        if (record.student_email && !flat._recipient_email) {
          flat._recipient_email = record.student_email
        }
        return flat
      })
    }
  } catch (error) {
    console.error('Failed to load batch:', error)
    ElMessage.error('Failed to load batch')
    goBack()
  } finally {
    loading.value = false
  }
}

function handleTemplateChange() {
  // Clear existing records when template changes
  if (!isEdit.value) {
    form.records = []
  }
}

function addRecord() {
  if (!selectedTemplate.value) {
    ElMessage.warning('Please select a template first')
    return
  }
  
  const variables = selectedTemplate.value.variables || []
  
  if (variables.length === 0) {
    ElMessage.warning('This template has no defined variables')
    return
  }
  
  const record = {}
  variables.forEach(variable => {
    record[variable] = ''
  })

  // Always add recipient email field if template has no email variable
  if (!hasEmailVariable.value) {
    record._recipient_email = ''
  }

  // Initialize internal fields from any supported record-level email/name variable
  if (emailVariableNames.some(variable => variables.includes(variable))) {
    record.student_email = ''
  }
  if (nameVariableNames.some(variable => variables.includes(variable))) {
    record.student_name = ''
  }  
  form.records.push(record)
  
  ElMessage.success('Record added, please fill in the data')
}

function removeRecord(index) {
  form.records.splice(index, 1)
}

function clearAllRecords() {
  ElMessageBox.confirm(
    'Are you sure you want to clear all records? This action cannot be undone.',
    'Confirm Clear',
    {
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel',
      type: 'warning'
    }
  ).then(() => {
    form.records = []
    ElMessage.success('All records cleared')
  }).catch(() => {
    // Cancel operation
  })
}

function generateTestData() {
  if (!selectedTemplate.value) {
    ElMessage.warning('Please select a template first')
    return
  }

  const variables = selectedTemplate.value.variables || []

  if (variables.length === 0) {
    ElMessage.warning('This template has no defined variables')
    return
  }

  generateTranscriptTestData(variables)
}

// Generate transcript test data
function generateTranscriptTestData(variables) {
  // Generate default email configuration
  if (!form.email_config.subject) {
    form.email_config.subject = `{{CLASS_NAME}} - {{SEMESTER}} Transcript`
  }
  if (!form.email_config.body) {
    form.email_config.body = `Dear {{STUDENT_NAME}},\n\nPlease find your transcript for {{SEMESTER}} attached.\n\nStudent ID: {{STUDENT_ID}}\nClass: {{CLASS_NAME}}\n\nBest wishes for your studies!\n\nAcademic Office`
  }
  
  // Generate 10 student records
  const studentNames = ['Alice Johnson', 'Bob Smith', 'Charlie Brown', 'Diana Lee', 'Edward Wilson', 'Fiona Davis', 'George Chen', 'Hannah Wang', 'Ivan Zhang', 'Julia Li']
  const studentsData = []
  
  for (let i = 0; i < 10; i++) {
    const chinese = 60 + Math.floor(Math.random() * 40)
    const math = 60 + Math.floor(Math.random() * 40)
    const english = 60 + Math.floor(Math.random() * 40)
    const total = chinese + math + english
    
    studentsData.push({
      id: `2025${String(i + 1).padStart(3, '0')}`,
      name: studentNames[i],
      email: `student${i + 1}@example.com`,
      chinese,
      math,
      english,
      total
    })
  }
  
  // Sort by total score descending
  studentsData.sort((a, b) => b.total - a.total)
  
  // Generate a record for each student
  studentsData.forEach((student, index) => {
    const record = {}
    
    // Assign values for each template variable
    variables.forEach(variable => {
      switch(variable) {
        case 'STUDENT_ID':
          record[variable] = student.id
          break
        case 'STUDENT_NAME':
          record[variable] = student.name
          break
        case 'STUDENT_EMAIL':
          record[variable] = student.email
          break
        case 'CHINESE':
          record[variable] = student.chinese
          break
        case 'MATH':
          record[variable] = student.math
          break
        case 'ENGLISH':
          record[variable] = student.english
          break
        case 'TOTAL':
          record[variable] = student.total
          break
        case 'RANK':
          record[variable] = index + 1
          break
        case 'CLASS_NAME':
          record[variable] = 'Computer Science Class 1'
          break
        case 'SEMESTER':
          record[variable] = 'Fall 2024'
          break
        case 'ISSUE_DATE':
          record[variable] = new Date().toISOString().split('T')[0]
          break
        default:
          record[variable] = `Test ${variable}`
      }
    })
    
    form.records.push(record)
  })
  
  
  ElMessage.success(`Generated 10 test records (1 record per student, ranked by total score)`)
}

// ===== File Import/Export =====

const SAMPLE_VALUES = {
  STUDENT_ID: '2025001', STUDENT_NAME: 'Alice Johnson', STUDENT_EMAIL: 'alice@example.com',
  CLASS_NAME: 'Computer Science Class 1', SEMESTER: 'Fall 2024',
  CHINESE: 85, MATH: 90, ENGLISH: 88, TOTAL: 263, RANK: 1,
  ISSUE_DATE: new Date().toISOString().split('T')[0]
}

function getSampleData(variables) {
  const sample = {}
  variables.forEach(v => { sample[v] = SAMPLE_VALUES[v] ?? `Sample ${v}` })
  return sample
}

function handleImportClick(type) {
  if (!selectedTemplate.value) {
    ElMessage.warning('Please select a template first')
    return
  }
  importFileType.value = type
  fileInputRef.value?.click()
}

function downloadTemplateFile(format) {
  if (!selectedTemplate.value) {
    ElMessage.warning('Please select a template first')
    return
  }

  const variables = selectedTemplate.value.variables || []
  if (variables.length === 0) {
    ElMessage.warning('This template has no defined variables')
    return
  }

  const sampleData = getSampleData(variables)
  const data = [variables, Object.values(sampleData)]
  const fileName = `${selectedTemplate.value.name}_template`

  if (format === 'excel') {
    const ws = XLSX.utils.aoa_to_sheet(data)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1')
    ws['!cols'] = variables.map(() => ({ wch: 20 }))
    XLSX.writeFile(wb, `${fileName}.xlsx`)
  } else {
    const csvContent = data.map(row =>
      row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
    ).join('\n')
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = Object.assign(document.createElement('a'), {
      href: URL.createObjectURL(blob), download: `${fileName}.csv`, style: 'display:none'
    })
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
  ElMessage.success(`${format === 'excel' ? 'Excel' : 'CSV'} template downloaded`)
}

function mapRowToRecord(row, variables) {
  const record = {}
  variables.forEach(v => {
    const val = row[v]
    record[v] = val !== undefined && val !== null ? String(val).trim() : ''
  })
  return record
}

function processImportedRecords(records) {
  const valid = records.filter(r => Object.values(r).some(v => v !== ''))
  if (valid.length === 0) {
    ElMessage.error('No valid data found in file')
    return
  }

  form.records.push(...valid)


  ElMessage.success(`Successfully imported ${valid.length} records`)
  if (fileInputRef.value) fileInputRef.value.value = ''
}

function warnMissingFields(headers, variables) {
  const missing = variables.filter(v => !headers.includes(v))
  if (missing.length > 0) {
    ElMessage.warning(`File is missing fields: ${missing.join(', ')}`)
  }
}

function handleFileImport(event) {
  const file = event.target.files?.[0]
  if (!file) return

  const variables = selectedTemplate.value?.variables || []
  const ext = file.name.split('.').pop().toLowerCase()

  if (importFileType.value === 'csv') {
    if (ext !== 'csv') { ElMessage.error('Please select a CSV file (.csv)'); return }
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        let text = e.target.result
        if (text.charCodeAt(0) === 0xFEFF) text = text.substring(1)
        Papa.parse(text, {
          header: true, skipEmptyLines: true,
          transformHeader: h => h.trim(),
          transform: v => v ? v.toString().trim() : '',
          complete: (results) => {
            if (!results.data?.length) { ElMessage.error('No valid data found'); return }
            warnMissingFields(results.meta.fields || [], variables)
            processImportedRecords(results.data.map(row => mapRowToRecord(row, variables)))
          },
          error: () => ElMessage.error('Failed to parse CSV file')
        })
      } catch (err) {
        ElMessage.error('Failed to process CSV: ' + err.message)
      }
    }
    reader.onerror = () => ElMessage.error('Failed to read file')
    reader.readAsText(file, 'UTF-8')
  } else {
    if (!['xlsx', 'xls'].includes(ext)) { ElMessage.error('Please select an Excel file (.xlsx or .xls)'); return }
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const workbook = XLSX.read(new Uint8Array(e.target.result), { type: 'array' })
        const sheet = workbook.Sheets[workbook.SheetNames[0]]
        const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '', raw: false })
        if (rows.length < 2) { ElMessage.error('File must have a header row and at least one data row'); return }
        const headers = rows[0].map(h => String(h || '').trim()).filter(Boolean)
        warnMissingFields(headers, variables)
        const records = rows.slice(1)
          .filter(row => row?.some(cell => cell && String(cell).trim()))
          .map(row => {
            const record = {}
            headers.forEach((h, i) => { if (variables.includes(h)) record[h] = String(row[i] ?? '').trim() })
            variables.forEach(v => { if (!(v in record)) record[v] = '' })
            return record
          })
        processImportedRecords(records)
      } catch (err) {
        ElMessage.error('Import failed: ' + (err.message || 'Invalid file format'))
      }
    }
    reader.onerror = () => ElMessage.error('Failed to read file')
    reader.readAsArrayBuffer(file)
  }
}

async function handleSave() {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    if (form.records.length === 0) {
      ElMessage.warning('Please add at least one record')
      return
    }
    
    loading.value = true
    try {
      // Prepare records
      const records = form.records.map(record => {
        const data = { ...record }
        
        // Map the record-level recipient email into the backend storage field
        const recipientEmail = data._recipient_email || getRecordEmail(data)
        if (recipientEmail) {
          data.student_email = recipientEmail
        }
        delete data._recipient_email

        // Map the record-level recipient name into the backend storage field
        const recipientName = getRecordName(data)
        if (recipientName && !data.student_name) {
          data.student_name = recipientName
        }        
        return data
      })
      
      let res
      if (isEdit.value) {
        res = await updateBatch(route.params.id, {
          name: form.name,
          email_subject: form.email_config.subject || null,
          email_body: form.email_config.body || null,
          records: records
        })
      } else {
        res = await createBatch({
          name: form.name,
          template_id: form.template_id,
          email_subject: form.email_config.subject || null,
          email_body: form.email_config.body || null,
          records: records
        })
      }
      
      if (res.success) {
        ElMessage.success(isEdit.value ? 'Batch updated successfully' : 'Batch created successfully')
        goBack()
      }
    } catch (error) {
      console.error('Failed to save batch:', error)
    } finally {
      loading.value = false
    }
  })
}

function goBack() {
  router.push('/batches')
}
</script>

<style scoped>
.batch-editor {
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

.records-section {
  width: 100%;
}

.records-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.record-count {
  color: #666;
  font-size: 14px;
}

.email-field-group {
  width: 100%;
}

.email-field-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 200px;
  gap: 10px;
  align-items: start;
  width: 100%;
}

.email-main-input {
  width: 100%;
}

.email-body-input :deep(textarea) {
  min-height: 160px;
}

.email-variable-select {
  width: 100%;
}

.field-hint {
  margin-top: 6px;
  font-size: 12px;
  color: #909399;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .batch-editor {
    padding: 12px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
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

  .email-field-row {
    grid-template-columns: 1fr;
  }

  .email-main-input,
  .email-variable-select {
    width: 100%;
  }

  .record-count {
    width: 100%;
  }
}
</style>
