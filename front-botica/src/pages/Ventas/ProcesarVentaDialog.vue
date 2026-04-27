<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide" persistent>
    <q-card style="width: 490px; max-width: 90vw " class="bg-grey-2">

      <!-- Header -->
      <q-card-section class="bg-dark text-white row items-center q-py-md">
        <!-- <q-icon name="bi-receipt" size="sm" class="q-mr-sm" /> -->
        <span class="text-subtitle1 text-weight-bold">Registrar Pago</span>
        <q-space />
        <q-btn flat round dense icon="close" color="white" @click="onDialogCancel" />
      </q-card-section>

      <q-card-section class="q-pa-md q-gutter-sm">
        <!-- Resumen del carrito -->
        <div class="text-overline text-grey-9 q-mb-xs ">Resumen de venta</div>
        <q-list dense separator class="rounded-borders q-mb-lg">
          <q-item v-for="item in carrito" :key="item._key" dense>
            <q-item-section>
              <q-item-label class="text-weight-medium ellipsis text-dark q-mt-sm">{{ item.nombre }}</q-item-label>
              <q-item-label caption class="text-dark">
                <q-chip dense size="sm" :color="item.tipo_venta === 'blister' ? 'orange-1' : 'blue-1'"
                  :text-color="item.tipo_venta === 'blister' ? 'orange-9' : 'blue-9'" class="q-ma-none"
                  style="padding:6px">
                  {{ item.tipo_venta === 'blister' ? 'Blister' : 'Unidad' }}
                </q-chip>
                x{{ item.qty }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <span class="text-caption text-weight-bold text-dark">
                S/. {{ (item.precio_venta * item.qty).toFixed(2) }}
              </span>
            </q-item-section>
          </q-item>
        </q-list>

        <div class="row justify-end">
          <span class="text-subtitle2 q-mr-md q-mt-sm">Total:</span>
          <span class="text-h6 text-weight-bold text-dark">S/. {{ total }}</span>
        </div>


        <!-- Cliente -->
        <div class="text-overline text-grey-9">Información del cliente</div>
        <q-toggle v-model="consumidorFinal" label="Consumidor final" color="dark" class="text-dark" />
        <template v-if="!consumidorFinal">
          <q-input v-model="form.cliente_nombre" label="Nombre del cliente" dense outlined class="q-mb-sm"
            :rules="[v => !!v || 'Requerido']" />
          <q-input v-model="form.cliente_dni" label="DNI" dense outlined mask="########"
            :rules="[v => !v || v.length === 8 || 'DNI debe tener 8 dígitos']" />
        </template>

        <!-- <q-separator /> -->

        <!-- Tipo comprobante -->
        <div class="text-overline text-grey-9" hidden>COMPROBANTE
          <q-btn-toggle dense :class="'text-dark'" v-model="form.tipo_comprobante" spread no-caps unelevated
            toggle-color="dark" :options="[
              { label: 'Boleta', value: 'boleta' },
              { label: 'Factura', value: 'factura' },
              { label: 'Sin comprobante', value: 'sin_comprobante' },
            ]" class="q-mb-sm" />
        </div>

        <!-- <q-separator /> -->

        <!-- Método de pago -->
        <!-- <div class="text-overline text-grey-9 ">MÉTODO DE PAGO</div>
        <q-btn-toggle v-model="form.metodo_pago" class="pos-toggle q-ma-md" :class="'text-dark'" spread no-caps
          unelevated toggle-color="dark" :options="[
            { label: 'Efectivo', value: 'efectivo', icon: 'payments' },
            { label: 'Tarjeta', value: 'tarjeta', icon: 'credit_card' },
            { label: 'Yape', value: 'transferencia', icon: 'smartphone' },
          ]" /> -->

        <!-- <div class="pos-pay-row">
            <button class="pos-pay-btn">
              <q-icon name="payments" size="18px" /><span>Efectivo</span>
            </button>
            <button class="pos-pay-btn">
              <q-icon name="credit_card" size="18px" /><span>Tarjeta</span>
            </button>
            <button class="pos-pay-btn">
              <q-icon name="smartphone" size="18px" /><span>Yape</span>
            </button>
          </div> -->


      </q-card-section>

      <!-- Acciones -->
      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn flat label="Cancelar" no-caps color="grey-7" @click="onDialogCancel" />
        <q-btn color="dark" no-caps icon-right="bi-check-lg" :loading="loading" :disable="!formValido"
          @click="confirmar">
          Confirmar venta
        </q-btn>
      </q-card-actions>

    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useDialogPluginComponent, useQuasar } from 'quasar';
import VentaService from 'src/services/VentaService';
import { abrirBoletaPDF } from 'src/pages/Ventas/boletaPDF';

const props = defineProps({
  carrito: { type: Array, required: true },
  total: { type: String, required: true },
  tipoPago: { type: String, required: true },
});

defineEmits([...useDialogPluginComponent.emits]);
const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent();

const $q = useQuasar();
const loading = ref(false);
const consumidorFinal = ref(true);
const form = ref({
  cliente_nombre: '',
  cliente_dni: '',
  tipo_comprobante: 'boleta',
  metodo_pago: props.tipoPago,
});

const formValido = computed(() => {
  if (!consumidorFinal.value) {
    if (!form.value.cliente_nombre) return false;
    if (form.value.cliente_dni && form.value.cliente_dni.length !== 8) return false;
  }
  return true;
});

async function confirmar() {
  loading.value = true;
  try {
    const payload = {
      cliente_nombre: consumidorFinal.value ? 'Consumidor Final' : form.value.cliente_nombre,
      cliente_dni: consumidorFinal.value ? null : (form.value.cliente_dni || null),
      tipo_comprobante: form.value.tipo_comprobante,
      metodo_pago: form.value.metodo_pago,
      items: props.carrito.map(i => ({
        inventario_id: i.id,
        cantidad: i.qty,
        tipo_venta: i.tipo_venta,
        precio_venta: i.precio_venta,
      })),
    };

    const res = await VentaService.registrar(payload);
    $q.notify({ type: 'positive', message: 'Venta registrada correctamente.', position: 'top-right' });

    if (form.value.tipo_comprobante !== 'sin_comprobante') {
      abrirBoletaPDF(res.venta);
    }

    onDialogOK();
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al registrar la venta.', position: 'top-right' });
  } finally {
    loading.value = false;
  }
}
</script>
