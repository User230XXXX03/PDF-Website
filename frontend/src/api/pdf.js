import request from '../utils/request'

// API base URL
const API_BASE_URL = 'http://localhost:8000/api'

export function generateBatchPDFs(batchId) {
  return request({
    url: `/pdf.php?action=generate_batch&id=${batchId}`,
    method: 'post'
  })
}

export function generateRecordPDF(recordId) {
  return request({
    url: `/pdf.php?action=generate_record&id=${recordId}`,
    method: 'post'
  })
}

export function getPreviewUrl(recordId) {
  return `${API_BASE_URL}/pdf.php?action=preview&id=${recordId}`
}

export function getDownloadUrl(recordId) {
  return `${API_BASE_URL}/pdf.php?action=download&id=${recordId}`
}

