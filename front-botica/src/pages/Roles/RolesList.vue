<template>
  <q-dialog v-model="formRoles" persistent>
    <RolesForm :title="title" :edit="edit" :id="editId" ref="rolesformRef" prop-path="rol" @save="save"></RolesForm>
  </q-dialog>
  <q-page>
      <!-- <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el label="Lista de Roles" icon="mdi-key" />
      </q-breadcrumbs> -->

      <q-toolbar style="color: #ff1744">
        <q-breadcrumbs :active-color="$q.dark.isActive ? 'white' : 'dark'" style="font-size: 14px">
          <q-breadcrumbs-el label="Dashboard" icon="home" />
          <q-breadcrumbs-el label="Roles" icon="widgets" />
          <!-- <q-breadcrumbs-el label="Toolbar" /> -->
        </q-breadcrumbs>
      </q-toolbar>
      <div class="q-pa-sm">
        <q-btn outline color="primary" :disable="loading" :label="$q.screen.lt.sm ? '' : 'Agregar'"
          icon-right="add"
          @click="
            {
              formRoles = true;
              edit = false;
              title = 'Añadir Rol';
              editId = null;
            }
          "/>
      </div>
    <!-- <q-separator inset /> -->
  <q-card class="q-ma-sm" flat :bordered="!$q.dark.isActive">
    <q-table flat :bordered="!$q.dark.isActive"
      table-header-class="my-custom"
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="LISTA DE ROLES"
      ref="tableRef"
      color="red-13"
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      :filter="filter"
      binary-state-sort
      @request="onRequest"
    >
      <template v-slot:top-right>
        <q-input
          active-class="text-white"
          standout="bg-primary"
          color="white"
          dense
          debounce="500"
          v-model="filter"
          placeholder="Buscar"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>
      <template v-slot:header="props">
        <q-tr :props="props">
          <q-th v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.label }}
          </q-th>
          <q-th auto-width> Acciones </q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            {{ col.value }}
          </q-td>
          <q-td auto-width>
            <q-btn
              size="sm"
              text-color="cyan-8"
              color="cyan-1"
              outline
              round
              @click="editar(props.row.id)"
              icon="edit"
              class="q-mr-xs"
            />
            <q-btn
              size="sm"
              text-color="red-13"
              color="red-1"
              outline
              round
              @click="eliminar(props.row.id)"
              icon="delete"
            />
          </q-td>
        </q-tr>
      </template>
    </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue";
import RolService from "src/services/RolService";
import { useQuasar } from "quasar";
import RolesForm from "src/pages/Roles/RolesForm.vue";
const $q = useQuasar();
const columns = [
  {
    name: "id",
    label: "Id",
    aling: "center",
    field: (row) => row.id,
    sortable: true,
  },
  {
    name: "name",
    label: "Nombre",
    aling: "center",
    field: (row) => row.name,
    sortable: true,
  },
];

const tableRef = ref();
const formRoles = ref(false);
const rolesformRef = ref();
const title = ref("");
const edit = ref(false);
const editId = ref();
const rows = ref([]);
const filter = ref("");
const loading = ref(false);
const pagination = ref({
  sortBy: "id",
  descending: true,
  page: 1,
  rowsPerPage: 9,
  rowsNumber: 10,
});

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  const filter = props.filter;
  loading.value = true;

  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await RolService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by },
  });
  console.log(data);
  // clear out existing data and add new
  rows.value.splice(0, rows.value.length, ...data);
  // don't forget to update local pagination object
  !total
    ? (pagination.value.rowsNumber = data.length)
    : (pagination.value.rowsNumber = total);
  pagination.value.page = page;
  pagination.value.rowsPerPage = rowsPerPage;
  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
  // ...and turn of loading indicator
  loading.value = false;
}


onMounted(() => {
  tableRef.value.requestServerInteraction();
});

const save = () => {
  formRoles.value = false;
  tableRef.value.requestServerInteraction();
  $q.notify({
    type: "positive",
    message: "Guardado con Exito.",
    position: "top-right",
    progress: true,
    timeout: 1000,
  });
};
async function editar(id) {
  title.value = "Editar Rol";
  formRoles.value = true;
  edit.value = true;
  editId.value = id;
  const row = await RolService.get(id);
  // console.log(row);

  rolesformRef.value.form.setData({rol:{
    id: row.rol.id,
    name: row.rol.name,
    permisosSelected: row.permisosSelected,
  }
  });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await RolService.delete(id);
    tableRef.value.requestServerInteraction();
    $q.notify({
      type: "positive",
      message: "Eliminado con Exito.",
      position: "top-right",
      progress: true,
      timeout: 1000,
    });
  });
}
</script>


<style lang="sass">
.my-sticky-header-table
  /* height or max-height is important */
  height: 74vh

  .q-table__top,
  .q-table__bottom,
  thead tr:first-child th
    /* bg color is important for th; just specify one */


  thead tr th
    position: sticky
    z-index: 1
    background-color: $primary
    color: white
  thead tr:first-child th
    top: 0

  /* this is when the loading indicator appears */
  &.q-table--loading thead tr:last-child th
    /* height of all previous header rows */
    top: 48px

  /* prevent scrolling behind sticky top row on focus */
  tbody
    /* height of all previous header rows */
    scroll-margin-top: 48px

</style>