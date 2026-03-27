import { api } from "src/boot/axios";

class CategoriaService {
    static async getData(params) {
        return (await api.get('/api/categorias',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/categorias/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/categorias/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("api/categorias", reg)).data;
        } else {
            return (await api.put(`api/categorias/${reg.id}`, reg)).data;
        }
    }
}

export default CategoriaService;