<template>
  <q-dialog v-model="formInventarios" persistent>
    <InventariosForm :title="title" :edit="edit" :id="editId" ref="inventariosformRef" prop-path="inventario"
      @save="save"></InventariosForm>
  </q-dialog>
  <q-page>
    <!-- <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el label="Productos" icon="mdi-package-variant" />
      </q-breadcrumbs>
    </div>
    <q-separator /> -->
    <div class="q-gutter-xs q-pa-sm">
    </div>
    <q-card class="q-ma-xs q-pa-md" flat :bordered="!$q.dark.isActive">
      <div class="row q-col-gutter-xs">
        <div>
          <q-btn class="q-pa-sm" outline color="primary" :disable="loading" :label="$q.screen.lt.sm ? '' : 'Agregar'" icon-right="add"
            @click="{ formInventarios = true; edit = false; title = 'REGISTRAR PRODUCTO'; editId = null;}" />
        </div>
        <div class="col">
          <q-input active-class="text-white" standout="bg-primary" color="white" dense debounce="500" v-model="filter"
            placeholder="Buscar">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </div>
    </q-card>
    <q-card class="q-ma-xs" flat :bordered="!$q.dark.isActive">
      <q-table flat :bordered="!$q.dark.isActive" table-header-class="my-custom" :rows-per-page-options="[7, 10, 15]"
        class="my-sticky-header-table htable q-ma-sm" ref="tableRef" color="red-13" :rows="rows" :columns="columns"
        row-key="id" v-model:pagination="pagination" :loading="loading" :filter="filter" binary-state-sort
        @request="onRequest">
        <!-- <template v-slot:top-right>

        </template> -->
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
              <template v-if="col.name === 'flag_blister'">
                <q-chip :color="props.row.flag_blister === 1 ? 'positive' : 'red-13'" text-color="white" size="sm">
                  s/. {{ props.row.flag_blister === 1 ? props.row.precio_blister : 'S/P' }}
                </q-chip>
              </template>
              <template v-else-if="col.name === 'flag_unidad'">
                <q-chip :color="props.row.flag_unidad === 1 ? 'positive' : 'red-13'" text-color="white" size="sm">
                  s/. {{ props.row.flag_unidad === 1 ? props.row.precio_unidad : 'S/P' }}
                </q-chip>
              </template>
              <template v-else>
                {{ col.value }}
              </template>
            </q-td>
            <q-td auto-width>
              <q-btn size="sm" text-color="cyan-8" color="cyan-1" outline round @click="editar(props.row.id)"
                icon="edit" class="q-mr-xs">
              </q-btn>
              <q-btn size="sm" text-color="red-13" color="red-1" outline round @click="eliminar(props.row.id)"
                icon="delete" />
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </q-card>
    <q-inner-loading :showing="loading">
        <q-spinner-cube size="180px" :color="$q.dark.isActive ? 'white' : 'primary'" />
    </q-inner-loading>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue";
import InventarioService from "src/services/InventarioService";
import { useQuasar } from "quasar";
import InventariosForm from "src/pages/Inventarios/InventariosForm.vue";
const $q = useQuasar();
const columns = [
  {
    name: "id",
    label: "ID",
    aling: "center",
    field: (row) => row.id,
    sortable: true,
  },
  {
    name: "nombre",
    label: "NOMBRE DEL PRODUCTO",
    aling: "center",
    field: (row) => row.nombre,
    sortable: true,
  },
  {
    name: "fecha_vencimiento",
    label: "FECHA VENCIMIENTO",
    aling: "center",
    field: (row) => row.fecha_vencimiento,
    sortable: true,
  },
  {
    name: "cantidad_presentacion",
    label: "CANTIDAD Y PRESENTACIÓN",
    aling: "center",
    field: (row) => row.cantidad + " " + row.presentacion,
    sortable: true,
  },
  {
    name: "flag_blister",
    label: "BLISTER",
    aling: "center",
    field: (row) => row.flag_blister,
    sortable: true,
  },
  {
    name: "flag_unidad",
    label: "UNIDAD",
    aling: "center",
    field: (row) => row.flag_unidad,
    sortable: true,
  },
  {
    name: "lote",
    label: "LOTE",
    aling: "center",
    field: (row) => row.lote,
    sortable: true,
  }
];
const tableRef = ref();
const formInventarios = ref(false);
const inventariosformRef = ref();
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
  rowsPerPage: 7,
  rowsNumber: 10,
});

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  const filter = props.filter;
  loading.value = true;

  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage;
  const order_by = descending ? "-" + sortBy : sortBy;
  const { data, total = 0 } = await InventarioService.getData({
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
  formInventarios.value = false;
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
  title.value = "Editar Inventario";
  formInventarios.value = true;
  edit.value = true;
  editId.value = id;
  const row = await InventarioService.get(id);
  console.log(row);

  inventariosformRef.value.form.setData({ inventario: row });

}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await InventarioService.delete(id);
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
  height: 75vh

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

.htable
  #height: calc(100vh - 157px)
</style>