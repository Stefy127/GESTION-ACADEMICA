<!-- Registrar Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Registrar Asistencia
        </h1>
        <p class="text-muted mb-0">Registra la asistencia de los docentes</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/asistencia" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Registro -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información de la Asistencia</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora</label>
                            <input type="time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horario</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar horario</option>
                                <?php foreach ($horarios as $horario): ?>
                                <option value="<?php echo $horario['id']; ?>">
                                    <?php echo htmlspecialchars($horario['dia'] . ' ' . $horario['hora'] . ' - ' . $horario['materia'] . ' (' . $horario['grupo'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado de Asistencia</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar estado</option>
                                <option value="presente">Presente</option>
                                <option value="ausente">Ausente</option>
                                <option value="tardanza">Tardanza</option>
                                <option value="justificado">Justificado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" rows="3" placeholder="Observaciones sobre la asistencia..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Registrar Asistencia
                        </button>
                        <a href="/asistencia" class="btn btn-outline-secondary ms-2">
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
                <h5 class="card-title mb-0">Estados de Asistencia</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Tipos de registro:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check-circle text-success me-2"></i><strong>Presente:</strong> Docente asistió puntualmente</li>
                    <li><i class="bi bi-x-circle text-danger me-2"></i><strong>Ausente:</strong> Docente no asistió</li>
                    <li><i class="bi bi-clock text-warning me-2"></i><strong>Tardanza:</strong> Docente llegó tarde</li>
                    <li><i class="bi bi-shield-check text-info me-2"></i><strong>Justificado:</strong> Ausencia con justificación</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Resumen del Día</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-value text-success">5</div>
                        <div class="stat-label">Presentes</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-value text-danger">1</div>
                        <div class="stat-label">Ausentes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
