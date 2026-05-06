const links1 = [
  { icon: 'dashboard', text: 'Dashboard', route: 'Dashboard' },
];

const links2 = [
    { icon: 'bi-building', text: 'Areas', permission: 'admin-area-index' },
    { icon: 'bi-people-fill', text: 'Usuarios', permission: 'admin-usuarios-index' },
    { icon: 'bi-person-fill-lock', text: 'Roles', permission: 'admin-roles-index' },
    { icon: 'bi-person-fill-lock', text: 'Permisos', permission: 'admin-permisos-index' },
];

const links3 = [
    { icon: 'bi-file-earmark-text', text: 'Presentaciones', permission: 'admin-presentacion-index' },
    { icon: 'bi-file-earmark-text', text: 'Categorias', permission: 'admin-categoria-index' },
    { icon: 'bi-file-earmark-text', text: 'Categoria Malestar', permission: 'admin-categoria-malestar-index' },
    { icon: 'bi-file-earmark-text', text: 'Laboratorios', permission: 'admin-laboratorio-index' },
    { icon: 'bi-ui-checks', text: 'Proveedores', permission: 'admin-proveedor-index' },
    { icon: 'bi-basket-fill', text: 'Inventario', permission: 'admin-inventario-index' },
];

const links4 = [
    { icon: 'bi-shop', text: 'Venta', permission: 'admin-venta-index' },
    { icon: 'bi-bank', text: 'Caja', permission: 'admin-caja-index' },
    { icon: 'bi-bank', text: 'Historial', permission: 'admin-historial-index' },
];

const links5 = [
    { icon: 'bi-graph-up', text: 'Reportes', permission: 'admin-reporte-index' },
    { icon: 'bi-file-earmark-text', text: 'Auditoria' },
];

export default {
  links1,
  links2,
  links3,
  links4,
  links5
};