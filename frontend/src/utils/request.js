import axios from 'axios'
import { ElMessage } from 'element-plus'

const request = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 30000,
  withCredentials: true
})

// Request interceptor
request.interceptors.request.use(
  config => {
    return config
  },
  error => {
    console.error('Request error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor
request.interceptors.response.use(
  response => {
    const res = response.data
    
    if (res.success === false) {
      ElMessage.error(res.message || 'Request failed')
      
      // Redirect to login if unauthorized
      if (response.status === 401) {
        sessionStorage.removeItem('user')
        window.location.href = '/login'
      }
      
      return Promise.reject(new Error(res.message || 'Request failed'))
    }
    
    return res
  },
  error => {
    console.error('Response error:', error)
    
    let message = 'Network error'
    if (error.response) {
      if (error.response.status === 401) {
        message = 'Unauthorized. Please login.'
        sessionStorage.removeItem('user')
        window.location.href = '/login'
      } else if (error.response.data && error.response.data.message) {
        message = error.response.data.message
      }
    }
    
    ElMessage.error(message)
    return Promise.reject(error)
  }
)

export default request
