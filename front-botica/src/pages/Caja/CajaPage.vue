<template>
  <q-page class="q-pa-md">
    <!-- ── HEADER ───────────────────────────────────── -->
    <div class="row items-center q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">PANEL DE CAJA</div>
        <div class="text-caption text-grey-5">{{ fechaHoy }} — {{ horaActual }}</div>
      </div>
      <q-space />
      <q-btn flat round dense icon="refresh" color="grey-5" :loading="loading" @click="cargar">
        <q-tooltip>Actualizar</q-tooltip>
      </q-btn>
    </div>

    <!-- ── STAT CARDS ────────────────────────────────── -->
    <div class="row q-col-gutter-xs q-mb-lg">

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card" flat>
          <q-card-section class="q-pa-md">
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="stat-label">Total del día</div>
                <div class="stat-value">S/. {{ totalDia }}</div>
              </div>
              <div class="stat-icon-wrap bg-primary">
                <q-icon name="bi-graph-up-arrow" size="22px" color="white" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card" flat>
          <q-card-section class="q-pa-md">
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="stat-label">Ventas realizadas</div>
                <div class="stat-value">{{ totalVentas }}</div>
              </div>
              <div class="stat-icon-wrap bg-teal">
                <q-icon name="bi-receipt" size="22px" color="white" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card" flat>
          <q-card-section class="q-pa-md">
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="stat-label">Turno activo</div>
                <div class="stat-value text-capitalize">{{ cajaAbierta?.turno ?? '—' }}</div>
              </div>
              <div class="stat-icon-wrap bg-orange">
                <q-icon name="bi-clock-history" size="22px" color="white" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="stat-card" :class="cajaAbierta?.id ? 'stat-green' : 'stat-grey'" flat>
          <q-card-section class="q-pa-md">
            <div class="row items-center no-wrap">
              <div class="col">
                <div class="stat-label">Estado</div>
                <div class="stat-value">{{ cajaAbierta?.id ? 'Abierta' : 'Cerrada' }}</div>
              </div>
              <div class="stat-icon-wrap" :class="cajaAbierta?.id ? 'bg-positive' : 'bg-grey-5'">
                <q-icon :name="cajaAbierta?.id ? 'bi-unlock-fill' : 'bi-lock-fill'" size="22px" color="white" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>

    <!-- ── CUERPO PRINCIPAL ──────────────────────────── -->
    <div class="row q-gutter-md">

      <!-- Acción principal -->
      <div class="col-12 col-md-4 q-mt-lg">
        <q-card flat bordered class="action-card full-height">

          <!-- Caja abierta -->
          <template v-if="cajaAbierta?.id">
            <div class="action-card__header action-card__header--open">
              <q-icon name="bi-unlock-fill" size="32px" class="q-mb-sm" />
              <div class="text-h6 text-weight-bold">CAJA ABIERTA</div>
              <q-chip dense size="sm"
                :color="cajaAbierta.turno === 'mañana' ? 'orange' : 'blue'"
                text-color="white" icon="bi-clock" class="q-mt-xs" style="padding: 12px;">
                Turno {{ cajaAbierta.turno }}
              </q-chip>
            </div>

            <div class="q-pa-md">
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-calendar3" class="q-mr-xs" />Fecha</span>
                <span class="detail-value">{{ formatFecha(cajaAbierta.fecha) }}</span>
              </div>
              <q-separator class="q-my-xs" />
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-clock" class="q-mr-xs" />Apertura</span>
                <span class="detail-value">{{ formatHora(cajaAbierta.hora_apertura) }}</span>
              </div>
              <q-separator class="q-my-xs" />
              <div class="detail-row">
                <span class="detail-label"><q-icon name="payments" class="q-mr-xs" />Monto inicial</span>
                <span class="detail-value text-weight-bold">S/. {{ Number(cajaAbierta.monto_inicial).toFixed(2) }}</span>
              </div>
              <q-separator class="q-my-xs" />
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-person-fill" class="q-mr-xs" />Cajero</span>
                <span class="detail-value">{{ cajaAbierta.usuario_apertura?.name ?? '—' }}</span>
              </div>
            </div>

            <div class="q-pa-md q-pt-none">
              <q-btn unelevated color="negative" icon="bi-lock-fill" label="Cerrar caja"
                class="full-width" size="md" @click="abrirDialogCerrar" />
            </div>
          </template>

          <!-- Sin caja -->
          <template v-else>
            <div class="action-card__header action-card__header--closed">
              <q-icon name="bi-lock-fill" size="32px" class="q-mb-sm" />
              <div class="text-h6 text-weight-bold">Caja Cerrada</div>
              <div class="text-caption q-mt-xs opacity-70">
                {{ turnos.length === 0 ? 'Turnos del día completados' : 'Abrí la caja para comenzar' }}
              </div>
            </div>

            <div class="q-pa-md" v-if="turnos.length > 0">
              <q-btn unelevated color="primary" icon="bi-unlock-fill" label="Abrir caja" 
                class="full-width" size="md" @click="abrirDialogAbrir" />
            </div>

            <div v-else class="q-pa-md column items-center text-grey-5">
              <q-icon name="bi-check-circle" size="32px" class="q-mb-xs" />
              <span class="text-caption">Completaste los dos turnos de hoy</span>
            </div>
          </template>

        </q-card>
      </div>

      <!-- Turnos del día -->
      <div class="col column q-gutter-sm">

        <div v-for="turno in ['mañana', 'tarde']" :key="turno">
          <q-card flat bordered class="turno-card"
            :class="estadoCaja(turno) === 'abierta' ? 'turno-card--active' : ''">
            <q-card-section class="q-pa-lg">

              <div class="row items-center q-mb-md">
                <div class="turno-icon-wrap q-mr-sm"
                  :class="turno === 'mañana' ? 'bg-orange-1' : 'bg-indigo-1'">
                  <q-icon :name="turno === 'mañana' ? 'wb_sunny' : 'nights_stay'"
                    :color="turno === 'mañana' ? 'orange-8' : 'indigo-5'" size="20px" />
                </div>
                <div>
                  <div class="text-subtitle1 text-weight-bold text-capitalize">Turno {{ turno }}</div>
                  <div class="text-caption text-grey-5">
                    {{ turno === 'mañana' ? 'AM' : 'PM' }}
                  </div>
                </div>
                <q-space />
                <q-badge rounded
                  :color="estadoCaja(turno) === 'abierta' ? 'positive' : estadoCaja(turno) === 'cerrada' ? 'grey-5' : 'grey-3'"
                  :label="estadoCaja(turno) === 'abierta' ? 'Abierta' : estadoCaja(turno) === 'cerrada' ? 'Cerrada' : 'Sin actividad'"
                  class="text-weight-medium"
                />
              </div>

              <template v-if="cajaPorTurno(turno)">
                <div class="row q-gutter-md items-end q-mb-lg">
                  <div>
                    <div class="text-caption text-grey-5 q-mb-xs">Recaudado</div>
                    <div class="text-h4 text-weight-bold text-primary">
                      S/. {{ Number(cajaPorTurno(turno).ventas_sum_total ?? 0).toFixed(2) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-caption text-grey-5 q-mb-xs">Ventas</div>
                    <div class="text-h5 text-weight-bold text-grey-7">
                      {{ cajaPorTurno(turno).ventas_count ?? 0 }}
                    </div>
                  </div>
                </div>

                <div class="row q-gutter-lg text-caption text-grey-5">
                  <div>
                    <q-icon name="bi-door-open" size="12px" class="q-mr-xs" />
                    Apertura {{ formatHora(cajaPorTurno(turno).hora_apertura) }}
                  </div>
                  <div v-if="cajaPorTurno(turno).hora_cierre">
                    <q-icon name="bi-door-closed" size="12px" class="q-mr-xs" />
                    Cierre {{ formatHora(cajaPorTurno(turno).hora_cierre) }}
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="row items-center q-gutter-sm text-grey-4 q-py-sm">
                  <q-icon name="bi-dash-circle" size="20px" />
                  <span class="text-body2">Sin actividad en este turno</span>
                </div>
              </template>
            </q-card-section>
          </q-card>
        </div>

      </div>
    </div>

    <!-- ── DIALOG ABRIR ──────────────────────────────── -->
    <q-dialog v-model="dialogAbrir" persistent>
      <q-card style="min-width: 360px; border-radius: 16px !important">
        <q-card-section class="bg-primary text-white row items-center q-py-md">
          <q-icon name="bi-unlock-fill" size="sm" class="q-mr-sm" />
          <span class="text-subtitle1 text-weight-bold">Abrir Caja</span>
          <q-space />
          <q-btn flat round dense icon="close" color="white" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg q-gutter-md">
          <div class="text-overline text-grey-6">SELECCIONAR TURNO</div>
          <q-btn-toggle v-model="formAbrir.turno" spread no-caps unelevated toggle-color="primary"
            :options="turnosOpts" />
          <q-input v-model.number="formAbrir.monto_inicial" label="Efectivo al abrir (S/.)"
            dense outlined type="number" min="0" step="0.10">
            <template v-slot:prepend><q-icon name="payments" /></template>
          </q-input>
        </q-card-section>

        <q-card-actions align="right" class="q-px-lg q-pb-lg">
          <q-btn flat label="Cancelar" color="grey-7" v-close-popup />
          <q-btn unelevated color="primary" icon="bi-unlock-fill" label="Abrir"
            :loading="guardando" :disable="!formAbrir.turno" @click="confirmarAbrir" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- ── DIALOG CERRAR ─────────────────────────────── -->
    <q-dialog v-model="dialogCerrar" persistent>
      <q-card style="min-width: 360px; border-radius: 16px !important">
        <q-card-section class="bg-negative text-white row items-center q-py-md">
          <!-- <q-icon name="bi-lock-fill" size="sm" class="q-mr-sm" /> -->
          <span class="text-subtitle1 text-weight-bold">CERRAR CAJA</span>
          <q-space />
          <q-btn flat round dense icon="close" color="white" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg q-gutter-md">
          <div v-if="cajaAbierta?.id" class="row items-center q-gutter-sm">
            <q-chip dense :color="cajaAbierta.turno === 'mañana' ? 'orange-1' : 'blue-1'"
              :text-color="cajaAbierta.turno === 'mañana' ? 'orange-9' : 'blue-9'" icon="bi-clock" style="padding: 18px;">
              Turno {{ cajaAbierta.turno }}
            </q-chip>
            <span class="text-caption text-grey-5">Abierta {{ formatHora(cajaAbierta.hora_apertura) }}</span>
          </div>
          <q-input v-model.number="formCerrar.monto_final" label="Efectivo al cerrar (S/.)"
            dense outlined type="number" min="0" step="0.10"
            :rules="[v => (v !== null && v !== '' && v >= 0) || 'Ingresá el monto al cerrar']">
            <template v-slot:prepend><q-icon name="payments" /></template>
          </q-input>
          <q-input v-model="formCerrar.observaciones" label="Observaciones (opcional)"
            dense outlined type="textarea" rows="2" />
        </q-card-section>

        <q-card-actions align="right" class="q-px-lg q-pb-lg">
          <q-btn flat label="Cancelar" color="grey-7" v-close-popup />
          <q-btn unelevated color="negative" icon="bi-lock-fill" label="Cerrar caja"
            :loading="guardando" @click="confirmarCerrar" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-inner-loading :showing="loading">
      <q-spinner-pie size="120px" color="primary" />
    </q-inner-loading>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useQuasar, date } from 'quasar';
import CajaService from 'src/services/CajaService';

const $q = useQuasar();

const loading     = ref(false);
const guardando   = ref(false);
const cajaAbierta = ref(null);
const cajasHoy    = ref([]);
const dialogAbrir = ref(false);
const dialogCerrar= ref(false);
const horaActual  = ref(date.formatDate(new Date(), 'HH:mm'));

const formAbrir = ref({ turno: null, monto_inicial: 0 });
const formCerrar= ref({ monto_final: null, observaciones: '' });

const fechaHoy = date.formatDate(new Date(), 'dddd DD [de] MMMM YYYY');

let reloj = null;
onMounted(() => {
  cargar();
  reloj = setInterval(() => {
    horaActual.value = date.formatDate(new Date(), 'HH:mm');
  }, 60000);
});
onUnmounted(() => clearInterval(reloj));

const turnos = computed(() => {
  const usados = cajasHoy.value.map(c => c.turno);
  return ['mañana', 'tarde'].filter(t => !usados.includes(t));
});

const turnosOpts = computed(() =>
  turnos.value.map(t => ({ label: t.charAt(0).toUpperCase() + t.slice(1), value: t }))
);

const totalDia = computed(() =>
  cajasHoy.value.reduce((sum, c) => sum + Number(c.ventas_sum_total ?? 0), 0).toFixed(2)
);

const totalVentas = computed(() =>
  cajasHoy.value.reduce((sum, c) => sum + Number(c.ventas_count ?? 0), 0)
);

function cajaPorTurno(turno) {
  return cajasHoy.value.find(c => c.turno === turno) ?? null;
}

function estadoCaja(turno) {
  const c = cajaPorTurno(turno);
  return c ? c.estado : null;
}

function formatFecha(f) {
  return f ? date.formatDate(f, 'DD/MM/YYYY') : '—';
}

function formatHora(h) {
  return h ? date.formatDate(new Date(h), 'HH:mm') : '—';
}

async function cargar() {
  loading.value = true;
  try {
    const [estado, hoy] = await Promise.all([
      CajaService.estado(),
      CajaService.hoy(),
    ]);
    cajaAbierta.value = (estado && estado.id) ? estado : null;
    cajasHoy.value    = Array.isArray(hoy) ? hoy : [];
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar estado de caja.', position: 'top-right' });
  } finally {
    loading.value = false;
  }
}

function abrirDialogAbrir() {
  formAbrir.value = { turno: turnos.value[0] ?? null, monto_inicial: 0 };
  dialogAbrir.value = true;
}

function abrirDialogCerrar() {
  formCerrar.value = { monto_final: null, observaciones: '' };
  dialogCerrar.value = true;
}

async function confirmarAbrir() {
  guardando.value = true;
  try {
    await CajaService.abrir({ turno: formAbrir.value.turno, monto_inicial: formAbrir.value.monto_inicial ?? 0 });
    $q.notify({ type: 'positive', message: `Caja turno ${formAbrir.value.turno} abierta.`, position: 'top-right' });
    dialogAbrir.value = false;
    await cargar();
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error al abrir la caja.', position: 'top-right' });
  } finally {
    guardando.value = false;
  }
}

async function confirmarCerrar() {
  if (!cajaAbierta.value?.id) return;
  guardando.value = true;
  try {
    await CajaService.cerrar(cajaAbierta.value.id, { monto_final: formCerrar.value.monto_final, observaciones: formCerrar.value.observaciones });
    $q.notify({ type: 'positive', message: 'Caja cerrada correctamente.', position: 'top-right' });
    dialogCerrar.value = false;
    await cargar();
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error al cerrar la caja.', position: 'top-right' });
  } finally {
    guardando.value = false;
  }
}
</script>

<style scoped>
/* Stat cards */
.stat-card {
  border-radius: 10px !important;
  border: none !important;
}
.stat-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 900;
  opacity: 0.7;
  margin-bottom: 4px;
}
.stat-value {
  font-size: 22px;
  font-weight: 800;
  line-height: 1.1;
}
.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-left: 12px;
}
.stat-primary { background: rgba(var(--q-primary-rgb, 255,132,176), 0.08); }
.stat-teal    { background: rgba(0,150,136,0.08); }
.stat-orange  { background: rgba(255,152,0,0.08); }
.stat-green   { background: rgba(76,175,80,0.08); }
.stat-grey    { background: rgba(150,150,150,0.06); }

/* Action card */
.action-card {
  border-radius: 10px !important;
  overflow: hidden;
}
.action-card__header {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 24px 24px;
  text-align: center;
}
.action-card__header--open {
  background: linear-gradient(135deg, rgba(76,175,80,0.15), rgba(76,175,80,0.05));
  color: var(--q-positive);
}
.action-card__header--closed {
  background: rgba(150,150,150,0.07);
  color: var(--q-grey-7);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  font-size: 13px;
}
.detail-label {
  color: var(--q-grey-6);
  display: flex;
  align-items: center;
}
.detail-value {
  font-weight: 500;
}

/* Turno cards */
.turno-card {
  border-radius: 10px !important;
  transition: border-color 0.2s;
}
.turno-card--active {
  border-color: var(--q-positive) !important;
}
.turno-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
