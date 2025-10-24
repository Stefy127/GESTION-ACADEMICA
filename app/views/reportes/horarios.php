<!-- Reporte de Horarios -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-calendar-week me-3 text-primary"></i>Reporte de Horarios
        </h1>
        <p class="text-muted mb-0">Análisis de la distribución de horarios y ocupación de aulas</p>
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
                <li><a class="dropdown-item" href="#">PDF</a></li>
                <li><a class="dropdown-item" href="#">Excel</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="stat-value"><?php echo $datos['total_horarios']; ?></div>
            <div class="stat-label">Total Horarios</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $datos['horarios_activos']; ?></div>
            <div class="stat-label">Activos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-value"><?php echo $datos['aulas_ocupadas']; ?></div>
            <div class="stat-label">Aulas Ocupadas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="stat-value"><?php echo $datos['aulas_disponibles']; ?></div>
            <div class="stat-label">Aulas Disponibles</div>
        </div>
    </div>
</div>

<!-- Gráficos de Análisis -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ocupación por Día</h5>
            </div>
            <div class="card-body">
                <canvas id="ocupacionDiaChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución de Aulas</h5>
            </div>
            <div class="card-body">
                <canvas id="distribucionAulasChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ocupación por Día -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Detalle por Día de la Semana</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Día</th>
                        <th class="border-0">Horarios</th>
                        <th class="border-0">Ocupación</th>
                        <th class="border-0">Estado</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos['por_dia'] as $dia): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($dia['dia']); ?></span>
                        </td>
                        <td>
                            <span class="fw-semibold"><?php echo $dia['horarios']; ?></span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress me-2" style="width: 100px; height: 8px;">
                                    <div class="progress-bar bg-<?php echo $dia['ocupacion'] >= 90 ? 'success' : ($dia['ocupacion'] >= 70 ? 'warning' : 'danger'); ?>" 
                                         style="width: <?php echo $dia['ocupacion']; ?>%"></div>
                                </div>
                                <span class="fw-semibold"><?php echo $dia['ocupacion']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <?php if ($dia['ocupacion'] >= 90): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Óptima
                                </span>
                            <?php elseif ($dia['ocupacion'] >= 70): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Regular
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>Baja
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Ver Detalle">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Resumen de Recomendaciones -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recomendaciones</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Optimización de Horarios:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Considerar redistribuir horarios en días con baja ocupación</li>
                        <li>Evaluar la necesidad de aulas adicionales</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Estado Actual:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Distribución equilibrada de horarios</li>
                        <li>Uso eficiente de las aulas disponibles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de ocupación por día
const ctx1 = document.getElementById('ocupacionDiaChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($datos['por_dia'], 'dia')); ?>,
        datasets: [{
            label: 'Ocupación (%)',
            data: <?php echo json_encode(array_column($datos['por_dia'], 'ocupacion')); ?>,
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

// Gráfico de distribución de aulas
const ctx2 = document.getElementById('distribucionAulasChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Aulas Ocupadas', 'Aulas Disponibles'],
        datasets: [{
            data: [<?php echo $datos['aulas_ocupadas']; ?>, <?php echo $datos['aulas_disponibles']; ?>],
            backgroundColor: ['#f59e0b', '#10b981'],
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
