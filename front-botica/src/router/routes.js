const routes = [
  {
    path: "/login",

    component: () => import("layouts/AuthLayout.vue"),
    children: [
      {
        path: "",
        name: "Login",
        component: () => import("src/pages/Auth/LoginPage.vue"),
      },
    ],
  },

  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('pages/Dashboard/DashboardPage.vue')
      },
      { 
        path: 'roles', 
        name: 'Roles',
        component: () => import('pages/Roles/RolesList.vue') 
      },
      { 
        path: 'permisos', 
        name: 'Permisos',
        component: () => import('pages/Permisos/PermisosList.vue') 
      },
      { 
        path: 'areas', 
        name: 'Areas',
        component: () => import('pages/Areas/AreasList.vue') 
      },
      { 
        path: 'users', 
        name: 'Usuarios',
        component: () => import('pages/Usuarios/UsuariosList.vue') 
      },
      { 
        path: 'inventario', 
        name: 'Inventario',
        component: () => import('pages/Inventarios/InventariosList.vue') 
      },
      { 
        path: 'subcategorias', 
        name: 'Categoria Malestar',
        component: () => import('pages/CategoriaMalestar/CategoriaMalestarList.vue') 
      },
      { 
        path: 'categorias', 
        name: 'Categorias',
        component: () => import('pages/Categorias/CategoriasList.vue') 
      },
      { 
        path: 'laboratorios', 
        name: 'Laboratorios',
        component: () => import('pages/Laboratorios/LaboratoriosList.vue') 
      },
      { 
        path: 'proveedores', 
        name: 'Proveedores',
        component: () => import('pages/Proveedores/ProveedoresList.vue') 
      },
      {
        path: 'presentaciones',
        name: 'Presentaciones',
        component: () => import('pages/Presentaciones/PresentacionesList.vue')
      },
      {
        path: 'venta',
        name: 'Venta',
        component: () => import('pages/Ventas/VentasList.vue')
      },
      {
        path: 'caja',
        name: 'Caja',
        component: () => import('pages/Caja/CajaPage.vue')
      },
      {
        path: 'cajas',
        name: 'Historial',
        component: () => import('pages/Caja/CajaList.vue')
      },
      {
        path: 'cajas/:id',
        name: 'CajaDetalle',
        component: () => import('pages/Caja/CajaDetalle.vue')
      },
      {
        path: 'auditoria',
        name: 'Auditoria',
        component: () => import('pages/Auditoria/AuditoriaList.vue')
      },
      // {
      //   path: 'alquiler/:id',
      //   name: 'grupo-alquiler',
      //   component: () => import('pages/Alquileres/Grupos/GruposAlquiler.vue')
      // },
      // {
      //   path: 'grupos/:id',
      //   name: 'grupo-detalle',
      //   component: () => import('pages/Alquileres/Grupos/GruposDetalle.vue')
      // },
      // {
      //   path: 'caja',
      //   name: 'Caja',
      //   component: () => import('pages/Alquileres/Cajas/CajasList.vue')
      // }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
