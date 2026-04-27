<template>
  <div class="template-editor">
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Edit Template' : 'Create Template' }}</h1>
      <div>
        <el-button @click="goBack">Cancel</el-button>
        <el-button type="primary" @click="handleSave" :loading="loading">
          Save
        </el-button>
      </div>
    </div>

    <el-card>
      <el-form :model="form" :rules="rules" ref="formRef" label-width="120px">
        <el-form-item label="Template Name" prop="name">
          <el-input v-model="form.name" placeholder="Template Name" />
        </el-form-item>

        <el-form-item label="Document Type" prop="set_type">
          <el-select v-model="form.set_type" placeholder="Document Type">
            <el-option label="Course Feedback" value="Course Feedback" />
            <el-option label="Certificate" value="Certificate" />
            <el-option label="Transcript" value="Transcript" />
            <el-option label="Payroll" value="Payroll" />
          </el-select>
        </el-form-item>

        <el-form-item label="Variables">
          <div class="variables-section">
            <el-tag
              v-for="(variable, index) in form.variables"
              :key="index"
              closable
              @close="removeVariable(index)"
              style="margin-right: 10px; margin-bottom: 10px"
            >
              {{ variable }}
            </el-tag>
            <el-input
              v-if="showVariableInput"
              ref="variableInputRef"
              v-model="newVariable"
              size="small"
              style="width: 200px"
              @keyup.enter="addVariable"
              @blur="addVariable"
            />
            <el-button v-else size="small" @click="showVariableInput = true">
              + Add Variable
            </el-button>
            <div class="hint" v-pre>
              Use {{VARIABLE_NAME}} syntax for placeholders
            </div>
          </div>
        </el-form-item>

        <el-form-item label="Template Content" prop="content">
          <el-input
            v-model="form.content"
            type="textarea"
            :rows="20"
            placeholder="Use {{VARIABLE_NAME}} syntax for placeholders"
          />
          <div class="hint" v-pre>
            Use {{VARIABLE_NAME}} syntax for placeholders
          </div>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { getTemplate, createTemplate, updateTemplate } from '../api/template'

const router = useRouter()
const route = useRoute()
const formRef = ref()
const loading = ref(false)
const showVariableInput = ref(false)
const variableInputRef = ref()
const newVariable = ref('')

const isEdit = computed(() => !!route.params.id)

const form = reactive({
  name: '',
  set_type: '',
  variables: [],
  content: ''
})

const rules = {
  name: [{ required: true, message: 'Please enter template name', trigger: 'blur' }],
  set_type: [{ required: true, message: 'Please select document type', trigger: 'change' }],
  content: [{ required: true, message: 'Please enter template content', trigger: 'blur' }]
}

onMounted(() => {
  if (isEdit.value) {
    loadTemplate()
  }
})

async function loadTemplate() {
  loading.value = true
  try {
    const res = await getTemplate(route.params.id)
    if (res.success && res.data) {
      Object.assign(form, res.data)
      
      // Parse variables field
      if (form.variables && typeof form.variables === 'string') {
        try {
          form.variables = JSON.parse(form.variables)
        } catch (e) {
          console.error('Failed to parse variables:', e)
          form.variables = []
        }
      }
      
      if (!Array.isArray(form.variables)) {
        form.variables = []
      }
    }
  } catch (error) {
    console.error('Failed to load template:', error)
    ElMessage.error('Error')
    goBack()
  } finally {
    loading.value = false
  }
}

async function addVariable() {
  if (newVariable.value) {
    const variable = newVariable.value.trim().toUpperCase()
    if (variable && !form.variables.includes(variable)) {
      form.variables.push(variable)
    }
    newVariable.value = ''
  }
  showVariableInput.value = false
}

function removeVariable(index) {
  form.variables.splice(index, 1)
}

async function handleSave() {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    loading.value = true
    try {
      let res
      if (isEdit.value) {
        res = await updateTemplate(route.params.id, form)
      } else {
        res = await createTemplate(form)
      }
      
      if (res.success) {
        ElMessage.success(isEdit.value ? 'Template updated successfully' : 'Template created successfully')
        goBack()
      }
    } catch (error) {
      console.error('Failed to save template:', error)
    } finally {
      loading.value = false
    }
  })
}

function goBack() {
  router.push('/templates')
}
</script>

<style scoped>
.template-editor {
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

.variables-section {
  width: 100%;
}

.hint {
  margin-top: 5px;
  font-size: 12px;
  color: #999;
}

@media (max-width: 768px) {
  .template-editor {
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
}
</style>
