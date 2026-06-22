import axios from 'axios'
import { addCredentialsInterceptor } from '@/infrastructure/interceptors/CredentialsInterceptor'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_BASE_API_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

addCredentialsInterceptor(apiClient)

export default apiClient
