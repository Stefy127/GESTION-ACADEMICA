<!-- Horarios del Grupo -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-calendar-week me-3 text-primary"></i>Horarios del Grupo: <?php echo htmlspecialchars($grupo['numero'] ?? 'N/A'); ?>
        </h1>
        <p class="text-muted mb-0">Horarios asignados a este grupo</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/grupos" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Grupos
            </a>
        </div>
    </div>
</div>

<!-- Detalles del Grupo -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Detalles del Grupo</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Número:</strong> <?php echo htmlspecialchars($grupo['numero'] ?? 'N/A'); ?></p>
                <p><strong>Semestre:</strong> <?php echo htmlspecialchars($grupo['semestre'] ?? 'N/A'); ?></p>
                <p><strong>Turno:</strong> <?php echo htmlspecialchars($grupo['turno'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Capacidad Máxima:</strong> <?php echo htmlspecialchars($grupo['capacidad_maxima'] ?? 'N/A'); ?> estudiantes</p>
                <p><strong>Docente:</strong> <?php echo htmlspecialchars($grupo['docente_nombre'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Horarios -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Horarios Asignados</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Día</th>
                        <th class="border-0">Hora Inicio</th>
                        <th class="border-0">Hora Fin</th>
                        <th class="border-0">Aula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($horarios)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No hay horarios asignados a este grupo.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $diasSemana = [
                        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 
                        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
                    ];
                    foreach ($horarios as $horario): 
                    ?>
                    <tr>
                        <td class="ps-4">
                            <span class="fw-semibold"><?php echo htmlspecialchars($diasSemana[$horario['dia_semana']] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars(date('H:i', strtotime($horario['hora_inicio'] ?? ''))); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars(date('H:i', strtotime($horario['hora_fin'] ?? ''))); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($horario['aula_nombre'])): ?>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($horario['aula_nombre'] ?? 'N/A'); ?>
                                    (<?php echo htmlspecialchars($horario['aula_codigo'] ?? ''); ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sin aula asignada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

