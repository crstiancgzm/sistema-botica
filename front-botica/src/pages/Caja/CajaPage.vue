<template>
  <q-page class="cash-pos-page q-pa-md">
    <div class="cash-pos-shell">

      <!-- TOPBAR -->
      <div class="pos-topbar q-mb-md">
        <div class="topbar-left">
          <div class="brand-box">
            <q-icon name="point_of_sale" size="20px" />
          </div>

          <div>
            <div class="brand-title">Boticas Puno</div>
            <div class="brand-subtitle">Panel de Caja</div>
          </div>

          <div class="topbar-tabs gt-sm q-ml-lg">
            <div class="topbar-tab topbar-tab--active">Punto de Caja</div>
            <!-- <div class="topbar-tab ">Turnos</div>
            <div class="topbar-tab ">Resumen</div> -->
          </div>
        </div>

        <div class="topbar-right">
          <div class="mini-status" :class="cajaAbierta?.id ? 'mini-status--success' : 'mini-status--muted'">
            <span class="status-dot"></span>
            {{ cajaAbierta?.id ? `Caja ${cajaAbierta.turno ? '· ' + cajaAbierta.turno : ''} abierta` : 'Caja cerrada' }}
          </div>

          <q-btn v-if="userStore.hasPermission('admin-caja-ver')"
            flat
            round
            dense
            icon="refresh"
            color="grey-7"
            :loading="loading"
            @click="cargar"
          >
            <q-tooltip>Actualizar</q-tooltip>
          </q-btn>
        </div>
      </div>

      <!-- CABECERA DE CONTENIDO -->
      <div class="section-header q-mb-md">
        <div>
          <div class="section-title">Punto de Caja</div>
          <div class="section-subtitle">{{ fechaHoy }} · {{ horaActual }}</div>
        </div>
      </div>

      <!-- METRICAS -->
      <div class="metrics-grid q-mb-md">
        <q-card flat class="metric-card">
          <q-card-section class="metric-card__section">
            <div>
              <div class="metric-label">Total del día</div>
              <div class="metric-value">S/ {{ totalDia }}</div>
            </div>
            <div class="metric-icon metric-icon--pink">
              <q-icon name="bi-graph-up-arrow" size="18px" />
            </div>
          </q-card-section>
        </q-card>

        <q-card flat class="metric-card">
          <q-card-section class="metric-card__section">
            <div>
              <div class="metric-label">Ventas realizadas</div>
              <div class="metric-value">{{ totalVentas }}</div>
            </div>
            <div class="metric-icon metric-icon--blue">
              <q-icon name="bi-receipt" size="18px" />
            </div>
          </q-card-section>
        </q-card>

        <q-card flat class="metric-card">
          <q-card-section class="metric-card__section">
            <div>
              <div class="metric-label">Turno activo</div>
              <div class="metric-value text-capitalize">{{ cajaAbierta?.turno ?? '—' }}</div>
            </div>
            <div class="metric-icon metric-icon--amber">
              <q-icon name="bi-clock-history" size="18px" />
            </div>
          </q-card-section>
        </q-card>

        <q-card flat class="metric-card">
          <q-card-section class="metric-card__section">
            <div>
              <div class="metric-label">Estado</div>
              <div class="metric-value">{{ cajaAbierta?.id ? 'Abierta' : 'Cerrada' }}</div>
            </div>
            <div class="metric-icon" :class="cajaAbierta?.id ? 'metric-icon--green' : 'metric-icon--grey'">
              <q-icon :name="cajaAbierta?.id ? 'bi-unlock-fill' : 'bi-lock-fill'" size="18px" />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- CONTENIDO -->
      <div class="content-grid">

        <!-- PANEL IZQUIERDO -->
        <div class="left-panel">
          <q-card flat class="panel-card side-card">
            <div class="panel-card__header">
              <div class="panel-title">Estado actual</div>
              <div class="panel-badge" :class="cajaAbierta?.id ? 'panel-badge--success' : 'panel-badge--muted'">
                {{ cajaAbierta?.id ? 'Abierta' : 'Cerrada' }}
              </div>
            </div>

            <template v-if="cajaAbierta?.id">
              <div class="status-box status-box--open">
                <div class="status-icon status-icon--green">
                  <q-icon name="bi-unlock-fill" size="22px" />
                </div>
                <div class="status-title">Caja abierta</div>
                <div class="status-subtitle">Turno {{ cajaAbierta.turno }}</div>
              </div>

              <div class="detail-list">
                <div class="detail-item">
                  <div class="detail-item__label">
                    <q-icon name="bi-calendar3" size="13px" class="q-mr-sm" />
                    Fecha
                  </div>
                  <div class="detail-item__value">{{ formatFecha(cajaAbierta.fecha) }}</div>
                </div>

                <div class="detail-item">
                  <div class="detail-item__label">
                    <q-icon name="bi-clock" size="13px" class="q-mr-sm" />
                    Apertura
                  </div>
                  <div class="detail-item__value">{{ formatHora(cajaAbierta.hora_apertura) }}</div>
                </div>

                <div class="detail-item">
                  <div class="detail-item__label">
                    <q-icon name="payments" size="13px" class="q-mr-sm" />
                    Monto inicial
                  </div>
                  <div class="detail-item__value">S/ {{ Number(cajaAbierta.monto_inicial).toFixed(2) }}</div>
                </div>

                <div class="detail-item">
                  <div class="detail-item__label">
                    <q-icon name="bi-person-fill" size="13px" class="q-mr-sm" />
                    Cajero
                  </div>
                  <div class="detail-item__value">{{ cajaAbierta.usuario_apertura?.name ?? '—' }}</div>
                </div>
              </div>

              <q-btn v-if="userStore.hasPermission('admin-caja-cerrar')"
                unelevated
                no-caps
                icon="bi-lock-fill"
                label="Cerrar caja"
                class="full-width action-button action-button--danger q-mt-md"
                @click="abrirDialogCerrar"
              />
            </template>

            <template v-else>
              <div class="status-box status-box--closed">
                <div class="status-icon status-icon--grey">
                  <q-icon name="bi-lock-fill" size="22px" />
                </div>
                <div class="status-title">Caja cerrada</div>
                <div class="status-subtitle">
                  {{ turnos.length === 0 ? 'Turnos del día completados' : 'Abrí la caja para comenzar' }}
                </div>
              </div>

              <template v-if="turnos.length > 0">
                <q-btn v-if="userStore.hasPermission('admin-caja-abrir')"
                  unelevated
                  no-caps
                  icon="bi-unlock-fill"
                  label="Abrir caja"
                  class="full-width action-button action-button--primary q-mt-md"
                  @click="abrirDialogAbrir"
                />
              </template>

              <template v-else>
                <div class="empty-note">
                  <q-icon name="bi-check-circle" size="20px" />
                  <span>Completaste los dos turnos de hoy</span>
                </div>
              </template>
            </template>
          </q-card>
        </div>

        <!-- PANEL DERECHO -->
        <div class="right-panel">
          <q-card flat class="panel-card main-card">
            <div class="panel-card__header panel-card__header--large">
              <div>
                <div class="panel-title">Turnos del día</div>
                <div class="panel-caption">Seguimiento de apertura, ventas y recaudación</div>
              </div>
            </div>

            <div class="turnos-stack">
              <q-card
                v-for="turno in ['mañana', 'tarde']"
                :key="turno"
                flat
                class="turno-card"
                :class="estadoCaja(turno) === 'abierta' ? 'turno-card--active' : ''"
              >
                <q-card-section class="q-pa-md">
                  <div class="turno-card__top">
                    <div class="turno-card__title-wrap">
                      <div
                        class="turno-card__icon"
                        :class="turno === 'mañana' ? 'turno-card__icon--morning' : 'turno-card__icon--afternoon'"
                      >
                        <q-icon :name="turno === 'mañana' ? 'wb_sunny' : 'nights_stay'" size="18px" />
                      </div>

                      <div>
                        <div class="turno-card__title text-capitalize">Turno {{ turno }}</div>
                        <div class="turno-card__subtitle">{{ turno === 'mañana' ? 'AM' : 'PM' }}</div>
                      </div>
                    </div>

                    <div
                      class="state-chip"
                      :class="{
                        'state-chip--success': estadoCaja(turno) === 'abierta',
                        'state-chip--closed': estadoCaja(turno) === 'cerrada',
                        'state-chip--idle': !estadoCaja(turno)
                      }"
                    >
                      {{
                        estadoCaja(turno) === 'abierta'
                          ? 'Abierta'
                          : estadoCaja(turno) === 'cerrada'
                          ? 'Cerrada'
                          : 'Sin actividad'
                      }}
                    </div>
                  </div>

                  <template v-if="cajaPorTurno(turno)">
                    <div class="turno-stats">
                      <div class="turno-stat">
                        <div class="turno-stat__label">Recaudado</div>
                        <div class="turno-stat__value turno-stat__value--money">
                          S/ {{ Number(cajaPorTurno(turno).ventas_sum_total ?? 0).toFixed(2) }}
                        </div>
                      </div>

                      <div class="turno-stat">
                        <div class="turno-stat__label">Ventas</div>
                        <div class="turno-stat__value">
                          {{ cajaPorTurno(turno).ventas_count ?? 0 }}
                        </div>
                      </div>
                    </div>

                    <div class="turno-meta">
                      <div class="turno-meta__item">
                        <q-icon name="bi-door-open" size="12px" class="q-mr-xs" />
                        Apertura {{ formatHora(cajaPorTurno(turno).hora_apertura) }}
                      </div>

                      <div v-if="cajaPorTurno(turno).hora_cierre" class="turno-meta__item">
                        <q-icon name="bi-door-closed" size="12px" class="q-mr-xs" />
                        Cierre {{ formatHora(cajaPorTurno(turno).hora_cierre) }}
                      </div>
                    </div>
                  </template>

                  <template v-else>
                    <div class="turno-empty">
                      <q-icon name="bi-dash-circle" size="18px" />
                      <span>Sin actividad en este turno</span>
                    </div>
                  </template>
                </q-card-section>
              </q-card>
            </div>
          </q-card>
        </div>
      </div>

      <!-- DIALOG ABRIR -->
      <q-dialog v-model="dialogAbrir" persistent>
        <q-card class="dialog-card">
          <q-card-section class="dialog-header dialog-header--primary">
            <div class="row items-center no-wrap full-width">
              <div class="dialog-header__icon">
                <q-icon name="bi-unlock-fill" size="16px" />
              </div>
              <div>
                <div class="dialog-title">Abrir caja</div>
                <div class="dialog-subtitle">Configurá turno y monto inicial</div>
              </div>
              <q-space />
              <q-btn flat round dense icon="close" color="white" v-close-popup />
            </div>
          </q-card-section>

          <q-card-section class="q-pa-lg q-gutter-md">
            <div class="input-label">Seleccionar turno</div>

            <q-btn-toggle
              v-model="formAbrir.turno"
              spread
              no-caps
              unelevated
              toggle-color="primary"
              class="toggle-modern"
              :options="turnosOpts"
            />

            <q-input
              v-model.number="formAbrir.monto_inicial"
              label="Efectivo al abrir (S/.)"
              dense
              outlined
              type="number"
              min="0"
              step="0.10"
            >
              <template v-slot:prepend>
                <q-icon name="payments" />
              </template>
            </q-input>
          </q-card-section>

          <q-card-actions align="right" class="q-px-lg q-pb-lg">
            <q-btn flat no-caps label="Cancelar" color="grey-7" v-close-popup />
            <q-btn
              unelevated
              no-caps
              color="primary"
              icon="bi-unlock-fill"
              label="Abrir caja"
              :loading="guardando"
              :disable="!formAbrir.turno"
              @click="confirmarAbrir"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- DIALOG CERRAR -->
      <q-dialog v-model="dialogCerrar" persistent>
        <q-card class="dialog-card">
          <q-card-section class="dialog-header dialog-header--danger">
            <div class="row items-center no-wrap full-width">
              <div class="dialog-header__icon">
                <q-icon name="bi-lock-fill" size="16px" />
              </div>
              <div>
                <div class="dialog-title">Cerrar caja</div>
                <div class="dialog-subtitle">Registrá el efectivo final del turno</div>
              </div>
              <q-space />
              <q-btn flat round dense icon="close" color="white" v-close-popup />
            </div>
          </q-card-section>

          <q-card-section class="q-pa-lg q-gutter-md">
            <div v-if="cajaAbierta?.id" class="row items-center q-gutter-sm">
              <q-chip
                dense
                no-caps
                class="mini-chip"
                :class="cajaAbierta.turno === 'mañana' ? 'mini-chip--morning' : 'mini-chip--afternoon'"
                :icon="cajaAbierta.turno === 'mañana' ? 'wb_sunny' : 'nights_stay'"
              >
                Turno {{ cajaAbierta.turno }}
              </q-chip>
              <span class="text-caption text-grey-6">Abierta {{ formatHora(cajaAbierta.hora_apertura) }}</span>
            </div>

            <q-input
              v-model.number="formCerrar.monto_final"
              label="Efectivo al cerrar (S/.)"
              dense
              outlined
              type="number"
              min="0"
              step="0.10"
              :rules="[v => (v !== null && v !== '' && v >= 0) || 'Ingresá el monto al cerrar']"
            >
              <template v-slot:prepend>
                <q-icon name="payments" />
              </template>
            </q-input>

            <q-input
              v-model="formCerrar.observaciones"
              label="Observaciones (opcional)"
              dense
              outlined
              type="textarea"
              rows="2"
            />
          </q-card-section>

          <q-card-actions align="right" class="q-px-lg q-pb-lg">
            <q-btn flat no-caps label="Cancelar" color="grey-7" v-close-popup />
            <q-btn
              unelevated
              no-caps
              color="negative"
              icon="bi-lock-fill"
              label="Cerrar caja"
              :loading="guardando"
              @click="confirmarCerrar"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <q-inner-loading :showing="loading">
        <q-spinner-pie size="90px" color="primary" />
      </q-inner-loading>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useQuasar, date } from 'quasar';
