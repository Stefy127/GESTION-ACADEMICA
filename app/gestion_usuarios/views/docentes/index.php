<!-- Gestión de Docentes -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-person-badge me-3 text-primary"></i>Gestión de Docentes
        </h1>
        <p class="text-muted mb-0">Administra la información académica de los docentes</p>
        <small class="text-muted"><i class="bi bi-info-circle"></i> Los usuarios docentes se crean en el módulo de Usuarios</small>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-value"><?php echo count($docentes); ?></div>
            <div class="stat-label">Total Docentes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo count($docentes); ?></div>
            <div class="stat-label">Activos</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="stat-value">15</div>
            <div class="stat-label">Horas Promedio</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value">95%</div>
            <div class="stat-label">Asistencia</div>
        </div>
    </div>
</div>

<!-- Tabla de Docentes -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Docentes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Docente</th>
                        <th class="border-0">Título/Especialidad</th>
                        <th class="border-0">Departamento</th>
                        <th class="border-0">Experiencia</th>
                        <th class="border-0">Estado de Información</th>
                        <th class="border-0">Contacto</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($docentes)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No hay docentes registrados</p>
                            <small class="text-muted">Crea usuarios con rol "docente" en el módulo de Usuarios</small>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php foreach ($docentes as $docente): 
                        // Determinar el estado de la información
                        $infoCompleta = !empty($docente['titulo_profesional']) && 
                                       !empty($docente['especialidad']) && 
                                       !empty($docente['departamento']);
                        $infoParcial = !empty($docente['titulo_profesional']) || 
                                      !empty($docente['especialidad']) || 
                                      !empty($docente['departamento']);
                        $estado = $infoCompleta ? 'completa' : ($infoParcial ? 'parcial' : 'sin_info');
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-initials bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr($docente['nombre'] ?? '', 0, 1) . substr($docente['apellido'] ?? '', 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars(($docente['nombre'] ?? '') . ' ' . ($docente['apellido'] ?? '')); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($docente['email'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="fw-semibold small"><?php echo htmlspecialchars($docente['titulo_profesional'] ?? 'N/A'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($docente['especialidad'] ?? ''); ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($docente['departamento'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <div>
                                <div><?php echo htmlspecialchars($docente['anos_experiencia'] ?? 0); ?> años</div>
                                <small class="text-muted"><?php echo htmlspecialchars($docente['categoria'] ?? ''); ?></small>
                            </div>
                        </td>
                        <td>
                            <?php if ($estado === 'completa'): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Completa
                                </span>
                            <?php elseif ($estado === 'parcial'): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Falta Información
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>Sin Información
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <div class="small"><i class="bi bi-phone"></i> <?php echo htmlspecialchars($docente['telefono'] ?? 'N/A'); ?></div>
                                <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($docente['ci'] ?? 'N/A'); ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/docentes/edit/<?php echo $docente['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="Ver Horarios">
                                    <i class="bi bi-calendar-week"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

