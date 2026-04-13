<template>
  <router-view />
</template>

<script setup>
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body, #app {
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB',
    'Microsoft YaHei', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

/* ===== Light Mode (default) ===== */
:root {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f5f5;
  --bg-card: #ffffff;
  --bg-input: #ffffff;
  --bg-hover: #f5f7fa;
  --text-primary: #333333;
  --text-secondary: #666666;
  --text-muted: #999999;
  --border-color: #dcdfe6;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --header-bg: #242424;
}

/* ===== Dark Mode ===== */
html.dark {
  --bg-primary: #1a1a2e;
  --bg-secondary: #16213e;
  --bg-card: #1e293b;
  --bg-input: #2a3442;
  --bg-hover: #2a3a4e;
  --text-primary: #e2e8f0;
  --text-secondary: #a0aec0;
  --text-muted: #718096;
  --border-color: #3a4a5c;
  --shadow-color: rgba(0, 0, 0, 0.4);
  --header-bg: #0f172a;
  color-scheme: dark;
}

/* Custom dark mode overrides (not covered by Element Plus dark/css-vars.css) */
html.dark body {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
}

html.dark .el-descriptions__label.is-bordered-label {
  background-color: var(--bg-secondary);
  font-weight: 600;
}

html.dark .el-dialog__header {
  border-bottom: 1px solid var(--border-color);
}

html.dark .el-badge__content {
  border-color: var(--header-bg);
}

html.dark .el-menu--horizontal .el-menu-item:not(.is-disabled):hover {
  background-color: rgba(255, 255, 255, 0.1);
}

/* Project-specific dark mode */
html.dark .page-title {
  color: var(--text-primary) !important;
}

html.dark .hint {
  color: var(--text-muted) !important;
}

/* Responsive layout */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px;
  }

  .page-title {
    font-size: 22px !important;
  }

  .filter-bar {
    width: 100%;
  }

  .filter-bar > * {
    flex: 1 1 auto;
    min-width: 0;
  }

  .el-dialog {
    width: 92vw !important;
    margin: 0 auto;
  }

  .el-pagination {
    flex-wrap: wrap;
    justify-content: center !important;
  }
}
</style>

