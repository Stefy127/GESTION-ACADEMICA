<!-- Editar Aula -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-pencil me-3 text-primary"></i>Editar Aula
        </h1>
        <p class="text-muted mb-0">Modifica la información de la aula</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/aulas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Edición -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información de la Aula</h5>
            </div>
            <div class="card-body">
                <form id="editAulaForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($aula['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($aula['codigo'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                            <input type="number" name="capacidad" class="form-control" value="<?php echo htmlspecialchars($aula['capacidad'] ?? ''); ?>" min="1" max="200" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Teórica" <?php echo ($aula['tipo'] ?? '') === 'Teórica' ? 'selected' : ''; ?>>Teórica</option>
                                <option value="Laboratorio" <?php echo ($aula['tipo'] ?? '') === 'Laboratorio' ? 'selected' : ''; ?>>Laboratorio</option>
                                <option value="Computación" <?php echo ($aula['tipo'] ?? '') === 'Computación' ? 'selected' : ''; ?>>Computación</option>
                                <option value="Auditorio" <?php echo ($aula['tipo'] ?? '') === 'Auditorio' ? 'selected' : ''; ?>>Auditorio</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" name="ubicacion" class="form-control" value="<?php echo htmlspecialchars($aula['ubicacion'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="activa" id="activa" <?php echo (isset($aula['activa']) && $aula['activa']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activa">
                                    Activa
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Equipamiento</label>
                            <textarea name="equipamiento" class="form-control" rows="3"><?php echo htmlspecialchars($aula['equipamiento'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
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
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Nota:</strong> Los cambios afectarán los horarios asignados.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editAulaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';
    submitBtn.disabled = true;
    
    fetch('/aulas/update/<?php echo htmlspecialchars($aula['id'] ?? ''); ?>', {
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
        alert('Ocurrió un error al actualizar el aula');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
