import { api } from "src/boot/axios";

class CajaService {
    static async estado() {
        return (await api.get("/api/caja/estado")).data;
    }

    static async hoy() {
        return (await api.get("/api/caja/hoy")).data;
    }

    static async getHistorial(params) {
        return (await api.get("/api/caja/historial", { params })).data;
    }

    static async getDetalle(id) {
        return (await api.get(`/api/caja/${id}`)).data;
    }

    static async abrir(payload) {
        return (await api.post("/api/caja/abrir", payload)).data;
    }

    static async cerrar(id, payload) {
        return (await api.put(`/api/caja/${id}/cerrar`, payload)).data;
    }
}

export default CajaService;
