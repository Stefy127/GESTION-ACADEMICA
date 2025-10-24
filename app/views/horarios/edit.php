<!-- Editar Horario -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-pencil me-3 text-primary"></i>Editar Horario
        </h1>
        <p class="text-muted mb-0">Modifica la información del horario</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/horarios" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información del Horario</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Día de la Semana</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar día</option>
                                <option value="lunes" <?php echo ($horario['dia'] ?? '') === 'Lunes' ? 'selected' : ''; ?>>Lunes</option>
                                <option value="martes" <?php echo ($horario['dia'] ?? '') === 'Martes' ? 'selected' : ''; ?>>Martes</option>
                                <option value="miercoles" <?php echo ($horario['dia'] ?? '') === 'Miércoles' ? 'selected' : ''; ?>>Miércoles</option>
                                <option value="jueves" <?php echo ($horario['dia'] ?? '') === 'Jueves' ? 'selected' : ''; ?>>Jueves</option>
                                <option value="viernes" <?php echo ($horario['dia'] ?? '') === 'Viernes' ? 'selected' : ''; ?>>Viernes</option>
                                <option value="sabado">Sábado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grupo</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar grupo</option>
                                <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id']; ?>" <?php echo ($horario['grupo'] ?? '') === $grupo['numero'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($grupo['numero']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Inicio</label>
                            <input type="time" class="form-control" value="<?php echo $horario['hora_inicio'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Fin</label>
                            <input type="time" class="form-control" value="<?php echo $horario['hora_fin'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aula</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar aula</option>
                                <?php foreach ($aulas as $aula): ?>
                                <option value="<?php echo $aula['id']; ?>" <?php echo ($horario['aula'] ?? '') === $aula['nombre'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($aula['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Docente</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar docente</option>
                                <?php foreach ($docentes as $docente): ?>
                                <option value="<?php echo $docente['id']; ?>" <?php echo ($horario['docente'] ?? '') === $docente['nombre'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($docente['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/horarios" class="btn btn-outline-secondary ms-2">
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
                    <strong>Nota:</strong> Los cambios pueden afectar otros horarios.
                </div>
            </div>
        </div>
    </div>
</div>