import CajaService from 'src/services/CajaService';

const $q = useQuasar();
import { useUserStore } from "src/stores/user-store";
const userStore = useUserStore(); 
const loading = ref(false);
const guardando = ref(false);
const cajaAbierta = ref(null);
const cajasHoy = ref([]);
const dialogAbrir = ref(false);
const dialogCerrar = ref(false);
const horaActual = ref(date.formatDate(new Date(), 'HH:mm'));

const formAbrir = ref({ turno: null, monto_inicial: 0 });
const formCerrar = ref({ monto_final: null, observaciones: '' });

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
  turnos.value.map(t => ({
    label: t.charAt(0).toUpperCase() + t.slice(1),
    value: t
  }))
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
    cajasHoy.value = Array.isArray(hoy) ? hoy : [];
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Error al cargar estado de caja.',
      position: 'top-right'
    });
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
    await CajaService.abrir({
      turno: formAbrir.value.turno,
      monto_inicial: formAbrir.value.monto_inicial ?? 0
    });

    $q.notify({
      type: 'positive',
      message: `Caja turno ${formAbrir.value.turno} abierta.`,
      position: 'top-right'
    });

    dialogAbrir.value = false;
    await cargar();
  } catch (e) {
    $q.notify({
      type: 'negative',
      message: e.response?.data?.message ?? 'Error al abrir la caja.',
      position: 'top-right'
    });
  } finally {
    guardando.value = false;
  }
}

