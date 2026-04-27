<template>
  <div class="templates">
    <div class="page-header">
      <h1 class="page-title">Templates</h1>
      <div>
        <el-button type="success" @click="showImportDialog">
          <el-icon><Upload /></el-icon>
          Import Template
        </el-button>
        <el-button type="primary" @click="createTemplate">
          <el-icon><Plus /></el-icon>
          Create Template
        </el-button>
      </div>
    </div>

    <!-- Import Template Dialog -->
    <el-dialog
      v-model="importDialogVisible"
      title="Import HTML Template"
      width="600px"
      class="import-dialog"
    >
      <el-alert
        type="info"
        :closable="false"
        style="margin-bottom: 20px;"
      >
        <template #title>
          💡 Download Test Template
        </template>
        <el-button 
          type="primary" 
          size="small" 
          @click="downloadTestTemplate"
          style="margin-top: 10px;"
        >
          <el-icon><Download /></el-icon>
          Download Transcript Test Template
        </el-button>
      </el-alert>
      
      <el-form :model="importForm" label-width="150px">
        <el-form-item label="Template Name" required>
          <el-input v-model="importForm.name" placeholder="Please enter template name" />
        </el-form-item>
        <el-form-item label="Document Type" required>
          <el-select v-model="importForm.set_type" placeholder="Please select template type" style="width: 100%">
            <el-option label="Course Feedback" value="Course Feedback" />
            <el-option label="Certificate" value="Certificate" />
            <el-option label="Transcript" value="Transcript" />
            <el-option label="Payroll" value="Payroll" />
          </el-select>
        </el-form-item>
        <el-form-item label="Template Content" required>
          <el-upload
            ref="uploadRef"
            :auto-upload="false"
            :limit="1"
            :on-change="handleFileChange"
            :on-exceed="handleExceed"
            accept=".html,.htm"
            drag
          >
            <el-icon class="el-icon--upload"><upload-filled /></el-icon>
            <div class="el-upload__text">
              Drag file here or click to upload
            </div>
            <template #tip>
              <div class="el-upload__tip">
                Only .html or .htm files are supported
              </div>
            </template>
          </el-upload>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="importDialogVisible = false">Cancel</el-button>
        <el-button type="primary" :loading="importing" @click="handleImport">
          Import
        </el-button>
      </template>
    </el-dialog>

    <el-card>
      <div class="filter-bar">
        <el-input
          v-model="searchText"
          placeholder="Search templates..."
          class="filter-search"
          clearable
          @change="loadTemplates"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
        <el-select
          v-model="filterType"
          placeholder="Filter by type"
          class="filter-type"
          clearable
          @change="loadTemplates"
        >
          <el-option label="Course Feedback" value="Course Feedback" />
          <el-option label="Certificate" value="Certificate" />
          <el-option label="Transcript" value="Transcript" />
          <el-option label="Payroll" value="Payroll" />
        </el-select>
      </div>

      <el-table :data="templates" v-loading="loading" style="margin-top: 20px">
        <el-table-column prop="name" label="Template Name" min-width="200" />
        <el-table-column prop="set_type" label="Type" width="150">
          <template #default="{ row }">
            <el-tag :type="getTypeColor(row.set_type)">{{ getTypeName(row.set_type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="Created" width="180" />
        <el-table-column prop="updated_at" label="Updated" width="180" />
        <el-table-column label="Actions" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="editTemplate(row.id)">
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
import { getTemplates, deleteTemplate } from '../api/template'
import { createTemplate as createTemplateAPI } from '../api/template'
import { UploadFilled, Download } from '@element-plus/icons-vue'

const router = useRouter()
const loading = ref(false)
const templates = ref([])
const searchText = ref('')
const filterType = ref('')

// Import template related
const importDialogVisible = ref(false)
const importing = ref(false)
const uploadRef = ref(null)
const importForm = ref({
  name: '',
  set_type: '',
  content: ''
})
const uploadedFile = ref(null)

onMounted(() => {
  loadTemplates()
})

async function loadTemplates() {
  loading.value = true
  try {
    const params = {}
    if (searchText.value) params.search = searchText.value
    if (filterType.value) params.set_type = filterType.value
    
    const res = await getTemplates(params)
    if (res.success && res.data) {
      templates.value = res.data
    }
  } catch (error) {
    console.error('Failed to load templates:', error)
  } finally {
    loading.value = false
  }
}

function createTemplate() {
  router.push('/templates/create')
}

function editTemplate(id) {
  router.push(`/templates/edit/${id}`)
}

// Show import dialog
function showImportDialog() {
  importForm.value = {
    name: '',
    set_type: '',
    content: ''
  }
  uploadedFile.value = null
  importDialogVisible.value = true
}

// Download test template
function downloadTestTemplate() {
  // Transcript test template HTML
  const templateHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Transcript</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            font-size: 14px;
            color: #222;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0;
        }
        .subtitle {
            font-size: 13px;
            color: #666;
        }
        .info-table,
        .score-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .info-table td,
        .score-table th,
        .score-table td {
            padding: 8px 12px;
            border: 1px solid #000;
        }
        .info-table .label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 20%;
        }
        .score-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        .score-table .score {
            text-align: center;
        }
        .score-table .total-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 22px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STUDENT TRANSCRIPT</h1>
        <div class="subtitle">Official Academic Record</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Student ID</td>
            <td>{{STUDENT_ID}}</td>
            <td class="label">Student Name</td>
            <td>{{STUDENT_NAME}}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{STUDENT_EMAIL}}</td>
            <td class="label">Class</td>
            <td>{{CLASS_NAME}}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td>{{SEMESTER}}</td>
            <td class="label">Issue Date</td>
            <td>{{ISSUE_DATE}}</td>
        </tr>
    </table>

    <table class="score-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Chinese</td>
                <td class="score">{{CHINESE}}</td>
            </tr>
            <tr>
                <td>Mathematics</td>
                <td class="score">{{MATH}}</td>
            </tr>
            <tr>
                <td>English</td>
                <td class="score">{{ENGLISH}}</td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td class="score">{{TOTAL}}</td>
            </tr>
            <tr class="total-row">
                <td>Class Rank</td>
                <td class="score">{{RANK}}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This transcript is automatically generated | Issue Date: {{ISSUE_DATE}}
    </div>
</body>
</html>`

  // Create Blob object
  const blob = new Blob([templateHTML], { type: 'text/html;charset=utf-8' })
  
  // Create download link
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'transcript_template_test.html'
  
  // Trigger download
  document.body.appendChild(link)
  link.click()
  
  // Cleanup
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
  
  ElMessage.success('Success')
}
// Handle file selection
function handleFileChange(file) {
  uploadedFile.value = file
  // Read file content
  const reader = new FileReader()
  reader.onload = (e) => {
    importForm.value.content = e.target.result
    // Auto extract template name from filename (if user hasn't filled it)
    if (!importForm.value.name) {
      const fileName = file.name.replace(/\.(html|htm)$/i, '')
      importForm.value.name = fileName
    }
  }
  reader.readAsText(file.raw)
}

// Handle file exceed limit
function handleExceed() {
  ElMessage.warning('Only one file can be uploaded')
}

// Import template
async function handleImport() {
  // Validation
  if (!importForm.value.name) {
    ElMessage.warning('Please enter template name')
    return
  }
  if (!importForm.value.set_type) {
    ElMessage.warning('Please select template type')
    return
  }
  if (!uploadedFile.value) {
    ElMessage.warning('Please upload HTML file')
    return
  }
  
  importing.value = true
  try {
    const res = await createTemplateAPI({
      name: importForm.value.name,
      set_type: importForm.value.set_type,
      content: importForm.value.content,
      variables: extractVariables(importForm.value.content)
    })
    
    if (res.success) {
      ElMessage.success('Template imported successfully')
      importDialogVisible.value = false
      loadTemplates()
    }
  } catch (error) {
    console.error('Failed to import template:', error)
    ElMessage.error('Template import failed')
  } finally {
    importing.value = false
  }
}

// Extract variables from HTML content
function extractVariables(html) {
  const regex = /\{\{([A-Z_]+)\}\}/g
  const variables = []
  let match
  while ((match = regex.exec(html)) !== null) {
    if (!variables.includes(match[1])) {
      variables.push(match[1])
    }
  }
  return variables
}

async function handleDelete(row) {
  try {
    await ElMessageBox.confirm(
      'Are you sure you want to delete template "' + row.name + '"?',
      'Confirm',
      {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }
    )
    
    loading.value = true
    const res = await deleteTemplate(row.id)
    if (res.success) {
      ElMessage.success('Template deleted successfully')
      loadTemplates()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Failed to delete template:', error)
    }
  } finally {
    loading.value = false
  }
}

function getTypeColor(type) {
  const colors = {
    'Course Feedback': '',
    'Certificate': 'success',
    'Transcript': 'warning',
    'Payroll': 'info'
  }
  return colors[type] || ''
}

function getTypeName(type) {
  const names = {
    'Course Feedback': 'Course Feedback',
    'Certificate': 'Certificate',
    'Transcript': 'Transcript',
    'Payroll': 'Payroll'
  }
  return names[type] || type
}
</script>

<style scoped>
.templates {
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

.filter-search {
  width: 300px;
}

.filter-type {
  width: 200px;
}

@media (max-width: 768px) {
  .templates {
    padding: 12px;
  }

  .filter-search,
  .filter-type {
    width: 100%;
  }
}
</style>
