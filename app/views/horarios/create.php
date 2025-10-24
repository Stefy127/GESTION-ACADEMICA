<!-- Crear Horario -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Crear Nuevo Horario
        </h1>
        <p class="text-muted mb-0">Asigna un nuevo horario académico</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/horarios" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información del Horario</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Día de la Semana</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar día</option>
                                <option value="lunes">Lunes</option>
                                <option value="martes">Martes</option>
                                <option value="miercoles">Miércoles</option>
                                <option value="jueves">Jueves</option>
                                <option value="viernes">Viernes</option>
                                <option value="sabado">Sábado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grupo</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar grupo</option>
                                <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id']; ?>"><?php echo htmlspecialchars($grupo['numero']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Inicio</label>
                            <input type="time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Fin</label>
                            <input type="time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aula</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar aula</option>
                                <?php foreach ($aulas as $aula): ?>
                                <option value="<?php echo $aula['id']; ?>"><?php echo htmlspecialchars($aula['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Docente</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar docente</option>
                                <?php foreach ($docentes as $docente): ?>
                                <option value="<?php echo $docente['id']; ?>"><?php echo htmlspecialchars($docente['nombre']); ?></option>
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
                            <i class="bi bi-check-circle me-1"></i>Crear Horario
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
                <h5 class="card-title mb-0">Validaciones</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>El sistema verificará:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check text-success me-2"></i>Disponibilidad del aula</li>
                    <li><i class="bi bi-check text-success me-2"></i>Disponibilidad del docente</li>
                    <li><i class="bi bi-check text-success me-2"></i>No cruce de horarios</li>
                    <li><i class="bi bi-check text-success me-2"></i>Capacidad del aula</li>
                </ul>
            </div>
        </div>
    </div>
</div>
