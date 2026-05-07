import { api } from "src/boot/axios";

class InventarioService {
    static async getData(params) {
        return (await api.get('/api/inventarios',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/inventarios/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/inventarios/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/inventarios", reg)).data;
        } else {
            return (await api.put(`api/inventarios/${reg.id}`, reg)).data;
        }
    }

    static async updateStock(id, cantidad) {
        return (await api.patch(`api/inventarios/${id}/stock`, { cantidad })).data;
    }

    static async getRelacionados(id) {
        return (await api.get(`/api/inventarios/${id}/relacionados`)).data;
    }
}

export default InventarioService;