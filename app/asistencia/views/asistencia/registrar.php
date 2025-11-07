<?php
$esDocente = isset($esDocente) && $esDocente;
?>

<!-- Registrar Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>
            <?php echo $esDocente ? 'Marcar Mi Asistencia' : 'Registrar Asistencia'; ?>
        </h1>
        <p class="text-muted mb-0">
            <?php echo $esDocente ? 'Selecciona una clase para registrar tu asistencia' : 'Registra la asistencia de los docentes'; ?>
        </p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group">
            <a href="/asistencia" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<?php if ($esDocente && isset($horarios) && count($horarios) > 0): ?>
<!-- Mis Horarios Disponibles -->
<div class="row g-3 mb-4">
    <?php foreach ($horarios as $horario): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 <?php echo $horario['estado'] === 'disponible' ? 'border-success shadow-sm' : ($horario['estado'] === 'vencido' ? 'border-secondary opacity-50' : ''); ?>">
            <div class="card-header <?php echo $horario['estado'] === 'disponible' ? 'bg-success text-white' : 'bg-light'; ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-book me-2"></i><?php echo htmlspecialchars($horario['materia_nombre'] ?? 'N/A'); ?>
                    </h6>
                    <?php if ($horario['estado'] === 'disponible'): ?>
                    <span class="badge bg-light text-success">
                        <i class="bi bi-check-circle me-1"></i>Disponible
                    </span>
                    <?php elseif ($horario['estado'] === 'pendiente'): ?>
                    <span class="badge bg-warning">
                        <i class="bi bi-clock me-1 me-1"></i>Pendiente
                    </span>
                    <?php elseif ($horario['estado'] === 'vencido'): ?>
                    <span class="badge bg-secondary">
                        <i class="bi bi-x-circle me-1"></i>Vencido
                    </span>
                    <?php elseif ($horario['estado'] === 'registrada'): ?>
                    <span class="badge bg-info">
                        <i class="bi bi-check-circle-fill me-1"></i>Registrada
                    </span>
                    <?php else: ?>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-calendar me-1"></i><?php echo htmlspecialchars($horario['dia_nombre'] ?? 'N/A'); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-calendar-event"></i> <strong>Día:</strong> <?php echo htmlspecialchars($horario['dia_nombre'] ?? 'N/A'); ?>
                    </small>
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-clock"></i> <strong>Horario:</strong> <?php echo htmlspecialchars($horario['hora_inicio'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($horario['hora_fin'] ?? 'N/A'); ?>
                    </small>
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-people"></i> <strong>Grupo:</strong> <?php echo htmlspecialchars($horario['grupo_numero'] ?? 'N/A'); ?>
                        <?php if (!empty($horario['semestre'])): ?>
                        <span class="text-muted">(<?php echo htmlspecialchars($horario['semestre']); ?>)</span>
                        <?php endif; ?>
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-building"></i> <strong>Aula:</strong> 
                        <?php if (!empty($horario['aula_nombre'])): ?>
                        <?php echo htmlspecialchars($horario['aula_nombre']); ?>
                        <?php if (!empty($horario['aula_codigo'])): ?>
                        (<?php echo htmlspecialchars($horario['aula_codigo']); ?>)
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </small>
                </div>
                
                <?php if ($horario['estado'] === 'registrada'): ?>
                <div class="alert alert-success mb-0 py-2">
                    <small>
                        <i class="bi bi-check-circle me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje'] ?? 'Asistencia ya registrada'); ?>
                    </small>
                </div>
                <button class="btn btn-success w-100 mt-3" disabled>
                    <i class="bi bi-check-circle-fill me-1"></i>Asistencia Registrada
                </button>
                <?php elseif ($horario['estado'] === 'disponible'): ?>
                <div class="alert alert-success mb-0 py-2">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje'] ?? 'Disponible para marcar'); ?>
                    </small>
                </div>
                <button class="btn btn-success w-100 mt-3 marcar-asistencia" 
                        data-horario-id="<?php echo $horario['id']; ?>"
                        data-materia="<?php echo htmlspecialchars($horario['materia_nombre'] ?? 'N/A'); ?>"
                        data-grupo="<?php echo htmlspecialchars($horario['grupo_numero'] ?? 'N/A'); ?>"
                        data-aula="<?php echo htmlspecialchars(($horario['aula_nombre'] ?? 'N/A') . (!empty($horario['aula_codigo']) ? ' (' . $horario['aula_codigo'] . ')' : '')); ?>">
                    <i class="bi bi-check-circle me-1"></i>Marcar Asistencia
                </button>
                <?php elseif ($horario['estado'] === 'pendiente'): ?>
                <div class="alert alert-warning mb-0 py-2">
                    <small>
                        <i class="bi bi-clock me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje']); ?>
                    </small>
                </div>
                <button class="btn btn-outline-secondary w-100 mt-3" disabled>
                    <i class="bi bi-lock me-1"></i>No Disponible
                </button>
                <?php elseif ($horario['estado'] === 'vencido'): ?>
                <div class="alert alert-secondary mb-0 py-2">
                    <small>
                        <i class="bi bi-x-circle me-1"></i>
                        Ventana de marcación cerrada
                    </small>
                </div>
                <button class="btn btn-outline-secondary w-100 mt-3" disabled>
                    <i class="bi bi-lock-fill me-1"></i>Vencido
                </button>
                <?php elseif ($horario['estado'] === 'incumplido'): ?>
                <div class="alert alert-danger mb-0 py-2">
                    <small>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No se registró asistencia dentro del tiempo permitido
                    </small>
                </div>
                <button class="btn btn-outline-danger w-100 mt-3" disabled>
                    <i class="bi bi-x-circle-fill me-1"></i>Incumplido
                </button>
                <?php elseif ($horario['estado'] === 'fuera_ventana'): ?>
                <div class="alert alert-warning mb-0 py-2">
                    <small>
                        <i class="bi bi-clock-history me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje'] ?? 'Fuera de ventana de marcación'); ?>
                    </small>
                </div>
                <button class="btn btn-warning w-100 mt-3 marcar-asistencia" 
                        data-horario-id="<?php echo $horario['id']; ?>"
                        data-materia="<?php echo htmlspecialchars($horario['materia_nombre'] ?? 'N/A'); ?>"
                        data-grupo="<?php echo htmlspecialchars($horario['grupo_numero'] ?? 'N/A'); ?>"
                        data-aula="<?php echo htmlspecialchars(($horario['aula_nombre'] ?? 'N/A') . (!empty($horario['aula_codigo']) ? ' (' . $horario['aula_codigo'] . ')' : '')); ?>">
                    <i class="bi bi-check-circle me-1"></i>Marcar como Asistido Tarde
                </button>
                <?php else: ?>
                <div class="alert alert-info mb-0 py-2">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje'] ?? 'Estado: ' . ($horario['estado'] ?? 'no_disponible')); ?>
                    </small>
                </div>
                <button class="btn btn-outline-secondary w-100 mt-3" disabled>
                    <i class="bi bi-calendar-x me-1"></i>No Disponible
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Alerta de Instrucciones -->
<div class="alert alert-info">
    <h6 class="alert-heading">
        <i class="bi bi-info-circle me-2"></i>Instrucciones
    </h6>
    <ul class="mb-0">
        <li>Ventana de marcación: <strong>20 minutos antes</strong> hasta <strong>10 minutos después</strong> del inicio de la clase</li>
        <li>Si marcas dentro de la ventana (antes del inicio), se registrará como <strong>presente</strong></li>
        <li>Si marcas dentro de la ventana (después del inicio), se registrará como <strong>tardanza</strong></li>
        <li>Si marcas fuera de la ventana pero dentro del tiempo de clase, se registrará como <strong>asistido tarde</strong></li>
        <li>Si no marcas asistencia dentro del tiempo de la clase, se registrará automáticamente como <strong>incumplido</strong></li>
        <li>El sistema registrará automáticamente la hora exacta de marcación</li>
    </ul>