async function confirmarCerrar() {
  if (!cajaAbierta.value?.id) return;

  guardando.value = true;
  try {
    await CajaService.cerrar(cajaAbierta.value.id, {
      monto_final: formCerrar.value.monto_final,
      observaciones: formCerrar.value.observaciones
    });

    $q.notify({
      type: 'positive',
      message: 'Caja cerrada correctamente.',
      position: 'top-right'
    });

    dialogCerrar.value = false;
    await cargar();
  } catch (e) {
    $q.notify({
      type: 'negative',
      message: e.response?.data?.message ?? 'Error al cerrar la caja.',
      position: 'top-right'
    });
  } finally {
    guardando.value = false;
  }
}
</script>

<style scoped>
.cash-pos-page {
  
  min-height: 100vh;
  background: #f7f7f5;
  /* font-family: 'JetBrains Mono', monospace; */
  /* font-weight: 500; */
}

.cash-pos-shell {
  max-width: 1600px;
  margin: 0 auto;
}

.pos-topbar {
  min-height: 64px;
  background: #ffffff;
  border: 1px solid #e9eaf0;
  border-radius: 14px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.topbar-left,
.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-box {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #ef476f, #f87088);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-title {
  font-size: 16px;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.1;
}

.brand-subtitle {
  font-size: 12px;
  color: #8b94a7;
  line-height: 1.2;
}

.topbar-tabs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.topbar-tab {
  font-size: 13px;
  font-weight: 700;
  color: #6b7280;
  padding: 10px 14px;
  border-radius: 10px;
  cursor: default;
}

.topbar-tab--active {
  background: #fdecef;
  color: #df4668;
}

.mini-status {
  height: 36px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border-radius: 10px;
  padding: 0 12px;
  font-size: 13px;
  font-weight: 600;
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #6b7280;
}

.mini-status--success {
  background: #f6fbf8;
  color: #4b7a5d;
}

.mini-status--muted {
  background: #fafafa;
  color: #7b8190;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #44c47d;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.section-title {
  font-size: 28px;
  font-weight: 800;
  color: #111827;
  line-height: 1.1;
}

.section-subtitle {
  margin-top: 4px;
  font-size: 13px;
  color: #8b94a7;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.metric-card {
  border: 1px solid #e9eaf0;
  border-radius: 14px;
  background: #fff;
}

.metric-card__section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  min-height: 92px;
}

