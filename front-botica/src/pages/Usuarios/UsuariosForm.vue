<template>
  <!-- content -->
  <q-card class="my-card">
    <q-card-section>
    <div class="row">
          <div class="text-h6">{{ title }}</div>
            <q-space/>
            <q-btn v-close-popup round size="sm" flat>
              <q-icon name="close" />
            </q-btn>
        </div>
    </q-card-section>
    {{ form }}
    <q-form @submit.prevent="submit">
      <q-card-section class="q-pa-md">
        <q-input
          dense
          outlined
          v-model="form.name"
          :loading="form.validating"
          label="Nombre *"
          @update:model-value="form.validate('name')"
          :error="form.invalid('name')"
          :class="form.invalid('name') ? 'q-mb-sm' : ''"
          ><template v-slot:prepend>
            <q-icon name="mdi-account" />
          </template>
          <template v-slot:error>
            <div>
              {{ form.errors.name }}
            </div>
          </template>
        </q-input>
        <q-input
          dense
          outlined
          v-model="form.email"
          label="Email *"
          type="email"
          @change="form.validate('email')"
          :error="form.invalid('email')"
          :class="form.invalid('email') ? 'q-mb-sm' : ''"
        >
          <template v-slot:prepend>
            <q-icon name="mail" />
          </template>
          <template v-slot:error>
            <div>
              {{ form.errors.email }}
            </div>
          </template>
        </q-input>
        <q-input
          dense
          outlined
          v-model="form.password"
          :type="isPwd ? 'password' : 'text'"
          label="Constraseña *"
          @change="form.validate('password')"
          :error="form.invalid('password')"
          :class="form.invalid('password') ? 'q-mb-sm' : ''"
        >
          <template v-slot:append>
            <q-icon
              :name="isPwd ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              @click="isPwd = !isPwd"
            />
          </template>
          <template v-slot:prepend>
            <q-icon name="lock" />
          </template>
          <template v-slot:error>
            <div>
              {{ form.errors.password }}
            </div>
          </template>
        </q-input>

            <SelectGeneralAsync :add="false" optionLabel="nombre"  
                :serviceApi="AreasService" optionValue="id" label="Sede"
                prependIcon="mdi-format-list-bulleted-square" v-model="form.area" v-model:id="form.area_id">
            </SelectGeneralAsync>
        <!-- <SelectArea
          ref="areaSelectRef"
          class="q-mb-md"
          label="Area de Trabajo"
          :id="form.area_id || idSelectArea"
          @selectedItem="updateArea($event)"
        ></SelectArea> -->
        <div class="row q-col-gutter-xs q-ma-xs"  >
        <div class="col-12 col-md-6">
          <q-card class="q-pa-md"  flat bordered>
            <div class="q-mb-md text-center">Roles</div>
            <q-list bordered separator>
              <template v-for="(p, i) in roles" :key="i">
                <q-item clickable v-ripple dense>
                  <q-item-section>
                    <q-toggle
                      keep-color
                      v-model="form.rolesSelected"
                      :label="`${p.name}`"
                      color="secondary"
                      :val="p.id"
                      hide-details
                      dense
                    >
                    </q-toggle
                  ></q-item-section>
                </q-item>
              </template>
            </q-list>
          </q-card>
        </div>
        <div class="col-12 col-md-6">
          <q-card class="q-pa-md" flat bordered>
            <div class="q-mb-sm text-center">Permisos</div>
            <div class="q-my-xs" style="align-items: start">
              <q-select
                dense
                outlined
                v-model="selectedPermiso"
                clearable
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                label="Permisos"
                :options="permisos"
                option-label="name"
                option-value="id"
              >
                <template v-slot:prepend>
                  <q-icon name="mdi-key" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:after>
                  <q-btn
                    color="blue"
                    outline
                    round
                    dense
                    flat
                    icon="add"
                    @click="storePermiso"
                  />
                </template>
              </q-select>
            </div>
            <q-list class="row q-col-gutter-xs">
              <template v-for="(p, i) in filteredPermisos" :key="i">
                <q-item clickable v-ripple dense>
                  <q-item-section>
                    <q-toggle
                      keep-color
                      v-model="form.permisosSelected"
                      :label="`${p.name}`"
                      color="secondary"
                      :val="p.id"
                      hide-details
                      dense
                    >
                    </q-toggle
                  ></q-item-section>
                </q-item>
              </template>
            </q-list>
          </q-card>
        </div>
        </div>
      </q-card-section>
      <q-separator />

      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup></q-btn>
        <q-btn outline
          label="Guardar"
          :loading="form.processing"
          type="submit"
          color="positive"
        ></q-btn>
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { useForm } from "laravel-precognition-vue";
import { onMounted, ref, computed } from "vue";
import RolService from "src/services/RolService";
import PermisoService from "src/services/PermisoService";
import AreasService from "src/services/AreaService";
import { useUserStore } from "src/stores/user-store";
const userStore = useUserStore();
const isPwd = ref(true);
const roles = ref([]);
const permisos = ref([]);
const listPermisos = ref([]);
const emits = defineEmits(["save"]);

