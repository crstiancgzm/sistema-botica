import { api } from "src/boot/axios";

class CategoriaMalestarService {
    static async getData(params) {
        return (await api.get('/api/subcategorias',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/subcategorias/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/subcategorias/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/subcategorias", reg)).data;
        } else {
            return (await api.put(`api/subcategorias/${reg.id}`, reg)).data;
        }
    }
}

export default CategoriaMalestarService;