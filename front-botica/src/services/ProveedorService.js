import { api } from "src/boot/axios";

class ProveedorService {
    static async getData(params) {
        return (await api.get('/api/proveedores',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/proveedores/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/proveedores/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/proveedores", reg)).data;
        } else {
            return (await api.put(`api/proveedores/${reg.id}`, reg)).data;
        }
    }
}

export default ProveedorService;