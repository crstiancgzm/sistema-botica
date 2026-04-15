import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLayoutStore = defineStore('layout', () => {
  const drawerOpen = ref(true)

  function closeDrawer()  { drawerOpen.value = false }
  function openDrawer()   { drawerOpen.value = true  }
  function toggleDrawer() { drawerOpen.value = !drawerOpen.value }

  return { drawerOpen, closeDrawer, openDrawer, toggleDrawer }
})
