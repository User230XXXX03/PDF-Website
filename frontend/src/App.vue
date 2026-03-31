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
  --el-bg-color: #ffffff;
  --el-fill-color-blank: #ffffff;
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

/* Dark mode overrides for Element Plus */
html.dark .el-card {
  --el-card-bg-color: var(--bg-card);
  border-color: var(--border-color);
  color: var(--text-primary);
}

html.dark .el-card__header {
  border-bottom-color: var(--border-color);
  color: var(--text-primary);
}

html.dark .el-input__wrapper,
html.dark .el-textarea__inner {
  background-color: var(--bg-input);
  box-shadow: 0 0 0 1px var(--border-color) inset;
  color: var(--text-primary);
}

html.dark .el-input__inner {
  color: var(--text-primary);
}

html.dark .el-input__wrapper:hover,
html.dark .el-textarea__inner:hover {
  box-shadow: 0 0 0 1px #409eff inset;
}

html.dark .el-form-item__label {
  color: var(--text-secondary);
}

html.dark .el-table {
  --el-table-bg-color: var(--bg-card);
  --el-table-tr-bg-color: var(--bg-card);
  --el-table-header-bg-color: var(--bg-secondary);
  --el-table-row-hover-bg-color: var(--bg-hover);
  --el-table-border-color: var(--border-color);
  --el-table-text-color: var(--text-primary);
  --el-table-header-text-color: var(--text-secondary);
  --el-fill-color-lighter: var(--bg-secondary);
  color: var(--text-primary);
}

html.dark .el-table th.el-table__cell {
  background-color: var(--bg-secondary);
}

html.dark .el-table td.el-table__cell {
  background-color: var(--bg-card);
}

html.dark .el-table--border .el-table__inner-wrapper::after,
html.dark .el-table--border::before,
html.dark .el-table--border::after,
html.dark .el-table__inner-wrapper::before {
  background-color: var(--border-color);
}

html.dark .el-table .el-table__cell {
  border-color: var(--border-color);
}

html.dark .el-table--border .el-table__cell {
  border-color: var(--border-color);
}

html.dark .el-table__body-wrapper .el-table__body tr td.el-table-fixed-column--right,
html.dark .el-table__body-wrapper .el-table__body tr td.el-table-fixed-column--left {
  background-color: var(--bg-card);
}

html.dark .el-table__header-wrapper .el-table__header tr th.el-table-fixed-column--right,
html.dark .el-table__header-wrapper .el-table__header tr th.el-table-fixed-column--left {
  background-color: var(--bg-secondary);
}

html.dark .el-descriptions {
  --el-descriptions-item-bordered-label-background: var(--bg-secondary);
  --el-fill-color-blank: var(--bg-card);
  color: var(--text-primary);
}

html.dark .el-descriptions__label,
html.dark .el-descriptions__content {
  color: #f1f5f9 !important;
  border-color: var(--border-color) !important;
  background-color: var(--bg-card);
}

html.dark .el-descriptions__content * {
  color: #f1f5f9;
}

html.dark .el-descriptions__label.is-bordered-label {
  background-color: var(--bg-secondary);
  color: var(--text-secondary);
  font-weight: 600;
}

html.dark .el-descriptions__content .el-descriptions__body {
  color: var(--text-primary);
}

html.dark .el-descriptions .el-descriptions__body {
  color: var(--text-primary);
}

html.dark .el-alert--info {
  background-color: rgba(64, 158, 255, 0.1);
  color: var(--text-primary);
}

html.dark .el-alert--warning {
  background-color: rgba(230, 162, 60, 0.15);
  color: var(--text-primary);
}

html.dark .el-alert .el-alert__title {
  color: var(--text-primary);
}

html.dark .el-alert .el-alert__description {
  color: var(--text-secondary);
}

html.dark .el-divider {
  border-color: var(--border-color);
}

html.dark .el-loading-mask {
  background-color: rgba(30, 41, 59, 0.8);
}

