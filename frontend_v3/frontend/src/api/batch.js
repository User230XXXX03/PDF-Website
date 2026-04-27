import request from '../utils/request'

export function getBatches(params) {
  return request({
    url: '/batches.php',
    method: 'get',
    params
  })
}

export function getBatch(id) {
  return request({
    url: `/batches.php?id=${id}`,
    method: 'get'
  })
}

export function createBatch(data) {
  return request({
    url: '/batches.php',
    method: 'post',
    data
  })
}

export function updateBatch(id, data) {
  return request({
    url: `/batches.php?id=${id}`,
    method: 'put',
    data
  })
}

export function deleteBatch(id) {
  return request({
    url: `/batches.php?id=${id}`,
    method: 'delete'
  })
}

