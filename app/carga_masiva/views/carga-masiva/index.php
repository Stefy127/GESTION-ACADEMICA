<!-- Carga Masiva de Datos -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-upload me-3 text-primary"></i>Carga Masiva de Datos
        </h1>
        <p class="text-muted mb-0">Importa datos desde archivos Excel o CSV</p>
    </div>
</div>

<!-- Tarjetas de Tipos de Carga -->
<div class="row g-4 mb-4">
    <?php foreach ($tipos_carga as $tipo): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="<?php echo $tipo['icono']; ?> text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title"><?php echo htmlspecialchars($tipo['nombre']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                <button class="btn btn-primary" onclick="openUploadModal('<?php echo strtolower($tipo['nombre']); ?>')">
                    <i class="bi bi-upload me-1"></i>Cargar Archivo
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Instrucciones -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-info-circle me-2"></i>Instrucciones para Carga Masiva
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Formatos Soportados:</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-file-earmark-excel text-success me-2"></i>Excel (.xlsx, .xls) - Disponible</li>
                    <li><i class="bi bi-file-earmark-text text-info me-2"></i>CSV (.csv) - Disponible</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Requisitos:</h6>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-2"></i>Archivo máximo 5MB</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>Primera fila como encabezados</li>
                    <li><i class="bi bi-check-circle text-success me-2"></i>Datos válidos en todas las columnas</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-4">
            <h6>Plantillas de Ejemplo:</h6>
            <style>
                /* Asegurar que los títulos de las plantillas sean siempre visibles */
                .plantilla-card .card-title {
                    color: var(--gray-800) !important;
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
                .plantilla-card:hover .card-title {
                    color: var(--gray-800) !important;
                }
            </style>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="card plantilla-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Docentes</h6>
                            <div class="btn-group w-100">
                                <a href="/carga-masiva/plantilla/docentes?formato=csv" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>CSV
                                </a>
                                <a href="/carga-masiva/plantilla/docentes?formato=xlsx" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card plantilla-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Materias</h6>
                            <div class="btn-group w-100">
                                <a href="/carga-masiva/plantilla/materias?formato=csv" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>CSV
                                </a>
                                <a href="/carga-masiva/plantilla/materias?formato=xlsx" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card plantilla-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Grupos</h6>
                            <div class="btn-group w-100">
                                <a href="/carga-masiva/plantilla/grupos?formato=csv" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>CSV
                                </a>
                                <a href="/carga-masiva/plantilla/grupos?formato=xlsx" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card plantilla-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Aulas</h6>
                            <div class="btn-group w-100">
                                <a href="/carga-masiva/plantilla/aulas?formato=csv" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>CSV
                                </a>
                                <a href="/carga-masiva/plantilla/aulas?formato=xlsx" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card plantilla-card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Horarios</h6>
                            <div class="btn-group w-100">
                                <a href="/carga-masiva/plantilla/horarios?formato=csv" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>CSV
                                </a>
                                <a href="/carga-masiva/plantilla/horarios?formato=xlsx" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Carga -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cargar Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="tipo" id="dataType">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Datos</label>
                        <input type="text" class="form-control" id="dataTypeDisplay" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Archivo</label>
                        <input type="file" name="archivo" class="form-control" id="fileInput" accept=".csv,.xlsx,.xls" required>
                        <div class="form-text">Formatos soportados: CSV (.csv), Excel (.xlsx, .xls). Máximo 5MB</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="validateData" name="validar" checked>
                            <label class="form-check-label" for="validateData">
                                Validar datos antes de importar
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="updateExisting" name="actualizar">
                            <label class="form-check-label" for="updateExisting">
                                Actualizar registros existentes
                            </label>
                        </div>
                    </div>
                    <!-- Alertas de resultados -->
                    <div id="uploadAlerts"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="processBtn" onclick="processUpload()">
                    <i class="bi bi-upload me-1"></i>Procesar Archivo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const tipoMap = {
    'docentes': 'Docentes',
    'materias': 'Materias',
    'grupos': 'Grupos',
    'aulas': 'Aulas',
    'horarios': 'Horarios'
};

function openUploadModal(type) {
    document.getElementById('dataType').value = type;
    document.getElementById('dataTypeDisplay').value = tipoMap[type] || type;
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadAlerts').innerHTML = '';
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function processUpload() {
    const form = document.getElementById('uploadForm');
    const formData = new FormData(form);
    const processBtn = document.getElementById('processBtn');
    const alertsDiv = document.getElementById('uploadAlerts');
    
    // Limpiar alertas previas
    alertsDiv.innerHTML = '';
    
    // Validar archivo
    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files[0]) {
        alertsDiv.innerHTML = '<div class="alert alert-warning">Por favor selecciona un archivo</div>';
        return;
    }
    
    // Validar tamaño
    if (fileInput.files[0].size > 5 * 1024 * 1024) {
        alertsDiv.innerHTML = '<div class="alert alert-danger">El archivo excede el tamaño máximo de 5MB</div>';
        return;
    }
    
    // Deshabilitar botón
    processBtn.disabled = true;
    processBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
    
    fetch('/carga-masiva/procesar', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        processBtn.disabled = false;
        processBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Procesar Archivo';
        
        if (data.success) {
            const resultado = data.resultado;
            let html = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Archivo procesado exitosamente</strong>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-info">${resultado.total}</div>
                            <div class="text-muted small">Total Registros</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-success">${resultado.exitosos}</div>
                            <div class="text-muted small">Exitosos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-warning">${resultado.fallidos}</div>
                            <div class="text-muted small">Fallidos</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-primary">${resultado.total - resultado.fallidos}</div>
                            <div class="text-muted small">Importados</div>
                        </div>
                    </div>
                </div>
            `;
            
            if (resultado.errores && resultado.errores.length > 0) {
                html += `
                    <div class="alert alert-warning mt-3">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Errores encontrados:</h6>
                        <ul class="mb-0 small" style="max-height: 200px; overflow-y: auto;">
                `;
                resultado.errores.forEach(error => {
                    html += `<li>${error}</li>`;
                });
                html += `
                        </ul>
                    </div>
                `;
            }
            
            // Agregar botones de acción
            html += `
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="closeModalAndReload()">
                        <i class="bi bi-check-circle me-1"></i>Cerrar y Recargar
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="closeModalOnly()">
                        <i class="bi bi-x-circle me-1"></i>Cerrar
                    </button>
                </div>
            `;
            
            alertsDiv.innerHTML = html;
            
            // No cerrar automáticamente - el usuario debe cerrarlo manualmente
        } else {
            alertsDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        processBtn.disabled = false;
        processBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Procesar Archivo';
        alertsDiv.innerHTML = '<div class="alert alert-danger">Ocurrió un error al procesar el archivo</div>';
    });
}

function closeModalAndReload() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
    if (modal) {
        modal.hide();
    }
    setTimeout(() => {
        window.location.reload();
    }, 300);
}

function closeModalOnly() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
    if (modal) {
        modal.hide();
    }
}
</script>