html.dark .el-select-dropdown,
html.dark .el-dropdown-menu {
  background-color: var(--bg-card);
  border-color: var(--border-color);
}

html.dark .el-dropdown-menu__item {
  color: var(--text-primary);
}

html.dark .el-dropdown-menu__item:hover {
  background-color: var(--bg-hover);
}

html.dark .el-select-dropdown__item {
  color: var(--text-primary);
}

html.dark .el-select-dropdown__item:hover {
  background-color: var(--bg-hover);
}

html.dark .el-pagination {
  --el-pagination-bg-color: var(--bg-card);
  --el-pagination-text-color: var(--text-primary);
  --el-pagination-button-disabled-bg-color: var(--bg-secondary);
}

html.dark .el-dialog {
  --el-dialog-bg-color: var(--bg-card);
  --el-dialog-title-font-size: 18px;
  color: var(--text-primary);
}

html.dark .el-dialog__header {
  border-bottom: 1px solid var(--border-color);
}

html.dark .el-tag {
  border-color: var(--border-color);
}

html.dark .el-tag--light {
  background-color: rgba(64, 158, 255, 0.15);
  border-color: rgba(64, 158, 255, 0.3);
  color: #79bbff;
}

html.dark .el-tag--light.el-tag--info {
  background-color: rgba(160, 174, 192, 0.15);
  border-color: rgba(160, 174, 192, 0.3);
  color: #a0aec0;
}

html.dark .el-tag--light.el-tag--success {
  background-color: rgba(103, 194, 58, 0.15);
  border-color: rgba(103, 194, 58, 0.3);
  color: #95d475;
}

html.dark .el-tag--light.el-tag--warning {
  background-color: rgba(230, 162, 60, 0.15);
  border-color: rgba(230, 162, 60, 0.3);
  color: #eebe77;
}

html.dark .el-tag--light.el-tag--danger {
  background-color: rgba(245, 108, 108, 0.15);
  border-color: rgba(245, 108, 108, 0.3);
  color: #f89898;
}

html.dark .el-tag .el-tag__content {
  color: inherit;
}

html.dark .el-badge__content {
  border-color: var(--header-bg);
}

html.dark .el-input-number {
  --el-input-number-bg-color: var(--bg-input);
}

html.dark .el-input-number .el-input-number__decrease,
html.dark .el-input-number .el-input-number__increase {
  background-color: var(--bg-secondary);
  border-color: var(--border-color);
  color: var(--text-primary);
}

html.dark .el-popper.is-light {
  background: var(--bg-card);
  border-color: var(--border-color);
  color: var(--text-primary);
}

html.dark .el-popper.is-light .el-popper__arrow::before {
  background: var(--bg-card);
  border-color: var(--border-color);
}

html.dark .el-menu--horizontal .el-menu-item:not(.is-disabled):hover {
  background-color: rgba(255,255,255,0.1);
}

/* Global dark mode text/background */
html.dark .page-title {
  color: var(--text-primary) !important;
}

html.dark .hint {
  color: var(--text-muted) !important;
}

html.dark .el-steps {
  --el-text-color-primary: var(--text-primary);
  --el-text-color-regular: var(--text-secondary);
  --el-text-color-placeholder: var(--text-muted);
}

html.dark .el-step__title {
  color: var(--text-primary) !important;
}

html.dark .el-step__title.is-finish {
  color: #67c23a !important;
}

html.dark .el-step__title.is-process {
  color: #409eff !important;
}

html.dark .el-step__description {
  color: var(--text-muted) !important;
}

html.dark .el-step__description.is-finish {
  color: var(--text-secondary) !important;
}

html.dark .el-timeline-item__content {
  color: var(--text-primary);
}

html.dark .el-timeline-item__timestamp {
  color: var(--text-muted) !important;
}

html.dark body {
  background-color: var(--bg-secondary);
  color: var(--text-primary);
}

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

