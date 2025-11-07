<!-- Reporte de Aulas -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-building me-3 text-primary"></i>Reporte de Aulas
        </h1>
        <p class="text-muted mb-0">Uso y disponibilidad de aulas</p>
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
                <li><a class="dropdown-item" href="/reportes/exportar/aulas?formato=pdf"><i class="bi bi-file-pdf me-2"></i>PDF</a></li>
                <li><a class="dropdown-item" href="/reportes/exportar/aulas?formato=xlsx"><i class="bi bi-file-excel me-2"></i>Excel</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-value"><?php echo $datos['total_aulas']; ?></div>
            <div class="stat-label">Total Aulas</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo $datos['aulas_ocupadas']; ?></div>
            <div class="stat-label">Aulas Ocupadas</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="stat-value"><?php echo $datos['aulas_disponibles']; ?></div>
            <div class="stat-label">Aulas Disponibles</div>
        </div>
    </div>
</div>

<!-- Gráfico de Distribución -->
<div class="row g-4 mb-4">
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
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Uso por Tipo</h5>
            </div>
            <div class="card-body">
                <canvas id="usoPorTipoChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Detalle de Aulas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Nombre</th>
                        <th class="border-0">Código</th>
                        <th class="border-0">Capacidad</th>
                        <th class="border-0">Tipo</th>
                        <th class="border-0">Ubicación</th>
                        <th class="border-0">Horarios</th>
                        <th class="border-0">Días Ocupados</th>
                        <th class="border-0">Docentes</th>
                        <th class="border-0">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos['aulas'])): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox me-2"></i>No hay aulas registradas
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($datos['aulas'] as $aula): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($aula['nombre']); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($aula['codigo']); ?></span>
                        </td>
                        <td>
                            <span class="fw-semibold"><?php echo $aula['capacidad']; ?></span>
                        </td>
                        <td>
                            <span><?php echo htmlspecialchars($aula['tipo'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="text-muted small"><?php echo htmlspecialchars($aula['ubicacion'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $aula['horarios_asignados']; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $aula['dias_ocupados']; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?php echo $aula['docentes_asignados']; ?></span>
                        </td>
                        <td>
                            <?php if ($aula['ocupada']): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock-history me-1"></i>Ocupada
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Disponible
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
// Gráfico de distribución de aulas
const ctx1 = document.getElementById('distribucionAulasChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Ocupadas', 'Disponibles'],
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

// Gráfico de uso por tipo
const tipos = <?php echo json_encode(array_unique(array_column($datos['aulas'], 'tipo'))); ?>;
const usoPorTipo = {};
<?php foreach ($datos['aulas'] as $aula): ?>
const tipo = '<?php echo $aula['tipo'] ?? 'Sin Tipo'; ?>';
if (!usoPorTipo[tipo]) usoPorTipo[tipo] = 0;
if (<?php echo $aula['ocupada'] ? 'true' : 'false'; ?>) usoPorTipo[tipo]++;
<?php endforeach; ?>

const ctx2 = document.getElementById('usoPorTipoChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: Object.keys(usoPorTipo),
        datasets: [{
            label: 'Aulas Ocupadas',
            data: Object.values(usoPorTipo),
            backgroundColor: '#6366f1',
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

