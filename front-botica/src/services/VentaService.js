import { api } from "src/boot/axios";

class VentaService {
    static async registrar(venta) {
        return (await api.post("api/ventas", venta)).data;
    }
}

export default VentaService;
