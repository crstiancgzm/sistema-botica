import { api } from "src/boot/axios";

class PresentacionService {
    static async getData(params) {
        return (await api.get('/api/presentaciones',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/presentaciones/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/presentaciones/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/presentaciones", reg)).data;
        } else {
            return (await api.put(`api/presentaciones/${reg.id}`, reg)).data;
        }
    }
}

export default PresentacionService;