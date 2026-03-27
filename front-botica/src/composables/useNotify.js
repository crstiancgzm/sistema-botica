import { useQuasar } from 'quasar'

export function useNotify() {
  const $q = useQuasar()

  const notifySuccess = (message = 'Guardado con Exito.', position = 'top-right') => {
    $q.notify({
      type: 'positive',
      message: message,
      position: position,
      progress: true,
      timeout: 2000,
    })
  }

  const notifyError = (message = 'Hubo un error.', position = 'top-right') => {
    $q.notify({
      type: 'negative',
      message: message,
      position: position,
      progress: true,
      icon: 'error',
      timeout: 3000,
    })
  }

  const notifyAlert = (message = 'Alerta!.', position = 'top-right') => {
    $q.notify({
      type: 'warning',
      message: message,
      position: position,
      progress: true,
      timeout: 3000,
    })
  }

  // Puedes agregar otros tipos de notificaciones si lo deseas

  return { notifySuccess, notifyError, notifyAlert }
}
