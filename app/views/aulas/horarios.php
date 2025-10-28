<!-- Horarios del Aula -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-calendar-week me-3 text-primary"></i>Horarios del Aula: <?php echo htmlspecialchars($aula['nombre'] ?? ''); ?>
        </h1>
        <p class="text-muted mb-0">Ver horarios asignados a esta aula</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/aulas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Aulas
            </a>
        </div>
    </div>
</div>

<!-- Información del Aula -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Código:</strong>
                        <p class="mb-0"><?php echo htmlspecialchars($aula['codigo'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-3">
                        <strong>Capacidad:</strong>
                        <p class="mb-0"><?php echo htmlspecialchars($aula['capacidad'] ?? 'N/A'); ?> estudiantes</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Tipo:</strong>
                        <p class="mb-0">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($aula['tipo'] ?? 'N/A'); ?></span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <strong>Ubicación:</strong>
                        <p class="mb-0"><?php echo htmlspecialchars($aula['ubicacion'] ?? 'N/A'); ?></p>
                    </div>
                </div>
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
                        <th class="border-0">Hora</th>
                        <th class="border-0">Grupo</th>
                        <th class="border-0">Materia</th>
                        <th class="border-0">Docente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($horarios)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No hay horarios asignados a esta aula</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                    foreach ($horarios as $horario): 
                    ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-primary">
                                <?php echo $dias[$horario['dia_semana']] ?? 'N/A'; ?>
                            </span>
                        </td>
                        <td>
                            <i class="bi bi-clock"></i> 
                            <?php echo htmlspecialchars($horario['hora_inicio'] ?? ''); ?> - 
                            <?php echo htmlspecialchars($horario['hora_fin'] ?? ''); ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($horario['grupo'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($horario['materia'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($horario['docente'] ?? 'N/A'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

