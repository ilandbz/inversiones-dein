<script setup>
import { ref, onMounted, computed } from 'vue';
import useCredito from '@/Composables/Credito';
import useDesembolso from '@/Composables/Desembolso';
import useDatosSession from '@/Composables/session';
import FormArchivos from '@/Pages/Evaluacion/FormArchivos.vue';
import useHelper from '@/Helpers';

const { obtenerCreditos, creditos, cambiarEstadoCredito, respuesta } = useCredito();
const { agregarDesembolso, respuesta: respuestaDesem } = useDesembolso();
const { usuario } = useDatosSession();
const { openModal, Toast, Swal, formatoFecha } = useHelper();

const selectedId = ref(null);
const selectedClienteNombre = ref('');

const dato = ref({
    page: 1,
    buscar: '',
    paginacion: 10,
    estado: 'APROBADO',
});

const listarCreditos = async (page = 1) => {
    dato.value.page = page;
    await obtenerCreditos(dato.value);
};

const archivos = (credito) => {
    selectedId.value = credito.id;
    selectedClienteNombre.value = credito.cliente?.persona?.apenom || '';
    openModal('#archivosModal');
};

const desembolsar = async (creditoObj) => {
    const { isConfirmed, value } = await Swal.fire({
        title: 'Realizar Desembolso',
        html: `
            <div class="text-start">
                <p><b>Cliente:</b> ${creditoObj.cliente.persona.apenom}</p>
                <p><b>Monto Crédito:</b> S/. ${Number(creditoObj.monto).toFixed(2)}</p>
                
                <div class="mb-2">
                    <label class="form-label">Fecha de Desembolso</label>
                    <input id="swal-fecha" type="date" class="form-control" value="${formatoFecha(null, "YYYY-MM-DD")}">
                </div>

                <div class="mb-2">
                    <label class="form-label">Descontado (S/.)</label>
                    <input id="swal-descontado" type="number" step="0.01" class="form-control" value="0.00">
                </div>

                <div class="mb-2">
                    <label class="form-label">Total Entregado (S/.)</label>
                    <input id="swal-totalentregado" type="number" step="0.01" class="form-control" value="${Number(creditoObj.monto).toFixed(2)}">
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Confirmar Desembolso',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fecha = document.getElementById('swal-fecha').value;
            const descontado = document.getElementById('swal-descontado').value;
            const totalentregado = document.getElementById('swal-totalentregado').value;

            if (!fecha || !totalentregado) {
                Swal.showValidationMessage('La fecha y el total entregado son obligatorios');
                return;
            }

            return {
                credito_id: creditoObj.id,
                fecha: fecha,
                hora: new Date().toLocaleTimeString('it-IT'), // HH:mm:ss
                user_id: usuario.value.id,
                descontado: descontado,
                totalentregado: totalentregado,
                rcsdebe: 'PAGO'
            };
        }
    });

    if (isConfirmed) {
        await agregarDesembolso(value);
        if (respuestaDesem.value?.ok === 1) {
            Toast.fire({ icon: 'success', title: 'Desembolso realizado correctamente' });
            listarCreditos(creditos.value.current_page);
        } else {
            Toast.fire({ icon: 'error', title: 'Error al procesar el desembolso' });
        }
    }
};

// PAGINACIÓN
const offset = 2;
const isActived = () => creditos.value?.current_page;
const pagesNumber = computed(() => {
    const c = creditos.value;
    if (!c?.to) return [];
    let from = c.current_page - offset;
    if (from < 1) from = 1;
    let to = from + offset * 2;
    if (to >= c.last_page) to = c.last_page;
    const pages = [];
    for (let p = from; p <= to; p++) pages.push(p);
    return pages;
});

const buscar = () => listarCreditos(1);
const cambiarPaginacion = () => listarCreditos(1);
const cambiarPagina = (pagina) => listarCreditos(pagina);

onMounted(() => {
    listarCreditos();
});
</script>

<template>
    <div class="page-content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h6 class="card-title">Desembolso de Créditos</h6>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-1">
                            <div class="input-group mb-1">
                                <span class="input-group-text">Mostrar</span>
                                <select class="form-select" v-model="dato.paginacion" @change="cambiarPaginacion">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="input-group mb-1">
                                <span class="input-group-text">Buscar</span>
                                <input class="form-control" placeholder="Cliente, código..." type="text"
                                    v-model="dato.buscar" @change="buscar" />
                            </div>
                        </div>

                        <div class="col-md-4 mb-1">
                            <nav>
                                <ul class="pagination">
                                    <li v-if="creditos.current_page > 1" class="page-item">
                                        <a href="#" class="page-link" @click.prevent="cambiarPagina(creditos.current_page - 1)">
                                            <span><i class="fas fa-angle-left"></i></span>
                                        </a>
                                    </li>
                                    <li v-for="page in pagesNumber" :key="page" class="page-item" :class="{ active: page == isActived() }">
                                        <a href="#" class="page-link" @click.prevent="cambiarPagina(page)">{{ page }}</a>
                                    </li>
                                    <li v-if="creditos.current_page < creditos.last_page" class="page-item">
                                        <a href="#" class="page-link" @click.prevent="cambiarPagina(creditos.current_page + 1)">
                                            <span><i class="fas fa-angle-right"></i></span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-xs table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cod</th>
                                    <th>Cliente</th>
                                    <th>Asesor</th>
                                    <th>Monto</th>
                                    <th>Tasa</th>
                                    <th>Tipo</th>
                                    <th>Fecha Reg</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="creditos.total == 0">
                                    <td class="text-danger text-center" colspan="10">No hay créditos aprobados pendientes de desembolso</td>
                                </tr>
                                <tr v-else v-for="(credito, index) in creditos.data" :key="credito.id">
                                    <td>{{ index + creditos.from }}</td>
                                    <td>{{ credito.id }}</td>
                                    <td>{{ credito.cliente.persona.apenom }}</td>
                                    <td>{{ credito.asesor.user?.name }}</td>
                                    <td>{{ 'S/. ' + Number(credito.monto ?? 0).toFixed(2) }}</td>
                                    <td>{{ Number(credito.tasainteres * 100).toFixed(2) + '%' }}</td>
                                    <td>{{ credito.tipo }}</td>
                                    <td>{{ credito.fecha_reg }}</td>
                                    <td><span class="badge bg-success">{{ credito.estado }}</span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-success btn-sm" title="Desembolsar" @click.prevent="desembolsar(credito)">
                                                <i class="fas fa-money-bill-wave"></i> Desembolsar
                                            </button>
                                            <button class="btn btn-info btn-sm" title="Archivos" @click.prevent="archivos(credito)">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <FormArchivos :creditoId="selectedId" :clienteNombre="selectedClienteNombre" />
</template>
