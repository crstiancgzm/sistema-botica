import { defineBoot } from '#q-app/wrappers'
import axios from 'axios'
import { client } from 'laravel-precognition-vue'
import { Cookies } from 'quasar'
import { useUserStore } from 'src/stores/user-store'
import { Notify } from 'quasar'

const api = axios.create({ baseURL: process.env.API_BACKEND_URL })

// window.axios.defaults.headers.common['Authorization'] = authToken
api.interceptors.request.use(
  (config) => {
    console.log('tokennnnnnnn', config)
    const token = Cookies.get('token') // Reemplaza con el token de autenticación que desees enviar
    if (token) {
      config.headers['Authorization'] = token
    }
    return config
  },
  (error) => {
    console.log('token invalido')
    return Promise.reject(error)
  },
)

client.use(api)
export default defineBoot(({ app, router, store }) => {
  const userStore = useUserStore()
  // const userStore = useUserStore(store) // <--- AQUÍ le pasas la instancia del store de Quasar (Pinia)

  api.interceptors.response.use(
    function (response) {
      // Qualquer código de status que dentro do limite de 2xx faz com que está função seja acionada
      // Faz alguma coisa com os dados de resposta
      console.log('gaaaaa')
      return response
    },
    function (error) {
      console.log('gaaaaa invalido')
      // Qualquer código de status que não esteja no limite do código 2xx faz com que está função seja acionada
      // Faz alguma coisa com o erro da resposta
      if (error.response && (error.response.status === 401 || error.response.status === 419)) {
        Notify.create({
          type: 'negative',
          message: 'Tu sesión ha expirado. Por favor vuelve a iniciar sesión.',
          position: 'top',
          progress: true,
          icon: 'ion-close-circle',
          timeout: 4000,
        })
        console.log('sesion expirado')
        // useUserStore(store).logout()
        // logout()
        // store.user.logout()
        userStore.logout()
        router.push({ name: 'Login' })

        // aquí limpias el token y rediriges al login
        // Notify.create({
        //   type: 'negative',
        //   message: 'Tu sesión ha expirado. Por favor vuelve a iniciar sesión.',
        // })
      }
      return Promise.reject(error)
    },
  )
  // console.log(userStore)
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
