<!-- Reporte de Docentes -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-person-badge me-3 text-primary"></i>Reporte de Docentes
        </h1>
        <p class="text-muted mb-0">Información de docentes y carga horaria</p>
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
                <li><a class="dropdown-item" href="/reportes/exportar/docentes?formato=pdf"><i class="bi bi-file-pdf me-2"></i>PDF</a></li>
                <li><a class="dropdown-item" href="/reportes/exportar/docentes?formato=xlsx"><i class="bi bi-file-excel me-2"></i>Excel</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo $datos['total_docentes']; ?></div>
            <div class="stat-label">Total Docentes</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value">
                <?php 
                $totalHorarios = array_sum(array_column($datos['docentes'], 'total_horarios'));
                echo $totalHorarios;
                ?>
            </div>
            <div class="stat-label">Total Horarios</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value">
                <?php 
                $promedioHorarios = $datos['total_docentes'] > 0 ? round(array_sum(array_column($datos['docentes'], 'carga_horaria')) / $datos['total_docentes'], 1) : 0;
                echo $promedioHorarios;
                ?>
            </div>
            <div class="stat-label">Promedio Carga Horaria</div>
        </div>
    </div>
</div>

<!-- Tabla Detallada -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Docentes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">CI</th>
                        <th class="border-0">Nombre</th>
                        <th class="border-0">Email</th>
                        <th class="border-0">Título</th>
                        <th class="border-0">Especialidad</th>
                        <th class="border-0">Departamento</th>
                        <th class="border-0">Años Exp.</th>
                        <th class="border-0">Horarios</th>
                        <th class="border-0">Grupos</th>
                        <th class="border-0">Carga Horaria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos['docentes'])): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox me-2"></i>No hay docentes registrados
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($datos['docentes'] as $docente): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="fw-semibold"><?php echo htmlspecialchars($docente['ci'] ?? ''); ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']); ?></div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($docente['email'] ?? ''); ?></span>
                        </td>
                        <td>
                            <span><?php echo htmlspecialchars($docente['titulo_profesional'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span><?php echo htmlspecialchars($docente['especialidad'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span><?php echo htmlspecialchars($docente['departamento'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $docente['anos_experiencia'] ?? 0; ?> años</span>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $docente['total_horarios'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-success"><?php echo $docente['total_grupos'] ?? 0; ?></span>
                        </td>
                        <td>
                            <span class="fw-semibold text-primary"><?php echo $docente['carga_horaria'] ?? 0; ?> hrs/sem</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

