import { boot } from 'quasar/wrappers'
import SelectGeneral from 'src/components/selectGeneralAsyncrono.vue'

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async ({app}) => {
  app.component('SelectGeneralAsync', SelectGeneral)
  
})
