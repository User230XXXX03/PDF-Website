<template>
  <el-container class="layout-container">
    <el-header class="header">
      <div class="header-left">
        <h2>PDF Generator System</h2>
      </div>
      <el-menu
        :default-active="activeMenu"
        :mode="menuMode"
        :ellipsis="false"
        @select="handleMenuSelect"
        class="header-menu"
      >
        <el-menu-item index="/dashboard">
          <el-icon><DataAnalysis /></el-icon>
          <span>Dashboard</span>
        </el-menu-item>
        <el-menu-item index="/templates">
          <el-icon><Document /></el-icon>
          <span>Templates</span>
          <el-badge v-if="templatesCount > 0" :value="templatesCount" class="badge" />
        </el-menu-item>
        <el-menu-item index="/batches">
          <el-icon><Files /></el-icon>
          <span>Batches</span>
          <el-badge v-if="batchesCount > 0" :value="batchesCount" class="badge" />
        </el-menu-item>
        <el-menu-item index="/logs">
          <el-icon><List /></el-icon>
          <span>Logs</span>
        </el-menu-item>
        <el-menu-item index="/settings">
          <el-icon><Setting /></el-icon>
          <span>Settings</span>
        </el-menu-item>
      </el-menu>
      <div class="header-right">
        <div class="dark-mode-toggle" @click="toggleDarkMode" :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
          <el-icon :size="18" v-if="isDark"><Sunny /></el-icon>
          <el-icon :size="18" v-else><Moon /></el-icon>
          <span class="dark-mode-label">{{ isDark ? 'Dark' : 'Light' }}</span>
        </div>
        <el-dropdown @command="handleCommand">
          <span class="user-dropdown">
            <el-icon><User /></el-icon>
            <span>{{ username }}</span>
          </span>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="logout">
                <el-icon><SwitchButton /></el-icon>
                Logout
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
    </el-header>
    <el-main class="main-content">
      <router-view />
    </el-main>
  </el-container>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Sunny, Moon } from '@element-plus/icons-vue'
import { logout, getCurrentUser } from '../api/auth'
import { getDashboardStats } from '../api/dashboard'

const router = useRouter()
const route = useRoute()

const username = ref('')
const templatesCount = ref(0)
const batchesCount = ref(0)
const isDark = ref(false)
const windowWidth = ref(window.innerWidth)

const isMobile = computed(() => windowWidth.value <= 768)
const menuMode = computed(() => isMobile.value ? 'vertical' : 'horizontal')

const activeMenu = computed(() => {
  return route.path
})

function handleResize() {
  windowWidth.value = window.innerWidth
}

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

onMounted(async () => {
  window.addEventListener('resize', handleResize)
  // Restore dark mode preference
  const savedDark = localStorage.getItem('darkMode')
  if (savedDark === 'true') {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
  await loadUserInfo()
  await loadStats()
})

function toggleDarkMode() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('darkMode', isDark.value.toString())
}

async function loadUserInfo() {
  try {
    const res = await getCurrentUser()
    if (res.success && res.data) {
      username.value = res.data.username
    }
  } catch (error) {
    console.error('Failed to load user info:', error)
  }
}

async function loadStats() {
  try {
    const res = await getDashboardStats()
    if (res.success && res.data) {
      templatesCount.value = res.data.templates_count || 0
      batchesCount.value = res.data.batches_count || 0
    }
  } catch (error) {
    console.error('Failed to load stats:', error)
  }
}

function handleMenuSelect(index) {
  router.push(index)
}

async function handleCommand(command) {
  if (command === 'logout') {
    try {
      await logout()
      sessionStorage.removeItem('user')
      ElMessage.success('Logout Success')
      router.push('/login')
    } catch (error) {
      console.error('Logout failed:', error)
    }
  }
}
</script>

<style scoped>
.layout-container {
  height: 100vh;
}

.header {
  background: var(--header-bg, #242424);
  color: white;
  display: flex;
  align-items: center;
  padding: 0 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  gap: 16px;
}

.header-left {
  margin-right: 40px;
}

.header-left h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  white-space: nowrap;
}

.header-menu {
  flex: 1;
  background: transparent;
  border: none;
  min-width: 0;
}

.header-menu .el-menu-item {
  color: rgba(255, 255, 255, 0.7);
  border-bottom: 3px solid transparent;
}

.header-menu .el-menu-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.header-menu .el-menu-item.is-active {
  color: white;
  border-bottom-color: #409eff;
  background: rgba(255, 255, 255, 0.05);
}

.badge {
  margin-left: 8px;
}

.header-right {
  margin-left: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  flex-shrink: 0;
}

.user-dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background 0.3s;
}

.user-dropdown:hover {
  background: rgba(255, 255, 255, 0.1);
}

.main-content {
  background: var(--bg-secondary, #f5f5f5);
  padding: 20px;
  overflow-y: auto;
}

.dark-mode-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  transition: background 0.3s, transform 0.3s;
  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
  background: rgba(255, 255, 255, 0.08);
}

.dark-mode-toggle:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: scale(1.05);
}

.dark-mode-label {
  color: rgba(255, 255, 255, 0.85);
  font-weight: 500;
  user-select: none;
}

@media (max-width: 992px) {
  .header {
    flex-wrap: wrap;
    height: auto;
    padding: 8px 12px;
  }

  .header-left {
    margin-right: auto;
  }

  .header-left h2 {
    font-size: 17px;
  }

  .header-menu {
    order: 3;
    width: 100%;
    margin-top: 8px;
    overflow-x: auto;
    scrollbar-width: none;
  }

  .header-menu::-webkit-scrollbar {
    display: none;
  }

  .header-right {
    order: 2;
    margin-left: 0;
    gap: 10px;
  }

  .dark-mode-label {
    display: none;
  }

  .user-dropdown span:last-child {
    display: none;
  }
}

@media (max-width: 768px) {
  /* Stack header vertically on mobile */
  .header {
    flex-direction: column;
    align-items: stretch;
    padding: 10px 0;
    gap: 0;
  }

  .header-left {
    margin-right: 0;
    padding: 6px 16px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  }

  .header-left h2 {
    font-size: 17px;
    text-align: center;
  }

  /* Vertical menu — full width, items stacked */
  .header-menu {
    order: unset;
    width: 100%;
    margin-top: 0;
    overflow-x: unset;
    border-right: none !important;
  }

  .header-menu .el-menu-item {
    height: 44px;
    line-height: 44px;
    padding: 0 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    border-left: 3px solid transparent;
  }

  .header-menu .el-menu-item.is-active {
    border-left-color: #409eff;
    border-bottom-color: transparent;
    background: rgba(64, 158, 255, 0.1) !important;
  }

  /* Right controls below menu, centered */
  .header-right {
    order: unset;
    width: 100%;
    justify-content: center;
    gap: 12px;
    margin-top: 0;
    padding: 10px 16px 4px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
  }

  .dark-mode-label {
    display: inline;
  }

  .user-dropdown span:last-child {
    display: inline;
  }

  .main-content {
    padding: 12px;
  }
}

@media (max-width: 480px) {
  .header-left h2 {
    font-size: 15px;
  }
}
</style>