</div>

<?php else: ?>
<!-- Sin horarios disponibles -->
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>No hay horarios disponibles</strong> para marcar asistencia en este momento.
</div>
<?php endif; ?>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmarAsistenciaModal" tabindex="-1" aria-labelledby="confirmarAsistenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="confirmarAsistenciaModalLabel">
                    <i class="bi bi-question-circle me-2"></i>Confirmar Asistencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">¿Confirmas que deseas marcar tu asistencia para:</p>
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="mb-2">
                            <i class="bi bi-book text-primary me-2"></i>
                            <strong>Materia:</strong> <span id="materiaConfirmacion"></span>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-people text-info me-2"></i>
                            <strong>Grupo:</strong> <span id="grupoConfirmacion"></span>
                        </div>
                        <div class="mb-0">
                            <i class="bi bi-building text-success me-2"></i>
                            <strong>Aula:</strong> <span id="aulaConfirmacion"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmarMarcarBtn">
                    <i class="bi bi-check-circle me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Éxito -->
<div class="modal fade" id="exitoAsistenciaModal" tabindex="-1" aria-labelledby="exitoAsistenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="exitoAsistenciaModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Asistencia Registrada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" id="mensajeExito"></p>
                <p class="text-muted small mb-0" id="horaMarcacion"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="window.location.href='/asistencia'">
                    <i class="bi bi-check me-1"></i>Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Error -->
