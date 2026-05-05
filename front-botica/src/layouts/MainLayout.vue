<template>
  <q-layout view="hHh lpR fFf" :style="$q.dark.isActive ? 'background: #121212' : 'background: #f0f0f0'">
    <q-header :elevated="$q.dark.isActive ? false : true" :style="$q.dark.isActive ? 'background: #1d1d1d' : 'background: #ff84b0;'" height-hint="64">
      <q-toolbar class="GNL__toolbar">
        <q-btn flat dense round @click="layoutStore.toggleDrawer()" aria-label="Menu" icon="menu" class="q-mr-sm" />

        <q-toolbar-title v-if="$q.screen.gt.xs" shrink class="row items-center no-wrap">
          <!-- <img src="https://cdn.quasar.dev/img/layout-gallery/logo-google.svg"> -->
          <span class="text-white text-weight-bold text-overline" style="font-size: 24px; font-family: 'Dancing Script', cursive;">Boticas Puno</span>
        </q-toolbar-title>

        <q-space />

        <!-- <q-input class="GNL__toolbar-input bg-white" outlined dense v-model="search" placeholder="Search for topics, locations & sources">
          <template v-slot:prepend>
            <q-icon v-if="search === ''" name="search" />
            <q-icon v-else name="clear" class="cursor-pointer" @click="search = ''" />
          </template>
          <template v-slot:append>
            <q-btn
              flat
              dense
              round
              aria-label="Menu"
              icon="arrow_drop_down"
            >
              <q-menu anchor="bottom end" self="top end">
                <div class="q-pa-md" style="width: 400px">
                  <div class="text-body2 text-grey q-mb-md">
                    Narrow your search results
                  </div>

                  <div class="row items-center">
                    <div class="col-3 text-subtitle2 text-grey">
                      Exact phrase
                    </div>
                    <div class="col-9 q-pl-md">
                      <q-input dense v-model="exactPhrase" />
                    </div>

                    <div class="col-3 text-subtitle2 text-grey">
                      Has words
                    </div>
                    <div class="col-9 q-pl-md">
                      <q-input dense v-model="hasWords" />
                    </div>

                    <div class="col-3 text-subtitle2 text-grey">
                      Exclude words
                    </div>
                    <div class="col-9 q-pl-md">
                      <q-input dense v-model="excludeWords" />
                    </div>

                    <div class="col-3 text-subtitle2 text-grey">
                      Website
                    </div>
                    <div class="col-9 q-pl-md">
                      <q-input dense v-model="byWebsite" />
                    </div>

                    <div class="col-12 q-pt-lg row justify-end">
                      <q-btn flat dense no-caps color="grey-7" size="md" style="min-width: 68px;" label="Search" v-close-popup />
                      <q-btn flat dense no-caps color="grey-7" size="md" style="min-width: 68px;" @click="onClear" label="Clear" v-close-popup />
                    </div>
                  </div>
                </div>
              </q-menu>
            </q-btn>
          </template>
        </q-input> -->

        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">
          <q-btn v-if="$q.screen.gt.sm" round dense flat color="text-grey-7" icon="apps">
            <q-tooltip>Google Apps</q-tooltip>
          </q-btn>
          <SwitchDarkMode  />
          <q-btn v-if="$q.screen.gt.sm" round dense flat icon="notifications">
            <q-badge text-color="white" floating>
              2
            </q-badge>
            <q-tooltip>Notifications</q-tooltip>
          </q-btn>
          <NavUser/>
          <!-- <q-btn round dense flat>
            <q-avatar :size="$q.screen.gt.sm ? '40px' : '26px'">
              <img src="https://cdn.quasar.dev/img/avatar3.jpg">
            </q-avatar>
            <q-tooltip>Account</q-tooltip>
          </q-btn> -->
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="layoutStore.drawerOpen"
      show-if-above
      bordered
      :class="$q.dark.isActive ? 'text-white' : 'bg-white text-grey-8'" 
      :width="280"
    >
      <q-scroll-area class="fit">
        <q-list padding :class="$q.dark.isActive ? 'text-white' : 'text-grey-8'">
          <q-item-label class="text-subtitle2 text-bold q-ml-md q-mb-xs">Menú Principal</q-item-label>
          <template v-for="link in menuList.links1" :key="link.text">
            <q-item v-if="!link.permission || userStore.hasPermission(link.permission)" class="GNL__drawer-item" active-class="GNL__my-menu-link" v-ripple clickable @click="enlace = link.text" :to="{ name: link.text }" :active="enlace === link.text">
              <q-item-section avatar>
                <q-icon :color='$q.dark.isActive ? "white" : "dark"' :name="link.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ link.text }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>
          <q-separator inset class="q-my-sm" />
          <q-item-label class="text-subtitle2 text-bold q-ml-md q-mb-xs">Roles y Permisos</q-item-label>
          <template v-for="link in menuList.links2" :key="link.text">
            <q-item v-if="!link.permission || userStore.hasPermission(link.permission)" class="GNL__drawer-item" active-class="GNL__my-menu-link" v-ripple clickable @click="enlace = link.text" :to="{ name: link.text }" :active="enlace === link.text">
              <q-item-section avatar>
                <q-icon :color='$q.dark.isActive ? "white" : "dark"' :name="link.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ link.text }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>

          <q-separator inset class="q-my-sm" />
          <q-item-label class="text-subtitle2 text-bold q-ml-md q-mb-xs">Modulo Inventario</q-item-label>
          <template v-for="link in menuList.links3" :key="link.text">
            <q-item v-if="!link.permission || userStore.hasPermission(link.permission)" class="GNL__drawer-item" active-class="GNL__my-menu-link" v-ripple clickable @click="enlace = link.text" :to="{ name: link.text }" :active="enlace === link.text">
              <q-item-section avatar>
                <q-icon :color='$q.dark.isActive ? "white" : "dark"' :name="link.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ link.text }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>

          <q-separator inset class="q-my-sm" />
          <q-item-label class="text-subtitle2 text-bold q-ml-md q-mb-xs text-primary">Modulo Alquiler</q-item-label>
          <template v-for="link in menuList.links4" :key="link.text">
            <q-item v-if="!link.permission || userStore.hasPermission(link.permission)" class="GNL__drawer-item" active-class="GNL__my-menu-link" v-ripple clickable @click="enlace = link.text" :to="{ name: link.text }" :active="enlace === link.text">
              <q-item-section avatar>
                <q-icon :color='$q.dark.isActive ? "white" : "grey-8"' :name="link.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ link.text }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>

          <q-separator inset class="q-my-sm" />
          <q-item-label class="text-subtitle2 text-bold q-ml-md q-mb-xs text-primary">Modulo Reporte</q-item-label>
          <template v-for="link in menuList.links5" :key="link.text">
            <q-item v-if="!link.permission || userStore.hasPermission(link.permission)" class="GNL__drawer-item" active-class="GNL__my-menu-link" v-ripple clickable @click="enlace = link.text" :to="{ name: link.text }" :active="enlace === link.text">
              <q-item-section avatar>
                <q-icon :color='$q.dark.isActive ? "white" : "grey-8"' :name="link.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ link.text }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>

          <!-- <div class="q-mt-md">
            <div class="flex flex-center q-gutter-xs">
              <a class="GNL__drawer-footer-link" href="javascript:void(0)" aria-label="Privacy">Privacy</a>
              <span> · </span>
              <a class="GNL__drawer-footer-link" href="javascript:void(0)" aria-label="Terms">Terms</a>
              <span> · </span>
              <a class="GNL__drawer-footer-link" href="javascript:void(0)" aria-label="About">About Google</a>
            </div>
          </div> -->
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import menuList from 'src/layouts/MenuList.js'
import SwitchDarkMode from 'src/components/SwitchDarkMode.vue'
import NavUser from 'src/components/NavUser.vue'
import { useLayoutStore } from 'src/stores/layout-store'
import { useUserStore } from 'src/stores/user-store'
import { useRoute } from "vue-router"

const layoutStore = useLayoutStore()
const userStore = useUserStore()

const route = useRoute();
const enlace = ref(route.name);


</script>

<style lang="sass">
.GNL

  &__toolbar
    height: 64px

  &__toolbar-input
    width: 55%

  &__drawer-item
    line-height: 24px
    border-radius: 0 24px 24px 0
    margin-right: 12px

    .q-item__section--avatar
      .q-icon
        color: #5f6368

    .q-item__label
      letter-spacing: .01785714em
      font-size: .875rem
      font-weight: 500
      line-height: 1.25rem

  &__my-menu-link
    color: #ff84b0
    border-left: 6px solid #ff84b0
    border-radius: 8px
    margin-left: 2px
    


    &:hover
      color: #ff84b0

.q-card
  border-radius: 10px !important;
.q-btn
  border-radius: 10px !important;
.q-field__control
  border-radius: 10px !important;

.q-table th:last-child
  border-top-right-radius: 10px;

.q-table th:first-child
  border-top-left-radius: 10px;

</style>