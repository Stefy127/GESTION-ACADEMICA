<!-- Editar Grupo -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-pencil me-3 text-primary"></i>Editar Grupo
        </h1>
        <p class="text-muted mb-0">Modifica la información del grupo</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/grupos" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información del Grupo</h5>
            </div>
            <div class="card-body">
                <form id="formEditarGrupo" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Número del Grupo</label>
                            <input type="text" name="numero" class="form-control" value="<?php echo htmlspecialchars($grupo['numero'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semestre</label>
                            <select name="semestre" class="form-select" required>
                                <option value="">Seleccionar semestre</option>
                                <option value="2024-1" <?php echo ($grupo['semestre'] ?? '') === '2024-1' ? 'selected' : ''; ?>>2024-1</option>
                                <option value="2024-2" <?php echo ($grupo['semestre'] ?? '') === '2024-2' ? 'selected' : ''; ?>>2024-2</option>
                                <option value="2025-1" <?php echo ($grupo['semestre'] ?? '') === '2025-1' ? 'selected' : ''; ?>>2025-1</option>
                                <option value="2025-2" <?php echo ($grupo['semestre'] ?? '') === '2025-2' ? 'selected' : ''; ?>>2025-2</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Turno</label>
                            <select name="turno" class="form-select" required>
                                <option value="">Seleccionar turno</option>
                                <option value="Mañana" <?php echo ($grupo['turno'] ?? '') === 'Mañana' ? 'selected' : ''; ?>>Mañana</option>
                                <option value="Tarde" <?php echo ($grupo['turno'] ?? '') === 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                                <option value="Noche" <?php echo ($grupo['turno'] ?? '') === 'Noche' ? 'selected' : ''; ?>>Noche</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Materia</label>
                            <select name="materia_id" class="form-select" required>
                                <option value="">Seleccionar materia</option>
                                <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id']; ?>" <?php echo isset($grupo['materia_id']) && $grupo['materia_id'] == $materia['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($materia['nombre'] . ' (' . $materia['codigo'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad Máxima</label>
                            <input type="number" name="capacidad_maxima" class="form-control" value="<?php echo htmlspecialchars($grupo['capacidad_maxima'] ?? 30); ?>" min="1" max="50" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción adicional del grupo..."><?php echo htmlspecialchars($grupo['descripcion'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/grupos" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
                
<script>
document.getElementById('formEditarGrupo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';
    
    const formData = new FormData(this);
    
    fetch('/grupos/update/<?php echo $grupo['id']; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/grupos';
        } else {
            alert('Error: ' + data.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al actualizar el grupo');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
    });
});
</script>
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
