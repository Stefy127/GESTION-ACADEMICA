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
                    <li><i class="bi bi-file-earmark-excel text-success me-2"></i>Excel (.xlsx, .xls)</li>
                    <li><i class="bi bi-file-earmark-text text-info me-2"></i>CSV (.csv)</li>
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
            <div class="btn-group">
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>Plantilla Docentes
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>Plantilla Materias
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>Plantilla Grupos
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>Plantilla Aulas
                </a>
                <a href="#" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i>Plantilla Horarios
                </a>
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
                    <div class="mb-3">
                        <label class="form-label">Tipo de Datos</label>
                        <input type="text" class="form-control" id="dataType" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Archivo</label>
                        <input type="file" class="form-control" id="fileInput" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Formatos soportados: Excel (.xlsx, .xls) y CSV (.csv)</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="validateData" checked>
                            <label class="form-check-label" for="validateData">
                                Validar datos antes de importar
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="processUpload()">
                    <i class="bi bi-upload me-1"></i>Procesar Archivo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openUploadModal(type) {
    document.getElementById('dataType').value = type;
    document.getElementById('fileInput').value = '';
    new bootstrap.Modal(document.getElementById('uploadModal')).show();
}

function processUpload() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Por favor selecciona un archivo');
        return;
    }
    
    // Simular procesamiento
    alert('Archivo procesado exitosamente');
    bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
}
</script>
