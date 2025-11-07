<!-- Reporte de Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-check-circle me-3 text-primary"></i>Reporte de Asistencia
        </h1>
        <p class="text-muted mb-0">Análisis detallado de la asistencia docente</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/reportes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Exportar
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/reportes/exportar/asistencia?formato=pdf&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?><?php echo $docente_id ? '&docente_id=' . $docente_id : ''; ?>"><i class="bi bi-file-pdf me-2"></i>PDF</a></li>
                <li><a class="dropdown-item" href="/reportes/exportar/asistencia?formato=xlsx&fecha_inicio=<?php echo $fecha_inicio; ?>&fecha_fin=<?php echo $fecha_fin; ?><?php echo $docente_id ? '&docente_id=' . $docente_id : ''; ?>"><i class="bi bi-file-excel me-2"></i>Excel</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/reportes/asistencia" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $fecha_fin; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Docente</label>
                <select name="docente_id" class="form-select">
                    <option value="">Todos los docentes</option>
                    <?php foreach ($docentes as $docente): ?>
                    <option value="<?php echo $docente['id']; ?>" <?php echo $docente_id == $docente['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($docente['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $datos['total_clases']; ?></div>
            <div class="stat-label">Total Clases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $datos['asistencias']; ?></div>
            <div class="stat-label">Asistencias</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value"><?php echo $datos['ausencias']; ?></div>
            <div class="stat-label">Ausencias</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value"><?php echo $datos['porcentaje_asistencia']; ?>%</div>
            <div class="stat-label">Promedio</div>
        </div>
    </div>
</div>

<!-- Gráfico de Asistencia por Docente -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Asistencia por Docente</h5>
            </div>
            <div class="card-body">
                <canvas id="asistenciaDocenteChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución</h5>
            </div>
            <div class="card-body">
                <canvas id="distribucionChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Detalle por Docente</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Docente</th>
                        <th class="border-0">Total Clases</th>
                        <th class="border-0">Asistencias</th>
                        <th class="border-0">Ausencias</th>
                        <th class="border-0">Tardanzas</th>
                        <th class="border-0">Justificados</th>
                        <th class="border-0">Porcentaje</th>
                        <th class="border-0">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos['por_docente'])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox me-2"></i>No hay datos de asistencia para el período seleccionado
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($datos['por_docente'] as $docente): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($docente['docente']); ?></div>
                        </td>
                        <td>
                            <span class="fw-semibold"><?php echo $docente['total_clases']; ?></span>
                        </td>
                        <td>
                            <span class="text-success fw-semibold"><?php echo $docente['asistencias']; ?></span>
                        </td>
                        <td>
                            <span class="text-danger fw-semibold"><?php echo $docente['ausencias']; ?></span>
                        </td>
                        <td>
                            <span class="text-warning fw-semibold"><?php echo $docente['tardanzas'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="text-info fw-semibold"><?php echo $docente['justificados'] ?? 0; ?></span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                    <div class="progress-bar bg-<?php echo $docente['porcentaje'] >= 95 ? 'success' : ($docente['porcentaje'] >= 85 ? 'warning' : 'danger'); ?>" 
                                         style="width: <?php echo $docente['porcentaje']; ?>%"></div>
                                </div>
                                <span class="fw-semibold"><?php echo $docente['porcentaje']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <?php if ($docente['porcentaje'] >= 95): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Excelente
                                </span>
                            <?php elseif ($docente['porcentaje'] >= 85): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Regular
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>Deficiente
                                </span>
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

<script>
// Gráfico de asistencia por docente
const ctx1 = document.getElementById('asistenciaDocenteChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($datos['por_docente'], 'docente')); ?>,
        datasets: [{
            label: 'Porcentaje de Asistencia',
            data: <?php echo json_encode(array_column($datos['por_docente'], 'porcentaje')); ?>,
            backgroundColor: [
                '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Gráfico de distribución
const ctx2 = document.getElementById('distribucionChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Asistencias', 'Ausencias'],
        datasets: [{
            data: [<?php echo $datos['asistencias']; ?>, <?php echo $datos['ausencias']; ?>],
            backgroundColor: ['#10b981', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
