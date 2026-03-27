<template>
  <div style="display: flex; align-items: center">
    <q-img :src="imageUrl" :ratio="16 / 9" style="height: 90px; max-width: 100px; margin: 1px; border-radius: 10px;" spinner-color="primary" spinner-size="82px" ></q-img>

    <!-- Botón para visualizar la foto capturada -->

    <q-btn
    class="q-mx-sm"
    v-if="imageUrl"
    round
    outline
    size="sm"
    color="primary"
    icon="mdi-eye"
    @click="
    toggleFullScreen(
      imageUrl,
      { parametro: { nombre: 'Foto Seleccionada' }, url: imageUrl }
      )
      "
      />
      
      <q-btn
      label="Tomar Foto"
      color="primary"
      class="full-width"
      dense
      icon="photo_camera"
      @click="capturarConCamara()"
      ></q-btn>
      
      <!-- <div class="row"> -->
        <q-btn dense outline class="col-12 q-ml-md" color="primary"  icon-right="add" @click="agregar()" />
        <!-- <div class="col text-right"> -->
          <!-- </div> -->
        <!-- </div> -->
  </div>
  <!-- <q-input
    outlined
    bottom-slots
    v-model="data.comentarios"
    emit-value
    map-options
    label="Comentarios (Opcional)"
    :dense="true"
    :readonly="false"
     @keydown.enter.prevent="agregar()"
  >
    <template v-slot:before>
      <q-icon name="edit_note" color="primary" />
    </template>
  </q-input> -->
  <!-- <div v-if="modelValue.errors.fotografias" class="text-red">
    Las Fotografías son Requeridas
  </div> -->

  {{ modelValue }}
  <!-- {{ urlApi }} -->
  <q-list
    dense
    bordered
    class="rounded-borders q-mt-xs"
    v-for="(value, key) in modelValue.fotografias"
    :key="key"
    
  >
    <q-item>
      <q-item-section avatar> {{ key + 1 }} </q-item-section>

      <q-item-section>
        <q-item-label
          style="
            width: 100%;
            min-width: 100%;
            white-space: pre-wrap;
            word-wrap: break-word;
          "
        >
          <img
            :src="isLocalImage(value.url) ? value.urlTem : urlApi + value.url"
            alt="Previsualización de imagen"
            style="max-width: 100%; max-height: 90px; border-radius: 10px;"
            class="thumbnail"
            @click="
              toggleFullScreen(
                isLocalImage(value.url) ? value.urlTem : urlApi + value.url,
                value
              )
            "
          />
          <q-btn
            round
            outline
            size="sm"
            color="primary"
            icon="mdi-eye"
            style="position:absolute; z-index:3; left:47em; top:35px; opacity:0.9"
            @click="
              toggleFullScreen(
                isLocalImage(value.url) ? value.urlTem : urlApi + value.url,
                value
              )
            "
          />
        </q-item-label>
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ value.comentarios }}</q-item-label>
      </q-item-section>
      <q-item-section avatar>
        <q-btn dense flat color="red" icon-right="clear" @click="quitar(value)" />
      </q-item-section>
    </q-item>
  </q-list>
  <q-dialog v-model="isFullScreen">
    <q-card style="width: 90%; max-width: 60vw;  margin: 0; padding: 0" class="shadow-1">
    <!-- <q-card-section class="bg-primary">
        <div class="row text-white">
            <div class="text-h6">NOMBRE : {{ selectedImageName }}</div>
                <q-space />
            <q-btn v-close-popup round size="sm" unelevated>
                <q-icon name="close" />
            </q-btn>
        </div>
    </q-card-section> -->
      <!-- <q-toolbar>
        <q-toolbar-title>{{ selectedImageName }}</q-toolbar-title>
        <q-btn flat v-close-popup round dense icon="close" />
      </q-toolbar> -->
     <q-card-section class="q-pt-none flex flex-center q-mt-md">
        <q-img
            class="q-mx-auto"
            style="border-radius: 10px; object-fit: fill;"
            :src="selectedImage"
            spinner-color="primary"
            alt="Imagen en pantalla completa"
        />
        </q-card-section>
    </q-card>
  </q-dialog>

