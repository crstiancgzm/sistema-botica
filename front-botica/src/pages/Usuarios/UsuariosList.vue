 <template>
 <q-dialog v-model="formUser" persistent>
    <UsuariosForm
      :title="title"
      :edit="edit"
      :id="editId"
      ref="usuariosformRef"
      @save="save"
    ></UsuariosForm>
  </q-dialog>
  <q-page>
    <div class="q-pa-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el icon="home" />

        <q-breadcrumbs-el label="Usuarios" icon="mdi-account" />
      </q-breadcrumbs>
    </div>
    <q-separator />
    <div class="q-gutter-xs q-pa-sm">
      <q-btn outline
        color="primary"
        :disable="loading"
        :label="$q.screen.lt.sm ? '' : 'Agregar'"
        icon-right="add"
        @click="
          {
            formUser = true;
            edit = false;
            title = 'Añadir Usuario';
          }
        "
      />
    </div>
  <q-card class="q-ma-sm" flat :bordered="!$q.dark.isActive">
    <q-table flat :bordered="!$q.dark.isActive"
      :rows-per-page-options="[7, 10, 15]"
      class="my-sticky-header-table htable q-ma-sm"
      title="LISTA DE USUARIOS"
      ref="tableRef"
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      :filter="filter"
      binary-state-sort
      @request="onRequest"
    >
      <!-- <template v-slot:top-left>

        <q-btn
          color="primary"
          :disable="loading"
          :label="$q.screen.lt.sm ? '' : 'Agregar'"
          icon-right="add"
          @click="usuariosformRef.show = true"
        />
      </template> -->

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
          <q-th
  
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            {{ col.label }}
          </q-th>
          <q-th auto-width> Acciones </q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            <template v-if="col.name === 'active'">
              <q-badge
                :color="props.row.active ? 'positive' : 'negative'"
                :label="props.row.active ? 'Activo' : 'Inactivo'"
                class="q-pa-xs"
              />
            </template>
            <template v-else>
              {{ col.value }}
            </template>
          </q-td>
          <q-td auto-width>
            <q-btn
              size="sm"
              outline
              color="green"
              round
              @click="editar(props.row.id)"
              icon="edit"
              class="q-mr-xs"
            />
            <q-btn
              size="sm"
              :outline="props.row.active"
              :color="props.row.active ? 'orange' : 'grey'"
              round
              @click="toggleActive(props.row)"
              :icon="props.row.active ? 'person_off' : 'person'"
              class="q-mr-xs"
            >
              <q-tooltip>{{ props.row.active ? 'Dar de baja' : 'Activar' }}</q-tooltip>
            </q-btn>
            <q-btn
              size="sm"
              outline
              color="red"
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
import UsuarioService from "src/services/UsuarioService";
import { useQuasar } from "quasar";
import UsuariosForm from "src/pages/Usuarios/UsuariosForm.vue";
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
    name: "name",
    label: "USUARIO",
    aling: "center",
    field: (row) => row.name,
    sortable: true,
  },
  {
    name: "email",
    label: "EMAIL",
    aling: "center",
    field: (row) => row.email,
    sortable: true,
  },
  {
    name: "email",
    label: "SEDE",
    aling: "center",
    field: (row) => row.area?.nombre ? row.area?.nombre : 'S/S',
    sortable: true,
  },
  {
    name: "active",
    label: "ESTADO",
    align: "center",
    field: (row) => row.active,
    sortable: false,
  },
];

const tableRef = ref();
const formUser = ref(false);
const usuariosformRef = ref();
const title = ref("");
const edit = ref(false);
const editId = ref();
const rows = ref([]);
const filter = ref("");
const loading = ref(false);
const pagination = ref({
  sortBy: "id",
  descending: false,
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
  const { data, total = 0 } = await UsuarioService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by },
  });
//   console.log(data);
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
  formUser.value = false;
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
  title.value = "Editar Usuario";
  formUser.value = true;
  edit.value = true;
  editId.value = id;
  const row = await UsuarioService.get(id);
  console.log(row);

  usuariosformRef.value.form.setData({
    id: row.user.id,
    name: row.user.name,
    email: row.user.email,
    rolesSelected: row.rolesSelected,
    permisosSelected: row.permisosSelected,
    area: row.user.area,
    area_id: row.user.area_id,
  });

  // permisosformRef.value.setValue(row);
  // usuariosformRef.value.setData(row);
}

async function toggleActive(row) {
  const accion = row.active ? 'dar de baja' : 'activar';
  $q.dialog({
    title: `¿${row.active ? 'Dar de baja' : 'Activar'} usuario?`,
    message: `¿Estás seguro de ${accion} a <b>${row.name}</b>?`,
    html: true,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    const result = await UsuarioService.toggleActive(row.id);
    row.active = result.active;
    $q.notify({
      type: result.active ? 'positive' : 'warning',
      message: result.active ? 'Usuario activado.' : 'Usuario dado de baja.',
      position: 'top-right',
      timeout: 1500,
    });
  });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estas seguro de eliminar este registro?",
    message: "Este proceso es irreversible.",
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    await UsuarioService.delete(id);
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

.htable
  #height: calc(100vh - 157px)
</style>