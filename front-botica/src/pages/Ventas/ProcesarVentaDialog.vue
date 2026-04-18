<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide" persistent>
    <q-card style="width: 490px; max-width: 90vw">

      <!-- Header -->
      <q-card-section class="bg-primary text-white row items-center q-py-md">
        <!-- <q-icon name="bi-receipt" size="sm" class="q-mr-sm" /> -->
        <span class="text-subtitle1 text-weight-bold">REGISTRAR PAGO</span>
        <q-space />
        <q-btn flat round dense icon="close" color="white" @click="onDialogCancel" />
      </q-card-section>

      <q-card-section class="q-pa-md q-gutter-sm">

        <!-- Resumen del carrito -->
        <div class="text-overline text-grey-6 q-mb-xs">RESUMEN</div>
        <q-list dense  separator class="rounded-borders q-mb-md">
          <q-item v-for="item in carrito" :key="item._key" dense>
            <q-item-section>
              <q-item-label class="text-caption text-weight-medium ellipsis">{{ item.nombre }}</q-item-label>
              <q-item-label caption>
                <q-chip dense size="sm"
                  :color="item.tipo_venta === 'blister' ? 'orange-1' : 'blue-1'"
                  :text-color="item.tipo_venta === 'blister' ? 'orange-9' : 'blue-9'"
                  class="q-ma-none" style="padding:6px">
                  {{ item.tipo_venta === 'blister' ? 'Blister' : 'Unidad' }}
                </q-chip>
                x{{ item.qty }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <span class="text-caption text-weight-bold text-primary">
                S/. {{ (item.precio_venta * item.qty).toFixed(2) }}
              </span>
            </q-item-section>
          </q-item>
        </q-list>

        <div class="row justify-end">
          <span class="text-subtitle2 text-grey-7 q-mr-sm">Total:</span>
          <span class="text-h6 text-weight-bold text-primary">S/. {{ total }}</span>
        </div>


        <!-- Cliente -->
        <div class="text-overline text-grey-6">CLIENTE</div>
        <q-toggle v-model="consumidorFinal" label="Consumidor final" color="primary" class="q-mb-sm" />
        <template v-if="!consumidorFinal">
          <q-input v-model="form.cliente_nombre" label="Nombre del cliente" dense outlined
            class="q-mb-sm" :rules="[v => !!v || 'Requerido']" />
          <q-input v-model="form.cliente_dni" label="DNI" dense outlined mask="########"
            :rules="[v => !v || v.length === 8 || 'DNI debe tener 8 dígitos']" />
        </template>

        <q-separator />

        <!-- Tipo comprobante -->
        <div class="text-overline text-grey-6">COMPROBANTE</div>
        <q-btn-toggle dense
          v-model="form.tipo_comprobante"
          spread no-caps unelevated
          toggle-color="primary"
          :options="[
            { label: 'Boleta',   value: 'boleta' },
            { label: 'Factura',  value: 'factura' },
            { label: 'Sin comprobante', value: 'sin_comprobante' },
          ]"
          class="q-mb-sm"
        />

        <q-separator />

        <!-- Método de pago -->
        <div class="text-overline text-grey-6 ">MÉTODO DE PAGO</div>
        <q-btn-toggle dense
          v-model="form.metodo_pago"
          spread no-caps unelevated
          toggle-color="primary"
          :options="[
            { label: 'Efectivo',       value: 'efectivo' },
            { label: 'Tarjeta',        value: 'tarjeta' },
            { label: 'Transferencia',  value: 'transferencia' },
          ]"
          class="q-mb-sm"
        />


      </q-card-section>

      <!-- Acciones -->
      <q-card-actions align="right" class="q-px-md q-pb-md">
        <q-btn flat label="Cancelar" color="grey-7" @click="onDialogCancel" />
        <q-btn color="primary" icon-right="bi-receipt-cutoff" label="Confirmar venta"
          :loading="loading" :disable="!formValido" @click="confirmar" />
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
  total:   { type: String, required: true },
});

defineEmits([...useDialogPluginComponent.emits]);
const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } = useDialogPluginComponent();

const $q = useQuasar();
const loading         = ref(false);
const consumidorFinal = ref(true);
const form = ref({
  cliente_nombre:   '',
  cliente_dni:      '',
  tipo_comprobante: 'boleta',
  metodo_pago:      'efectivo',
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
      cliente_nombre:   consumidorFinal.value ? 'Consumidor Final' : form.value.cliente_nombre,
      cliente_dni:      consumidorFinal.value ? null : (form.value.cliente_dni || null),
      tipo_comprobante: form.value.tipo_comprobante,
      metodo_pago:      form.value.metodo_pago,
      items: props.carrito.map(i => ({
        inventario_id: i.id,
        cantidad:      i.qty,
        tipo_venta:    i.tipo_venta,
        precio_venta:  i.precio_venta,
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
