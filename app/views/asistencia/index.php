<!-- Control de Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-check-circle me-3 text-primary"></i>Control de Asistencia
        </h1>
        <p class="text-muted mb-0">Registra y consulta la asistencia docente</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/asistencia/registrar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Registrar Asistencia
            </a>
        </div>
        <?php if (in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
        <div class="btn-group">
            <a href="/asistencia/reportes" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>Ver Reportes
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo count($asistencias); ?></div>
            <div class="stat-label">Registros Hoy</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value">
                <?php 
                $presentes = array_filter($asistencias, fn($a) => isset($a['estado']) && $a['estado'] === 'presente');
                echo count($presentes); 
                ?>
            </div>
            <div class="stat-label">Presentes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">
                <?php 
                $tardanzas = array_filter($asistencias, fn($a) => isset($a['estado']) && $a['estado'] === 'tardanza');
                echo count($tardanzas); 
                ?>
            </div>
            <div class="stat-label">Tardanzas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value">
                <?php 
                $total = count($asistencias);
                $presentesYTardanzas = count(array_filter($asistencias, fn($a) => isset($a['estado']) && in_array($a['estado'], ['presente', 'tardanza'])));
                echo $total > 0 ? round(($presentesYTardanzas / $total) * 100, 1) : 0; 
                ?>%
            </div>
            <div class="stat-label">Porcentaje</div>
        </div>
    </div>
</div>

<!-- Tabla de Asistencias -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Registros de Asistencia</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Docente</th>
                        <th class="border-0">Materia</th>
                        <th class="border-0">Grupo</th>
                        <th class="border-0">Fecha</th>
                        <th class="border-0">Hora</th>
                        <th class="border-0">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($asistencias as $asistencia): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($asistencia['docente'] ?? 'N/A'); ?></div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($asistencia['materia'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($asistencia['grupo'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo isset($asistencia['fecha']) ? date('d/m/Y', strtotime($asistencia['fecha'])) : 'N/A'; ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($asistencia['hora'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <?php 
                            $estado = $asistencia['estado'] ?? 'ausente';
                            if ($estado === 'presente'): 
                            ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Puntual
                                </span>
                            <?php elseif ($estado === 'tardanza'): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock-history me-1"></i>Tarde
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>No asistió
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
