import request from '../utils/request'

export function getDashboardStats() {
  return request({
    url: '/dashboard.php',
    method: 'get'
  })
}

