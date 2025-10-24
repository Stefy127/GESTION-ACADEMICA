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
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Materia</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($materia['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($materia['codigo'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="basico" <?php echo ($materia['nivel'] ?? '') === 'Básico' ? 'selected' : ''; ?>>Básico</option>
                                <option value="intermedio" <?php echo ($materia['nivel'] ?? '') === 'Intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                                <option value="avanzado">Avanzado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carga Horaria</label>
                            <input type="number" class="form-control" value="<?php echo $materia['carga_horaria'] ?? ''; ?>" min="1" max="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semestre</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar semestre</option>
                                <option value="1">Primer Semestre</option>
                                <option value="2">Segundo Semestre</option>
                                <option value="3">Tercer Semestre</option>
                                <option value="4">Cuarto Semestre</option>
                                <option value="5">Quinto Semestre</option>
                                <option value="6">Sexto Semestre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="teorica">Teórica</option>
                                <option value="practica">Práctica</option>
                                <option value="mixta">Mixta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Descripción de la materia..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/materias" class="btn btn-outline-secondary ms-2">
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
                    <strong>Nota:</strong> Los cambios afectarán los grupos asignados.
                </div>
            </div>
        </div>
    </div>
</div>
