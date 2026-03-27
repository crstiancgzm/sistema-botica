import { api } from "src/boot/axios";

class LaboratorioService {
    static async getData(params) {
        return (await api.get('/api/laboratorios',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/laboratorios/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/laboratorios/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/laboratorios", reg)).data;
        } else {
            return (await api.put(`api/laboratorios/${reg.id}`, reg)).data;
        }
    }
}

export default LaboratorioService;