const idSelectArea = ref(null);
// const idSelectPermiso = ref(null);
const props = defineProps({
  title: String,
  id: Number,
  edit: {
    type: Boolean,
    default: false,
  },
});

let form;
if (props.edit) {
  form = useForm("put", "api/usuarios/" + props.id, {
    id: 0,
    name: "",
    email: "",
    password: "",
    area_id: null,
    permiso_id: null,
    rolesSelected: [],
    permisosSelected: [],
    correo_modificado: false,
    correo_original: "",
  });
} else {
  form = useForm("post", "api/usuarios", {
    id: 0,
    name: "",
    email: "",
    password: "",
    area_id: "",
    permiso_id: "",
    rolesSelected: [],
    permisosSelected: [],
    correo_modificado: false,
    correo_original: "",
  });
}

const filteredPermisos = computed(() => {
  return permisos.value.filter((p) => form.permisosSelected.includes(p.id));
});

const selectedPermiso = ref(null);

const storePermiso = () => {
  if (selectedPermiso.value) {
    if (!form.permisosSelected.includes(selectedPermiso.value.id)) {
      form.permisosSelected.push(selectedPermiso.value.id);
    }
    selectedPermiso.value = null;
  }
};

async function cargar() {
  const { data } = await RolService.getData({
    params: { rowsPerPage: 0, order_by: "id" },
  });
  roles.value = data;
  // console.log(roles.value);
}

async function permiso() {
  const { data } = await PermisoService.getData({
    params: { rowsPerPage: 0, order_by: "id" },
  });
  // console.log(data);
  permisos.value = data;
  // console.log(permisos.value);
}

function updateArea(event) {
  // console.log("gaaaa");
  if (event) {
    form.area_id = event.id;
    idSelectArea.value = event.id;
  } else {
    idSelectArea.value = null;
    form.area_id = null;
  }
}

const areaSelectRef = ref("");
const permisoSelectRef = ref("");

function setValue(values) {
  form.value = values;
  areaSelectRef.value.get(form.value.area_id);
  // permisoSelectRef.value.get(form.value.permiso_id);

  console.log(form.value);
}

const submit = () => {
  form.correo_modificado = form.email === form.correo_original ? false : true;
  if (form.password === "") {
    // Si la contraseña está vacía, eliminar el campo de contraseña del objeto de solicitud
    delete form.password;
  }
  form
    .submit()
    .then((response) => {
      form.reset();
      // form.setData()

      emits("save");
    })
    .catch((error) => {
      // alert("An error occurred.");
    });
};

onMounted(() => {
  // setData();
  console.log(props.edit);
  cargar();
  permiso();
  // console.log(form);
  // console.log(form);
});

defineExpose({
  // setData,
  setValue,
  form,
});
</script>
<style lang="sass" scoped>
p
  font-size: 12px
  line-height: 1

.my-card
  width: 100%
  max-width: 80vw
</style>
