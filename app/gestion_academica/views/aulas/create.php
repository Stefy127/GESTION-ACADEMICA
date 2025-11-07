<!-- Crear Aula -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Crear Nueva Aula
        </h1>
        <p class="text-muted mb-0">Registra una nueva aula en el sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/aulas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Creación -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información de la Aula</h5>
            </div>
            <div class="card-body">
                <form id="createAulaForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Aula 101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" class="form-control" placeholder="A101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                            <input type="number" name="capacidad" class="form-control" placeholder="30" min="1" max="200" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Teórica">Teórica</option>
                                <option value="Laboratorio">Laboratorio</option>
                                <option value="Computación">Computación</option>
                                <option value="Auditorio">Auditorio</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" name="ubicacion" class="form-control" placeholder="Edificio A - Primer Piso">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Equipamiento</label>
                            <textarea name="equipamiento" class="form-control" rows="3" placeholder="Ej: Proyector, pizarrón, aire acondicionado..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Crear Aula
                        </button>
                        <a href="/aulas" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Tipos de Aula:</strong>
                </div>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-check text-success me-2"></i><strong>Teórica:</strong> Para clases magistrales</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Laboratorio:</strong> Para prácticas</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Computación:</strong> Con equipos de cómputo</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Auditorio:</strong> Para conferencias</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createAulaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creando...';
    submitBtn.disabled = true;
    
    fetch('/aulas/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/aulas';
        } else {
            alert('Error: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear el aula');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
