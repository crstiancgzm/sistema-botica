import { api } from "src/boot/axios";

class AreaService {
    static async getData(params) {
        return (await api.get('/api/areas',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/areas/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/areas/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/areas", reg)).data;
        } else {
            return (await api.put(`api/areas/${reg.id}`, reg)).data;
        }
    }
}

export default AreaService;