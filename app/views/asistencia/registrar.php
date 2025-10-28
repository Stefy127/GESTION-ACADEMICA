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
                        <i class="bi bi-book me-2"></i><?php echo htmlspecialchars($horario['materia']); ?>
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
                    <?php else: ?>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-calendar me-1"></i><?php echo htmlspecialchars($horario['dia']); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-calendar-event"></i> <strong>Día:</strong> <?php echo htmlspecialchars($horario['dia']); ?>
                    </small>
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-clock"></i> <strong>Horario:</strong> <?php echo htmlspecialchars($horario['hora_inicio']); ?> - <?php echo htmlspecialchars($horario['hora_fin']); ?>
                    </small>
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-people"></i> <strong>Grupo:</strong> <?php echo htmlspecialchars($horario['grupo']); ?>
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-building"></i> <strong>Aula:</strong> <?php echo htmlspecialchars($horario['aula']); ?>
                    </small>
                </div>
                
                <?php if ($horario['estado'] === 'disponible'): ?>
                <div class="alert alert-success mb-0 py-2">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        <?php echo htmlspecialchars($horario['mensaje']); ?>
                    </small>
                </div>
                <button class="btn btn-success w-100 mt-3 marcar-asistencia" 
                        data-horario-id="<?php echo $horario['id']; ?>"
                        data-materia="<?php echo htmlspecialchars($horario['materia']); ?>">
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
                <?php else: ?>
                <div class="alert alert-info mb-0 py-2">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        No es el día programado
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
        <li>Solo podrás marcar asistencia <strong>15 minutos antes</strong> del inicio de la clase</li>
        <li>Si marcas asistencia después del horario programado, se registrará como <strong>llegada tardía</strong></li>
        <li>El sistema registrará automáticamente la hora exacta de marcación</li>
        <li>Si no puedes marcar asistencia, contacta con administración</li>
    </ul>
</div>

<?php else: ?>
<!-- Sin horarios disponibles -->
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>No hay horarios disponibles</strong> para marcar asistencia en este momento.
</div>
<?php endif; ?>

<script>
// Validación y registro de asistencia
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.marcar-asistencia');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const horarioId = this.dataset.horarioId;
            const materia = this.dataset.materia;
            
            if (confirm(`¿Confirmas que deseas marcar tu asistencia para:\n\n${materia}?`)) {
                // Deshabilitar botón mientras procesa
                this.disabled = true;
                this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Procesando...';
                
                // Crear FormData
                const formData = new FormData();
                formData.append('horario_id', horarioId);
                
                // Llamada AJAX
                fetch('/asistencia/registrar', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message + '\n\nRegistrado a las: ' + data.data.hora_marcacion);
                        setTimeout(() => {
                            window.location.href = '/asistencia';
                        }, 1500);
                    } else {
                        alert('Error: ' + data.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Marcar Asistencia';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al registrar la asistencia');
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Marcar Asistencia';
                });
            }
        });
    });
});
</script>
