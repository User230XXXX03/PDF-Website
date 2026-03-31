import request from '../utils/request'

export function getTemplates(params = {}) {
  return request({
    url: '/templates.php',
    method: 'get',
    params
  })
}

export function getTemplate(id) {
  return request({
    url: `/templates.php?id=${id}`,
    method: 'get'
  })
}

export function createTemplate(data) {
  return request({
    url: '/templates.php',
    method: 'post',
    data
  })
}

export function updateTemplate(id, data) {
  return request({
    url: `/templates.php?id=${id}`,
    method: 'put',
    data
  })
}

export function deleteTemplate(id) {
  return request({
    url: `/templates.php?id=${id}`,
    method: 'delete'
  })
}

