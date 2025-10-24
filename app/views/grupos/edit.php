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
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Número del Grupo</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($grupo['numero'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semestre</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar semestre</option>
                                <option value="primero" <?php echo ($grupo['semestre'] ?? '') === 'Primero' ? 'selected' : ''; ?>>Primero</option>
                                <option value="segundo" <?php echo ($grupo['semestre'] ?? '') === 'Segundo' ? 'selected' : ''; ?>>Segundo</option>
                                <option value="tercero" <?php echo ($grupo['semestre'] ?? '') === 'Tercero' ? 'selected' : ''; ?>>Tercero</option>
                                <option value="cuarto">Cuarto</option>
                                <option value="quinto">Quinto</option>
                                <option value="sexto">Sexto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Turno</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar turno</option>
                                <option value="mañana" <?php echo ($grupo['turno'] ?? '') === 'Mañana' ? 'selected' : ''; ?>>Mañana</option>
                                <option value="tarde" <?php echo ($grupo['turno'] ?? '') === 'Tarde' ? 'selected' : ''; ?>>Tarde</option>
                                <option value="noche">Noche</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Materia</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar materia</option>
                                <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id']; ?>" <?php echo ($grupo['materia'] ?? '') === $materia['nombre'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad Máxima</label>
                            <input type="number" class="form-control" placeholder="30" min="1" max="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Descripción adicional del grupo..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/grupos" class="btn btn-outline-secondary ms-2">
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
