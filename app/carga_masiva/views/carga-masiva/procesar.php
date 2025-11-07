<!-- Procesar Archivo -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-gear me-3 text-primary"></i>Procesar Archivo
        </h1>
        <p class="text-muted mb-0">Procesa y valida archivos de carga masiva</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/carga-masiva" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Carga -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Cargar Archivo</h5>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Datos</label>
                            <select class="form-select" id="dataType" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="docentes">Docentes</option>
                                <option value="materias">Materias</option>
                                <option value="grupos">Grupos</option>
                                <option value="aulas">Aulas</option>
                                <option value="horarios">Horarios</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Formato del Archivo</label>
                            <select class="form-select" id="fileFormat" required>
                                <option value="">Seleccionar formato</option>
                                <option value="excel">Excel (.xlsx, .xls)</option>
                                <option value="csv">CSV (.csv)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Seleccionar Archivo</label>
                            <input type="file" class="form-control" id="fileInput" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Tamaño máximo: 5MB</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="validateData" checked>
                                <label class="form-check-label" for="validateData">
                                    Validar datos antes de importar
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="updateExisting">
                                <label class="form-check-label" for="updateExisting">
                                    Actualizar registros existentes
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Procesar Archivo
                        </button>
                        <a href="/carga-masiva" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Resultados del Procesamiento -->
        <div class="card mt-4" id="resultsCard" style="display: none;">
            <div class="card-header">
                <h5 class="card-title mb-0">Resultados del Procesamiento</h5>
            </div>
            <div class="card-body">
                <div id="processingResults">
                    <!-- Los resultados se mostrarán aquí -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Instrucciones</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Pasos para cargar:</strong>
                </div>
                <ol class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-1-circle text-primary me-2"></i>Selecciona el tipo de datos</li>
                    <li class="mb-2"><i class="bi bi-2-circle text-primary me-2"></i>Elige el formato del archivo</li>
                    <li class="mb-2"><i class="bi bi-3-circle text-primary me-2"></i>Sube el archivo</li>
                    <li class="mb-2"><i class="bi bi-4-circle text-primary me-2"></i>Configura las opciones</li>
                    <li class="mb-2"><i class="bi bi-5-circle text-primary me-2"></i>Procesa el archivo</li>
                </ol>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Formatos Soportados</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Excel (.xlsx, .xls)</h6>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-check text-success me-2"></i>Primera fila como encabezados</li>
                        <li><i class="bi bi-check text-success me-2"></i>Datos en columnas ordenadas</li>
                        <li><i class="bi bi-check text-success me-2"></i>Sin filas vacías entre datos</li>
                    </ul>
                </div>
                <div>
                    <h6>CSV (.csv)</h6>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-check text-success me-2"></i>Separado por comas</li>
                        <li><i class="bi bi-check text-success me-2"></i>Codificación UTF-8</li>
                        <li><i class="bi bi-check text-success me-2"></i>Primera fila como encabezados</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('fileInput');
    const dataType = document.getElementById('dataType').value;
    const fileFormat = document.getElementById('fileFormat').value;
    
    if (!fileInput.files[0]) {
        alert('Por favor selecciona un archivo');
        return;
    }
    
    if (!dataType || !fileFormat) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    // Simular procesamiento
    showProcessingResults();
});

function showProcessingResults() {
    const resultsCard = document.getElementById('resultsCard');
    const resultsDiv = document.getElementById('processingResults');
    
    resultsDiv.innerHTML = `
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Archivo procesado exitosamente</strong>
        </div>
        
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="h4 text-success">25</div>
                    <div class="text-muted">Registros Procesados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="h4 text-success">23</div>
                    <div class="text-muted">Registros Importados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="h4 text-warning">2</div>
                    <div class="text-muted">Registros con Errores</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="h4 text-info">0</div>
                    <div class="text-muted">Registros Duplicados</div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6>Errores encontrados:</h6>
            <ul class="list-unstyled">
                <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Fila 5: Email inválido</li>
                <li><i class="bi bi-exclamation-triangle text-warning me-2"></i>Fila 12: Campo requerido vacío</li>
            </ul>
        </div>
        
        <div class="mt-3">
            <button class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i>Confirmar Importación
            </button>
            <button class="btn btn-outline-secondary ms-2">
                <i class="bi bi-download me-1"></i>Descargar Log de Errores
            </button>
        </div>
    `;
    
    resultsCard.style.display = 'block';
    resultsCard.scrollIntoView({ behavior: 'smooth' });
}
</script>
