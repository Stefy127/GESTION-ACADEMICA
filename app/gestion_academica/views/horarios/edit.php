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
                <form id="horarioForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Día de la Semana <span class="text-danger">*</span></label>
                            <select name="dia_semana" id="dia_semana" class="form-select" required>
                                <option value="">Seleccionar día</option>
                                <option value="1" <?php echo ($horario['dia_semana'] ?? 0) == 1 ? 'selected' : ''; ?>>Lunes</option>
                                <option value="2" <?php echo ($horario['dia_semana'] ?? 0) == 2 ? 'selected' : ''; ?>>Martes</option>
                                <option value="3" <?php echo ($horario['dia_semana'] ?? 0) == 3 ? 'selected' : ''; ?>>Miércoles</option>
                                <option value="4" <?php echo ($horario['dia_semana'] ?? 0) == 4 ? 'selected' : ''; ?>>Jueves</option>
                                <option value="5" <?php echo ($horario['dia_semana'] ?? 0) == 5 ? 'selected' : ''; ?>>Viernes</option>
                                <option value="6" <?php echo ($horario['dia_semana'] ?? 0) == 6 ? 'selected' : ''; ?>>Sábado</option>
                                <option value="7" <?php echo ($horario['dia_semana'] ?? 0) == 7 ? 'selected' : ''; ?>>Domingo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grupo <span class="text-danger">*</span></label>
                            <select name="grupo_id" id="grupo_id" class="form-select" required>
                                <option value="">Seleccionar grupo</option>
                                <?php foreach ($grupos as $grupo): ?>
                                <option value="<?php echo $grupo['id']; ?>" <?php echo ($horario['grupo_id'] ?? 0) == $grupo['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($grupo['numero']); ?>
                                    <?php if (!empty($grupo['semestre'])): ?>
                                        - <?php echo htmlspecialchars($grupo['semestre']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($grupo['turno'])): ?>
                                        (<?php echo htmlspecialchars($grupo['turno']); ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Inicio <span class="text-danger">*</span></label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($horario['hora_inicio'] ?? '08:00:00'))); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hora de Fin <span class="text-danger">*</span></label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" value="<?php echo htmlspecialchars(date('H:i', strtotime($horario['hora_fin'] ?? '10:00:00'))); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aula <span class="text-danger">*</span></label>
                            <select name="aula_id" id="aula_id" class="form-select" required>
                                <option value="">Seleccionar aula</option>
                                <?php foreach ($aulas as $aula): ?>
                                <option value="<?php echo $aula['id']; ?>" <?php echo ($horario['aula_id'] ?? 0) == $aula['id'] ? 'selected' : ''; ?> data-capacidad="<?php echo $aula['capacidad'] ?? 0; ?>">
                                    <?php echo htmlspecialchars($aula['nombre']); ?>
                                    <?php if (!empty($aula['codigo'])): ?>
                                        (<?php echo htmlspecialchars($aula['codigo']); ?>)
                                    <?php endif; ?>
                                    <?php if (!empty($aula['capacidad'])): ?>
                                        - Capacidad: <?php echo $aula['capacidad']; ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Docente <span class="text-danger">*</span></label>
                            <select name="docente_id" id="docente_id" class="form-select" required>
                                <option value="">Seleccionar docente</option>
                                <?php foreach ($docentes as $docente): ?>
                                <option value="<?php echo $docente['id']; ?>" <?php echo ($horario['docente_id'] ?? 0) == $docente['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($docente['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Alertas de conflictos -->
                    <div id="conflictAlerts" class="mt-3"></div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
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
                    <strong>Nota:</strong> Los cambios pueden afectar otros horarios. El sistema verificará automáticamente si existen conflictos.
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check text-success me-2"></i>Disponibilidad del aula</li>
                    <li><i class="bi bi-check text-success me-2"></i>Disponibilidad del docente</li>
                    <li><i class="bi bi-check text-success me-2"></i>No cruce de horarios del grupo</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('horarioForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    const conflictAlerts = document.getElementById('conflictAlerts');
    const horarioId = <?php echo $horario['id'] ?? 0; ?>;
    
    // Limpiar alertas previas
    conflictAlerts.innerHTML = '';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
    
    fetch(`/horarios/update/${horarioId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/horarios';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
            
            // Mostrar conflictos si existen
            if (data.conflicts && data.conflicts.length > 0) {
                let alertHtml = '<div class="alert alert-danger"><h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Conflictos detectados:</h6><ul class="mb-0">';
                data.conflicts.forEach(conflict => {
                    alertHtml += `<li>${conflict}</li>`;
                });
                alertHtml += '</ul></div>';
                conflictAlerts.innerHTML = alertHtml;
            } else {
                conflictAlerts.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
        conflictAlerts.innerHTML = '<div class="alert alert-danger">Ocurrió un error al actualizar el horario</div>';
    });
});

// Validación en tiempo real de horas
document.getElementById('hora_fin').addEventListener('change', function() {
    const horaInicio = document.getElementById('hora_inicio').value;
    const horaFin = this.value;
    
    if (horaInicio && horaFin && horaFin <= horaInicio) {
        alert('La hora de fin debe ser posterior a la hora de inicio');
        this.value = '';
    }
});
</script>