</template>

<script setup>
import { onMounted, ref, toRefs } from "vue";
// import { useEditStore } from "src/stores/edit-store";
// import ParametrosService from "src/services/ParametrosService";
import { Camera, CameraResultType } from "@capacitor/camera";
import Compressor from "compressorjs";

// const editStore = useEditStore();
const data = ref({
  id: 0,
  url: null,
  urlTem: null,
});
const imageUrl = ref(null);
const urlApi = process.env.API_BACKEND_URL;
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  tipo: {
    type: Number,
    // required: true,
    default: 100,
  },
});
const emit = defineEmits(["update:modelValue"]);
const { modelValue } = toRefs(props);
const updateValue = (key, value) => {
  emit("update:modelValue", { ...modelValue.value, [key]: value });
};

const parametros = ref([]);
function setValue(values) {}

function agregar() {
  if (!data.value.url) {
    return; // No hay imagen
  }

  // if (!data.value.comentarios || data.value.comentarios.trim() === '') {
  //   return; // Comentario vacío
  // }

  modelValue.value.fotografias.push({
    url: data.value.url,
    urlTem: imageUrl.value,
    // comentarios: data.value.comentarios,
  });

  // Limpiar
  data.value = {
    id: 0,
    url: null,
    // comentarios: null,
  };
  imageUrl.value = null;
}
// function agregar() {
//   if (
//     data.value.url != null &&
//     data.value.comentarios != ""
//   ) {
//     console.log(data.value.url);
//     modelValue.value.fotografias.push({
//       url: data.value.url,
//       urlTem: imageUrl.value,
//       comentarios: data.value.comentarios,
//     });
//     data.value = {
//       id: 0,
//       url: null,
//       comentarios: null,
//     };
//     imageUrl.value = null;
//   }
// }
function quitar(val) {
  modelValue.value.fotografias = modelValue.value.fotografias.filter((item) => {
    return !(
      item.url === val.url 
      // && item.comentarios === val.comentarios
    );
  });
}


function isLocalImage(url) {
  return url instanceof File || url.startsWith("blob:");
}


async function capturarConCamara() {
  const imagen = await Camera.getPhoto({
    quality: 90,
    resultType: CameraResultType.Uri,
    promptLabelPicture: "Abrir Camara",
    promptLabelPhoto: "Cargar desde Galeria",
    saveToGallery: true,
    width: 1440,
    height: 1920,
  });
  imageUrl.value = imagen.webPath;
  const response = await fetch(imagen.webPath);
  const blob = await response.blob();

  const file = new File([blob], imagen.path, {
    type: blob.type,
  });
  console.log("img original", file);
  // data.value.url = file;
  new Compressor(file, {
    quality: 0.6,
    convertSize: 500000,

    // The compression process is asynchronous,
    // which means you have to access the `result` in the `success` hook function.
    success(result) {
      // The third parameter is required for server
      console.log("imagen despues", result);
      const file = new File([result], imagen.path, {
        type: result.type,
      });
      data.value.url = file;
      console.log(data.value.url);
      // Send the compressed image file to server with XMLHttpRequest.
      // axios.post('/path/to/upload', formData).then(() => {
      //   console.log('Upload success');
      // });
    },
    error(err) {
      console.log(err.message);
      alert("agrega otra vez la imagen");
    },
  });
}

function clearImage() {
  data.value.url = null;
  imageUrl.value = null;
}

defineExpose({ setValue });
const isFullScreen = ref(false);
const selectedImage = ref("");
const selectedImageName = ref("imagen");

const toggleFullScreen = (url, value) => {
  selectedImage.value = url;
  selectedImageName.value = value.comentarios;
  isFullScreen.value = true;
};
</script>

<style style="css" scoped>
.rounded-tab {
  width: 40px;
  height: 5px;
  border-radius: 4px;
  background-color: rgba(200, 200, 200, 0.7);
}
.thumbnail {
  cursor: pointer;
  transition: transform 0.3s ease;
}
</style>