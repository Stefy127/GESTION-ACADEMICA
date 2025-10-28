<!-- Editar Materia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-pencil me-3 text-primary"></i>Editar Materia
        </h1>
        <p class="text-muted mb-0">Modifica la información de la materia</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/materias" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información de la Materia</h5>
            </div>
            <div class="card-body">
                <form id="formEditarMateria" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Materia</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($materia['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($materia['codigo'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel</label>
                            <select name="nivel" class="form-select" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="basico" <?php echo ($materia['nivel'] ?? '') === 'Básico' ? 'selected' : ''; ?>>Básico</option>
                                <option value="intermedio" <?php echo ($materia['nivel'] ?? '') === 'Intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                                <option value="avanzado">Avanzado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carga Horaria</label>
                            <input type="number" name="carga_horaria" class="form-control" value="<?php echo $materia['carga_horaria'] ?? ''; ?>" min="1" max="10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción de la materia..."><?php echo htmlspecialchars($materia['descripcion'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/materias" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
                
<script>
document.getElementById('formEditarMateria').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';
    
    const formData = new FormData(this);
    
    fetch('/materias/update/<?php echo $materia['id']; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/materias';
        } else {
            alert('Error: ' + data.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al actualizar la materia');
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
                    <strong>Nota:</strong> Los cambios afectarán los grupos asignados.
                </div>
            </div>
        </div>
    </div>
</div>
