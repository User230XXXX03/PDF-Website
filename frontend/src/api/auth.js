import request from '../utils/request'

export function register(data) {
  return request({
    url: '/auth.php?action=register',
    method: 'post',
    data
  })
}

export function login(data) {
  return request({
    url: '/auth.php?action=login',
    method: 'post',
    data
  })
}

export function logout() {
  return request({
    url: '/auth.php?action=logout',
    method: 'post'
  })
}

export function getCurrentUser() {
  return request({
    url: '/auth.php?action=user',
    method: 'get'
  })
}