<div class="modal fade" id="errorAsistenciaModal" tabindex="-1" aria-labelledby="errorAsistenciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorAsistenciaModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="mensajeError"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Validación y registro de asistencia
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.marcar-asistencia');
    let currentButton = null;
    let currentHorarioId = null;
    
    // Modales de Bootstrap
    const confirmarModal = new bootstrap.Modal(document.getElementById('confirmarAsistenciaModal'));
    const exitoModal = new bootstrap.Modal(document.getElementById('exitoAsistenciaModal'));
    const errorModal = new bootstrap.Modal(document.getElementById('errorAsistenciaModal'));
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const horarioId = this.dataset.horarioId;
            const materia = this.dataset.materia;
            const grupo = this.dataset.grupo;
            const aula = this.dataset.aula;
            
            currentButton = this;
            currentHorarioId = horarioId;
            
            // Mostrar modal de confirmación con toda la información
            document.getElementById('materiaConfirmacion').textContent = materia;
            document.getElementById('grupoConfirmacion').textContent = grupo;
            document.getElementById('aulaConfirmacion').textContent = aula;
            confirmarModal.show();
        });
    });
    
    // Botón de confirmar en el modal
    document.getElementById('confirmarMarcarBtn').addEventListener('click', function() {
        if (!currentButton || !currentHorarioId) return;
        
        // Cerrar modal de confirmación
        confirmarModal.hide();
        
        // Deshabilitar botón mientras procesa
        currentButton.disabled = true;
        const originalHtml = currentButton.innerHTML;
        currentButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Procesando...';
        
        // Crear FormData
        const formData = new FormData();
        formData.append('horario_id', currentHorarioId);
        
        // Llamada AJAX
        fetch('/asistencia/registrar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar modal de éxito
                document.getElementById('mensajeExito').textContent = data.message;
                if (data.data && data.data.hora_marcacion) {
                    document.getElementById('horaMarcacion').textContent = 'Registrado a las: ' + data.data.hora_marcacion;
                } else {
                    document.getElementById('horaMarcacion').textContent = 'Registrado exitosamente';
                }
                exitoModal.show();
            } else {
                // Mostrar modal de error
                document.getElementById('mensajeError').textContent = 'Error: ' + data.message;
                errorModal.show();
                
                // Restaurar botón
                currentButton.disabled = false;
                currentButton.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Mostrar modal de error
            document.getElementById('mensajeError').textContent = 'Ocurrió un error al registrar la asistencia. Por favor, intenta nuevamente.';
            errorModal.show();
            
            // Restaurar botón
            currentButton.disabled = false;
            currentButton.innerHTML = originalHtml;
        });
    });
});
</script>
