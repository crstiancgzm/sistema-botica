import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'
import { Cookies } from 'quasar'
import { secureStorage } from 'src/utils/SecureStorage'

export const useUserStore = defineStore('user', {
  state: () => ({
    id: null,
    name: null,
    email: null,
    alias: null,
    roles: null,
    permisos: null,
  }),
  getters: {
    getId: (state) => state.id,
    getName: (state) => state.name,
    getEmail: (state) => state.email,
    getRoles: (state) => state.roles,
    getPermisos: (state) => state.permisos,
    getAlias: (state) => state.alias,
    getArea: (state) => state.area,
    getAreaId: (state) => state.area_id
  },
  actions: {
    async login(email, password) {
      Cookies.remove('token')
      secureStorage.removeItem('user')
      try {
        const res = await api.post('oauth/token', {
          grant_type: 'password',
          client_id: '0197ce3d-db28-7138-a61b-177a734849fa',
          client_secret: 'C9zEkfPjdUrDcUuFDjxv7myST0N61O8sb0s7YOdM',
          username: email,
          password: password,
          scope: '',
        })
        // .then(() => {
        let tokenString = 'Bearer ' + res.data.access_token
        Cookies.set('token', tokenString, { path: '/' })
        // });

        // return res;
      } catch (e) {
        if (e) throw e
      }
      // this.getUser();
    },
    // async loginGoogle(token) {
    //   Cookies.remove("token");
    //   secureStorage.removeItem("user");
    //   let tokenString = "Bearer " + token;
    //   Cookies.set("token", tokenString, { path: "/" });
    // },
    async getUser() {
      try {
        const user = await api.get('api/user')
        secureStorage.setItem('user', user.data.user)
        // console.log(user.data);
        this.setUser(user.data)
        console.log(user.data.user)
      } catch (e) {
        if (e) throw e
      }
    },

    async logout() {
      // Cookies.remove("token");
      console.log('saliendo xd ... ')
      Cookies.remove('token', { path: '/' })
      // secureStorage.clear();
      this.clearUser()
    },

    setUser(payload) {
      if (payload.user.id) this.id = payload.user.id
      if (payload.user.name) this.name = payload.user.name
      if (payload.user.email) this.email = payload.user.email
      if (payload.user.alias) this.alias = payload.user.alias
      if (payload.permisos) this.permisos = payload.permisos
      if (payload.roles) this.roles = payload.roles
      if (payload.user.area_id) this.area_id = payload.user.area_id;
      if (payload.user.area?.nombre) this.area = payload.user.area.nombre;
    },

    clearUser() {
      this.id = null
      this.name = null
      this.email = null
      this.permisos = null
      this.roles = null
      this.alias = null
      this.area_id = null
      this.area = null
    },
    hasPermission(permission) {
      return this.permisos?.includes(permission)
    },
  },
  persist: {
    enabled: true,
    strategies: [
      {
        key: 'UserStore', // Nombre en el localStorage
        storage: localStorage, // Almacenar en localStorage (puedes usar sessionStorage también)
        // Puedes persistir solo partes del estado
        // paths: ["items", "total"], // Las propiedades que serán persistidas
      },
    ],
  },
})
