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
            <li v-if="!isPersonalizedTemplate"><strong>Recipients:</strong> Set uniformly, all recipients will receive the same email</li>
            <li v-if="isPersonalizedTemplate"><strong>Personalized Sending:</strong> System will automatically get each person's email from the STUDENT_EMAIL field in data records</li>
            <li><strong>Subject/Content:</strong> Supports template variables like {{STUDENT_NAME}}, {{CLASS_NAME}}, etc.</li>
          </ul>
        </el-alert>

        <el-form-item v-if="!isPersonalizedTemplate" label="Recipients (To)" prop="email_config.recipients">
          <el-input
            v-model="form.email_config.recipients"
            placeholder="Multiple emails supported, separated by semicolons: student1@example.com;student2@example.com"
            type="textarea"
            :rows="2"
          />
          <div style="font-size: 12px; color: #909399; margin-top: 5px;">
            💡 All recipients will receive the same email content
          </div>
        </el-form-item>

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
              <el-button size="small" @click="addRecord">
                <el-icon><Plus /></el-icon>
                Add Record
              </el-button>
              <el-button size="small" type="success" @click="generateTestData">
                <el-icon><MagicStick /></el-icon>
                Generate Test Data
              </el-button>
              <el-button size="small" type="warning" @click="clearAllRecords">
                <el-icon><Delete /></el-icon>
                Clear All
              </el-button>
              <span class="record-count">{{ form.records.length + ' records' }}</span>
            </div>

            <!-- Data Records Table (unified display) -->
            <el-table :data="form.records" border style="margin-top: 10px">
              <!-- Index Column -->
              <el-table-column label="#" type="index" width="50" fixed="left" />
              
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

const router = useRouter()
const route = useRoute()
const formRef = ref()
const loading = ref(false)
const templates = ref([])
const selectedVariableForSubject = ref(null)
const selectedVariableForBody = ref(null)

const isEdit = computed(() => !!route.params.id)

const form = reactive({
  name: '',
  template_id: null,
  records: [],
  email_config: {
    recipients: '',
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

// Check if it's a personalized template (transcript/payroll, each person receives their own)
const isPersonalizedTemplate = computed(() => {
  if (!selectedTemplate.value) return false
  const type = selectedTemplate.value.set_type?.toLowerCase() || ''
  // Transcript and Payroll need personalized sending
  return type === 'transcript' || type === 'payroll'
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
    // Certificate related
    'USER_FULL_NAME',
    'USER_EMAIL',
    'RECIPIENT_NAME'
  ]
  
  return selectedTemplate.value.variables.filter(variable => {
    return emailSuitablePatterns.includes(variable)
  })
})

// Insert variable into email subject or body
function insertVariable(field, variable) {
  if (!variable) return
  
  const varText = `{{${variable}}}`
  
  if (field === 'subject') {
    if (!form.email_config.subject) {
      form.email_config.subject = varText
    } else {
      form.email_config.subject += ' ' + varText
    }
    selectedVariableForSubject.value = null
  } else if (field === 'body') {
    if (!form.email_config.body) {
      form.email_config.body = varText
    } else {
      form.email_config.body += ' ' + varText
    }
    selectedVariableForBody.value = null
  }
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
      form.email_config.recipients = res.data.email_recipients || ''
      form.email_config.subject = res.data.email_subject || ''
      form.email_config.body = res.data.email_body || ''
      
      // Convert data object to flat structure
      form.records = form.records.map(record => {
        return { ...record.data, id: record.id }
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
  
  // Set default email field name for email sending
  if (variables.includes('USER_EMAIL')) {
    record.student_email = ''
  }
  if (variables.includes('USER_FULL_NAME')) {
    record.student_name = ''
  }
  if (variables.includes('STUDENT_NAME')) {
    record.student_name = ''
  }
  if (variables.includes('STUDENT_ID')) {
    record.student_email = ''
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
  const emails = []
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
          emails.push(student.email)
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
  
  // Auto fill recipients list
  if (emails.length > 0 && !form.email_config.recipients) {
    form.email_config.recipients = emails.join(';')
  }
  
  ElMessage.success(`Generated 10 test records (1 record per student, ranked by total score)`)
}

// Generate payroll test data
function generatePayrollTestData(variables) {
  // Generate default email configuration
  if (!form.email_config.subject) {
    form.email_config.subject = 'Payslip for {{MONTH}}'
  }
  if (!form.email_config.body) {
    form.email_config.body = 'Dear {{EMPLOYEE_NAME}},\n\nPlease check your payslip for {{MONTH}} attached.\n\nNet Salary: {{NET_SALARY}}\n\nBest regards,\nHR Department'
  }

  const demoEmployees = [
    { name: 'John Doe', email: 'john@example.com', dept: 'Engineering', pos: 'Engineer' },
    { name: 'Jane Smith', email: 'jane@example.com', dept: 'Marketing', pos: 'Manager' },
    { name: 'Bob Wilson', email: 'bob@example.com', dept: 'Sales', pos: 'Salesman' }
  ]
  
  const currentMonth = new Date().toISOString().slice(0, 7) // YYYY-MM

  demoEmployees.forEach((emp, i) => {
    const record = {}
    const income = 5000 + (i * 1000)
    const deduction = income * 0.15
    
    variables.forEach(variable => {
      switch(variable) {
        case 'EMPLOYEE_ID': record[variable] = `EMP${1000+i}`; break;
        case 'EMPLOYEE_NAME': record[variable] = emp.name; break;
        case 'EMPLOYEE_EMAIL': record[variable] = emp.email; break;
        case 'DEPARTMENT': record[variable] = emp.dept; break;
        case 'POSITION': record[variable] = emp.pos; break;
        case 'MONTH': record[variable] = currentMonth; break;
        case 'TOTAL_INCOME': record[variable] = income; break;
        case 'TOTAL_DEDUCTION': record[variable] = deduction; break;
        case 'NET_SALARY': record[variable] = income - deduction; break;
        case 'ISSUE_DATE': record[variable] = new Date().toISOString().split('T')[0]; break;
        default: record[variable] = `Test ${variable}`;
      }
    })
    form.records.push(record)
  })
  
  if (!form.email_config.recipients && demoEmployees.length > 0) {
    form.email_config.recipients = demoEmployees.map(e => e.email).join(';')
  }
  
  ElMessage.success(`Generated ${demoEmployees.length} payroll test records`)
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
      // Prepare records - ensure student_name and student_email are set
      const records = form.records.map(record => {
        const data = { ...record }
        
        // Map common variables to student_name and student_email
        if (data.USER_FULL_NAME && !data.student_name) {
          data.student_name = data.USER_FULL_NAME
        }
        if (data.USER_EMAIL && !data.student_email) {
          data.student_email = data.USER_EMAIL
        }
        
        return data
      })
      
      let res
      if (isEdit.value) {
        res = await updateBatch(route.params.id, {
          name: form.name,
          email_recipients: form.email_config.recipients || null,
          email_subject: form.email_config.subject || null,
          email_body: form.email_config.body || null,
          records: records
        })
      } else {
        res = await createBatch({
          name: form.name,
          template_id: form.template_id,
          email_recipients: form.email_config.recipients || null,
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
