import axios from 'axios'
import { API_BASE_URL } from '../utils/constants'
import { tokenUtils } from '../utils/token'

export const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
    timeout: 30000,
})

apiClient.interceptors.request.use(
    (config) => {
        const token = tokenUtils.get()
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            tokenUtils.remove()
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)
