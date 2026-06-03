<?php
// Verificar si ya se incluyó el header desde el controller
$titulo = $titulo ?? 'Generar Reportes';
?>
<?php require_once APP_ROOT . '/views/includes/header.php'; ?>

<div class="container-fluid py-4">

    <!-- Título -->
    <div class="d-flex align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0" style="color:#ff8c00;">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Generar Reportes
            </h2>
            <p class="text-muted mb-0 mt-1">Exporta la información de reservas en Excel o PDF.</p>
        </div>
    </div>

    <!-- Formulario de filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header" style="background:#ff8c00; color:#fff;">
            <i class="bi bi-funnel me-2"></i><strong>Filtros del Reporte</strong>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado de pago</label>
                    <select id="filtro-estado" class="form-select">
                        <option value="all">Todos</option>
                        <option value="confirmada">Confirmadas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="cancelada">Canceladas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Fecha desde</label>
                    <input type="date" id="filtro-desde" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Fecha hasta
                        <small class="text-muted fw-normal">(máx. hoy)</small>
                    </label>
                    <input type="date" id="filtro-hasta" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button onclick="exportar('excel')" class="btn btn-success w-100">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button onclick="exportar('pdf')" class="btn btn-danger w-100">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </button>
                    </div>
                </div>
            </div>
            <!-- Aviso de rango -->
            <div id="aviso-rango" class="alert alert-warning mt-3 mb-0 py-2 px-3 d-none" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="aviso-rango-texto"></span>
            </div>
        </div>
    </div>

    <!-- Tarjetas informativas -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-start gap-3">
                    <div style="background:#e8f5e9; border-radius:12px; padding:14px;">
                        <i class="bi bi-file-earmark-excel-fill" style="font-size:2rem; color:#388e3c;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Reporte Excel (CSV)</h5>
                        <p class="text-muted mb-0">Incluye: listado completo de reservas, datos del cliente, paquete, monto y estado. También un resumen por paquete. Abre directamente con Microsoft Excel u OpenOffice.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-start gap-3">
                    <div style="background:#fce4ec; border-radius:12px; padding:14px;">
                        <i class="bi bi-file-earmark-pdf-fill" style="font-size:2rem; color:#c62828;"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Reporte PDF</h5>
                        <p class="text-muted mb-0">Documento profesional con encabezado de Happy Jumping, tabla de reservas con colores por estado (verde=confirmado, rojo=cancelado) y resumen general de ingresos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    const inputDesde = document.getElementById('filtro-desde');
    const inputHasta = document.getElementById('filtro-hasta');
    const avisoBox   = document.getElementById('aviso-rango');
    const avisoTexto = document.getElementById('aviso-rango-texto');

    function hoy() {
        const d = new Date();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${d.getFullYear()}-${mm}-${dd}`;
    }

    function sumarMeses(fechaStr, meses) {
        const [y, m, d] = fechaStr.split('-').map(Number);
        const fecha = new Date(y, m - 1 + meses, d);
        if (fecha.getDate() !== d) fecha.setDate(0);
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const dd = String(fecha.getDate()).padStart(2, '0');
        return `${fecha.getFullYear()}-${mm}-${dd}`;
    }

    function mostrarError(msg) {
        avisoBox.className = 'alert alert-danger mt-3 mb-0 py-2 px-3';
        avisoTexto.textContent = msg;
    }

    function ocultarAviso() {
        avisoBox.className = 'alert mt-3 mb-0 py-2 px-3 d-none';
        avisoTexto.textContent = '';
    }

    const fechaHoy = hoy();
    inputHasta.max = fechaHoy;

    inputDesde.addEventListener('change', function () {
        ocultarAviso();
        if (!this.value) {
            inputHasta.max = fechaHoy;
            return;
        }
        const maxPorRango = sumarMeses(this.value, 2);
        inputHasta.max = maxPorRango < fechaHoy ? maxPorRango : fechaHoy;

        if (inputHasta.value && inputHasta.value > inputHasta.max) {
            inputHasta.value = '';
            mostrarError('⚠️ La fecha "hasta" que tenías seleccionada supera los 2 meses permitidos desde la nueva fecha de inicio. Por favor vuelve a elegirla.');
        }
    });

    inputHasta.addEventListener('change', function () {
        ocultarAviso();
        if (!this.value) return;

        if (this.value > fechaHoy) {
            this.value = '';
            mostrarError('⚠️ La fecha "hasta" no puede ser posterior al día de hoy (' + fechaHoy + ').');
            return;
        }

        if (inputDesde.value) {
            const maxPorRango = sumarMeses(inputDesde.value, 2);
            if (this.value > maxPorRango) {
                this.value = '';
                mostrarError('⚠️ El rango seleccionado supera los 2 meses permitidos. La fecha "hasta" no puede ser mayor a ' + maxPorRango + '.');
            }
        }
    });

    window.exportar = function (tipo) {
        ocultarAviso();
        const estado      = document.getElementById('filtro-estado').value;
        const fecha_desde = inputDesde.value;
        const fecha_hasta = inputHasta.value;

        if (fecha_desde && fecha_hasta && fecha_desde > fecha_hasta) {
            mostrarError('⚠️ La fecha "desde" no puede ser mayor que la fecha "hasta".');
            return;
        }
        if (fecha_desde && fecha_hasta) {
            const maxPorRango = sumarMeses(fecha_desde, 2);
            if (fecha_hasta > maxPorRango) {
                mostrarError('⚠️ El rango de fechas supera los 2 meses permitidos.');
                return;
            }
        }
        if (fecha_hasta && fecha_hasta > fechaHoy) {
            mostrarError('⚠️ La fecha "hasta" no puede ser posterior a la fecha de hoy.');
            return;
        }

        const params = new URLSearchParams({ estado, fecha_desde, fecha_hasta });
        window.location.href = '<?= URL_ROOT ?>/reporte/' + tipo + '?' + params.toString();
    };
})();
</script>

<?php require_once APP_ROOT . '/views/includes/footer.php'; ?>