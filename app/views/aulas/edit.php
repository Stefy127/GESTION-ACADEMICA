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
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Aula</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($aula['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" value="<?php echo $aula['capacidad'] ?? ''; ?>" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Aula</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="teoria" <?php echo ($aula['tipo'] ?? '') === 'Teoría' ? 'selected' : ''; ?>>Teoría</option>
                                <option value="laboratorio" <?php echo ($aula['tipo'] ?? '') === 'Laboratorio' ? 'selected' : ''; ?>>Laboratorio</option>
                                <option value="computacion">Computación</option>
                                <option value="audiovisual">Audiovisual</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" required>
                                <option value="disponible" <?php echo ($aula['disponible'] ?? false) ? 'selected' : ''; ?>>Disponible</option>
                                <option value="mantenimiento">En Mantenimiento</option>
                                <option value="ocupada" <?php echo !($aula['disponible'] ?? true) ? 'selected' : ''; ?>>Ocupada</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Descripción adicional de la aula..."></textarea>
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
