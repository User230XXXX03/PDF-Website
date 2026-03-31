import request from '../utils/request'

export function sendBatchEmails(batchId) {
  return request({
    url: `/email.php?action=send_batch&id=${batchId}`,
    method: 'post'
  })
}

export function sendRecordEmail(recordId) {
  return request({
    url: `/email.php?action=send_record&id=${recordId}`,
    method: 'post'
  })
}

export function getEmailSettings() {
  return request({
    url: '/email.php?action=settings',
    method: 'get'
  })
}

export function updateEmailSettings(data) {
  return request({
    url: '/email.php?action=settings',
    method: 'put',
    data
  })
}

