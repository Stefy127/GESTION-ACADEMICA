<!-- Reportes de Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-graph-up me-3 text-primary"></i>Reportes de Asistencia
        </h1>
        <p class="text-muted mb-0">Consulta y analiza la asistencia docente</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
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

<!-- Filtros de Reporte -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros de Reporte</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Período</label>
                <select class="form-select">
                    <option>Último mes</option>
                    <option>Últimos 3 meses</option>
                    <option>Último año</option>
                    <option>Personalizado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Docente</label>
                <select class="form-select">
                    <option>Todos los docentes</option>
                    <option>Juan Pérez</option>
                    <option>María González</option>
                    <option>Carlos López</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Materia</label>
                <select class="form-select">
                    <option>Todas las materias</option>
                    <option>Matemáticas</option>
                    <option>Física</option>
                    <option>Química</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen General -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $reportes[0]['total_clases'] ?? 0; ?></div>
            <div class="stat-label">Total Clases</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $reportes[0]['asistencias'] ?? 0; ?></div>
            <div class="stat-label">Asistencias</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value"><?php echo $reportes[0]['ausencias'] ?? 0; ?></div>
            <div class="stat-label">Ausencias</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value"><?php echo $reportes[0]['porcentaje'] ?? 0; ?>%</div>
            <div class="stat-label">Porcentaje</div>
        </div>
    </div>
</div>

<!-- Tabla de Reportes por Docente -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Asistencia por Docente</h5>
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
                        <th class="border-0">Porcentaje</th>
                        <th class="border-0">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportes as $reporte): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-initials bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php 
                                        $docente = $reporte['docente'] ?? 'Usuario';
                                        $nombres = explode(' ', $docente);
                                        $iniciales = '';
                                        if (count($nombres) >= 2) {
                                            $iniciales = strtoupper(substr($nombres[0], 0, 1) . substr($nombres[1], 0, 1));
                                        } else {
                                            $iniciales = strtoupper(substr($docente, 0, 2));
                                        }
                                        echo $iniciales;
                                        ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($docente); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo $reporte['total_clases'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="text-success fw-semibold"><?php echo $reporte['asistencias'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="text-danger fw-semibold"><?php echo $reporte['ausencias'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($reporte['porcentaje'] ?? 0) >= 95 ? 'success' : (($reporte['porcentaje'] ?? 0) >= 85 ? 'warning' : 'danger'); ?>">
                                <?php echo $reporte['porcentaje'] ?? 0; ?>%
                            </span>
                        </td>
                        <td>
                            <?php $porcentaje = $reporte['porcentaje'] ?? 0; ?>
                            <?php if ($porcentaje >= 95): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Excelente
                                </span>
                            <?php elseif ($porcentaje >= 85): ?>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Gráfico de Asistencia -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Tendencia de Asistencia</h5>
    </div>
    <div class="card-body">
        <canvas id="asistenciaChart" height="100"></canvas>
    </div>
</div>

<script>
// Gráfico de tendencia de asistencia
const ctx = document.getElementById('asistenciaChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Porcentaje de Asistencia',
            data: [95, 92, 98, 94, 96, 97],
            borderColor: '#6366f1',
            backgroundColor: '#6366f120',
            borderWidth: 3,
            fill: true,
            tension: 0.4
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
</script>
