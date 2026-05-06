import { api } from 'src/boot/axios'

class AuditService {
  static async getData({ params } = {}) {
    return (await api.get('/api/auditoria', { params })).data
  }
}

export default AuditService