.metric-label {
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  color: #9aa2b1;
  margin-bottom: 8px;
}

.metric-value {
  font-size: 28px;
  line-height: 1;
  font-weight: 800;
  color: #111827;
}

.metric-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
}

.metric-icon--pink { background: #ef5b7b; }
.metric-icon--blue { background: #4a84f5; }
.metric-icon--amber { background: #f5a524; }
.metric-icon--green { background: #38b26b; }
.metric-icon--grey { background: #98a2b3; }

.content-grid {
  display: grid;
  grid-template-columns: 350px 1fr;
  gap: 14px;
  align-items: start;
}

.panel-card {
  background: #fff;
  border: 1px solid #e9eaf0;
  border-radius: 16px;
  overflow: hidden;
}

.side-card,
.main-card {
  min-height: 100%;
}

.panel-card__header {
  padding: 16px 18px;
  border-bottom: 1px solid #eef0f4;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.panel-card__header--large {
  padding-bottom: 14px;
}

.panel-title {
  font-size: 16px;
  font-weight: 800;
  color: #1f2937;
}

.panel-caption {
  margin-top: 4px;
  font-size: 12px;
  color: #8b94a7;
}

.panel-badge {
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 11px;
  font-weight: 800;
}

.panel-badge--success {
  background: #ecfaf1;
  color: #31995a;
}

.panel-badge--muted {
  background: #f3f4f6;
  color: #7b8190;
}

.status-box {
  margin: 18px;
  padding: 22px 18px;
  border-radius: 14px;
  text-align: center;
}

.status-box--open {
  background: #f4fbf7;
  border: 1px solid #ddf2e5;
}

.status-box--closed {
  background: #f8f9fb;
  border: 1px solid #eceff3;
}

.status-icon {
  width: 54px;
  height: 54px;
  margin: 0 auto 12px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-icon--green {
  background: #38b26b;
  color: #fff;
}

.status-icon--grey {
  background: #9aa2b1;
  color: #fff;
}

.status-title {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
}

.status-subtitle {
  margin-top: 6px;
  font-size: 13px;
  color: #7b8190;
}

.detail-list {
  padding: 0 18px;
  display: grid;
  gap: 10px;
}

.detail-item {
  padding: 12px 14px;
  border: 1px solid #edf0f4;
  border-radius: 12px;
  background: #fafbfc;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.detail-item__label {
  display: flex;
  align-items: center;
  font-size: 13px;
  color: #6b7280;
  font-weight: 600;
}

.detail-item__value {
  font-size: 13px;
  color: #111827;
  font-weight: 700;
  text-align: right;
}

.action-button {
  margin: 10px 18px 18px;
  height: 46px;
  border-radius: 12px;
  font-weight: 700;
}

.action-button--primary {
  background: #ef5b7b;
  color: white;
}

.action-button--danger {
  background: #101219;
  color: white;
}

.empty-note {
  margin: 18px;
  padding: 14px 16px;
  border-radius: 12px;
  background: #fafbfc;
  border: 1px dashed #d8dde7;
  color: #7b8190;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
}

.turnos-stack {
  padding: 16px;
  display: grid;
  gap: 14px;
}

.turno-card {
  border: 1px solid #e9edf2;
  border-radius: 14px;
  background: #ffffff;
}

.turno-card--active {
  border-color: #d8eadf;
  background: #fcfffd;
}

.turno-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
}

.turno-card__title-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
}

.turno-card__icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.turno-card__icon--morning {
  background: #fff4df;
  color: #e79b13;
}

.turno-card__icon--afternoon {
  background: #eef2ff;
  color: #6772e5;
}

.turno-card__title {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  line-height: 1.1;
}

.turno-card__subtitle {
  margin-top: 4px;
  font-size: 12px;
  color: #9aa2b1;
}

.state-chip {
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}

.state-chip--success {
  background: #ecfaf1;
  color: #2e9b58;
}

.state-chip--closed {
  background: #f2f4f7;
  color: #667085;
}

.state-chip--idle {
  background: #f6f7f9;
  color: #98a2b3;
}

.turno-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  margin-bottom: 16px;
}

.turno-stat__label {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  color: #98a2b3;
  margin-bottom: 8px;
}

.turno-stat__value {
  font-size: 30px;
  line-height: 1;
  font-weight: 800;
  color: #111827;
}

.turno-stat__value--money {
  color: #ef5b7b;
}

.turno-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  font-size: 13px;
  color: #6b7280;
}

.turno-meta__item {
  display: flex;
  align-items: center;
}

.turno-empty {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #98a2b3;
  font-size: 14px;
  padding-top: 6px;
}

.dialog-card {
  min-width: 380px;
  border-radius: 18px !important;
  overflow: hidden;
}

.dialog-header {
  color: white;
  padding: 16px 18px;
}

.dialog-header--primary {
  background: #ef5b7b;
}

.dialog-header--danger {
  background: #101219;
}

.dialog-header__icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255,255,255,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
}

.dialog-title {
  font-size: 17px;
  font-weight: 800;
  line-height: 1.1;
}

.dialog-subtitle {
  margin-top: 4px;
  font-size: 12px;
  opacity: 0.92;
}

.input-label {
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  color: #98a2b3;
}

.toggle-modern {
  border-radius: 12px;
  overflow: hidden;
}

.mini-chip {
  font-weight: 700;
  border-radius: 999px;
}

.mini-chip--morning {
  background: #fff4df;
  color: #d88800;
}

.mini-chip--afternoon {
  background: #eef2ff;
  color: #5160df;
}

@media (max-width: 1200px) {
  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 700px) {
  .pos-topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .topbar-right {
    width: 100%;
    justify-content: space-between;
  }

  .metrics-grid {
    grid-template-columns: 1fr;
  }

  .metric-value {
    font-size: 24px;
  }

  .detail-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-item__value {
    text-align: left;
  }

  .dialog-card {
    min-width: unset;
    width: 94vw;
  }
}
</style>