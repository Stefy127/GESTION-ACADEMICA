<!-- Bitácora de Actividades -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-journal-text me-3 text-primary"></i>Bitácora de Actividades
        </h1>
        <p class="text-muted mb-0">Registro de todas las acciones del sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
            </button>
            <button class="btn btn-outline-secondary" onclick="exportBitacora()">
                <i class="bi bi-download me-1"></i>Exportar
            </button>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Usuario</label>
                        <select class="form-select" onchange="filterByUser(this.value)">
                            <option value="">Todos los usuarios</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Acción</label>
                        <select class="form-select" onchange="filterByAction(this.value)">
                            <option value="">Todas las acciones</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Tabla</label>
                        <select class="form-select" onchange="filterByTable(this.value)">
                            <option value="">Todas las tablas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" placeholder="Buscar en bitácora..." onkeyup="searchActivities(this.value)">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Bitácora -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-check me-2"></i>
            Registro Completo
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">ID</th>
                        <th class="border-0">Usuario</th>
                        <th class="border-0">Acción</th>
                        <th class="border-0">Tabla/Recurso</th>
                        <th class="border-0">Fecha</th>
                        <th class="border-0">IP</th>
                        <th class="border-0">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $activitiesList = is_array($activities) ? ($activities['activities'] ?? $activities) : $activities;
                    $totalActivities = is_array($activities) && isset($activities['total']) ? $activities['total'] : count($activitiesList);
                    $currentPage = is_array($activities) && isset($activities['page']) ? $activities['page'] : 1;
                    $totalPages = ceil($totalActivities / 50);
                    ?>
                    <?php if (empty($activitiesList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No hay actividades registradas</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($activitiesList as $activity): ?>
                    <tr>
                        <td class="ps-4">
                            <code>#<?php echo $activity['id'] ?? 'N/A'; ?></code>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($activity['nombre_usuario'] ?? 'Sistema'); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($activity['accion'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($activity['tabla_afectada'] ?? 'N/A'); ?>
                            </span>
                            <?php if (!empty($activity['registro_id'])): ?>
                            <small class="text-muted d-block">ID: <?php echo $activity['registro_id']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock text-muted me-2"></i>
                                <span><?php echo $activity['created_at'] ?? 'N/A'; ?></span>
                            </div>
                        </td>
                        <td>
                            <code class="text-muted small"><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></code>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $activity['id']; ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            <!-- Modal de Detalles -->
                            <div class="modal fade" id="detailsModal<?php echo $activity['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalles de Actividad #<?php echo $activity['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row">
                                                <dt class="col-sm-4">Usuario:</dt>
                                                <dd class="col-sm-8"><?php echo htmlspecialchars($activity['nombre_usuario'] ?? 'Sistema'); ?></dd>
                                                
                                                <dt class="col-sm-4">Acción:</dt>
                                                <dd class="col-sm-8"><span class="badge bg-info"><?php echo htmlspecialchars($activity['accion'] ?? 'N/A'); ?></span></dd>
                                                
                                                <dt class="col-sm-4">Tabla:</dt>
                                                <dd class="col-sm-8"><?php echo htmlspecialchars($activity['tabla_afectada'] ?? 'N/A'); ?></dd>
                                                
                                                <dt class="col-sm-4">Registro ID:</dt>
                                                <dd class="col-sm-8"><?php echo $activity['registro_id'] ?? 'N/A'; ?></dd>
                                                
                                                <dt class="col-sm-4">Fecha:</dt>
                                                <dd class="col-sm-8"><?php echo $activity['created_at'] ?? 'N/A'; ?></dd>
                                                
                                                <dt class="col-sm-4">IP:</dt>
                                                <dd class="col-sm-8"><code><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></code></dd>
                                                
                                                <?php if (!empty($activity['user_agent'])): ?>
                                                <dt class="col-sm-4">Navegador:</dt>
                                                <dd class="col-sm-8"><small class="text-muted"><?php echo htmlspecialchars($activity['user_agent']); ?></small></dd>
                                                <?php endif; ?>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Mostrando <?php echo count($activitiesList); ?> de <?php echo $totalActivities; ?> registros
                <?php if ($totalPages > 1): ?>
                | Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                <?php endif; ?>
            </small>
            <div class="btn-group btn-group-sm">
                <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>" class="btn btn-outline-primary">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportBitacora() {
    alert('Función de exportación en desarrollo');
}

function filterByUser(userId) {
    // Implementar filtro por usuario
    if (userId) {
        window.location.href = '?user=' + userId;
    } else {
        window.location.href = '?';
    }
}

function filterByAction(action) {
    // Implementar filtro por acción
}

function filterByTable(table) {
    // Implementar filtro por tabla
}

function searchActivities(query) {
    // Implementar búsqueda
}
</script>

