<!-- Grupos de la Materia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-collection me-3 text-primary"></i>Grupos de la Materia: <?php echo htmlspecialchars($materia['nombre'] ?? 'N/A'); ?>
        </h1>
        <p class="text-muted mb-0">Grupos asignados a esta materia</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/materias" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver a Materias
            </a>
        </div>
    </div>
</div>

<!-- Detalles de la Materia -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Detalles de la Materia</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($materia['nombre'] ?? 'N/A'); ?></p>
                <p><strong>Código:</strong> <code><?php echo htmlspecialchars($materia['codigo'] ?? 'N/A'); ?></code></p>
                <p><strong>Nivel:</strong> <?php echo htmlspecialchars($materia['nivel'] ?? 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Carga Horaria:</strong> <?php echo htmlspecialchars($materia['carga_horaria'] ?? 'N/A'); ?> horas</p>
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($materia['descripcion'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Grupos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Grupos Asignados</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Número de Grupo</th>
                        <th class="border-0">Semestre</th>
                        <th class="border-0">Turno</th>
                        <th class="border-0">Capacidad Máxima</th>
                        <th class="border-0">Docente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($grupos)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No hay grupos asignados a esta materia.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($grupo['numero'] ?? 'N/A'); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($grupo['semestre'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($grupo['turno'] ?? '') === 'Mañana' ? 'warning' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($grupo['turno'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($grupo['capacidad_maxima'] ?? 'N/A'); ?> estudiantes</span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($grupo['docente_nombre'] ?? 'N/A'); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

