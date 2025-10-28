<!-- Gestión de Aulas -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-building me-3 text-primary"></i>Gestión de Aulas
        </h1>
        <p class="text-muted mb-0">Administra las aulas y espacios académicos</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/aulas/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nueva Aula
            </a>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-value"><?php echo count($aulas); ?></div>
            <div class="stat-label">Total Aulas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo count(array_filter($aulas, fn($a) => isset($a['activa']) && $a['activa'])); ?></div>
            <div class="stat-label">Activas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-value"><?php echo count(array_filter($aulas, fn($a) => isset($a['activa']) && !$a['activa'])); ?></div>
            <div class="stat-label">Inactivas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?php echo array_sum(array_column($aulas, 'capacidad')); ?></div>
            <div class="stat-label">Capacidad Total</div>
        </div>
    </div>
</div>

<!-- Tabla de Aulas -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Aulas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Aula</th>
                        <th class="border-0">Capacidad</th>
                        <th class="border-0">Tipo</th>
                        <th class="border-0">Estado</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aulas as $aula): ?>
                    <tr>
                        <td class="ps-4">
                            <div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($aula['nombre'] ?? 'N/A'); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($aula['codigo'] ?? ''); ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo $aula['capacidad'] ?? 0; ?> estudiantes</span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($aula['tipo'] ?? '') === 'Laboratorio' ? 'info' : 'primary'; ?>">
                                <?php echo htmlspecialchars($aula['tipo'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (isset($aula['activa']) && $aula['activa']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Activa
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Inactiva
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/aulas/edit/<?php echo $aula['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/aulas/horarios/<?php echo $aula['id']; ?>" class="btn btn-outline-info" data-bs-toggle="tooltip" title="Ver Horarios">
                                    <i class="bi bi-calendar-week"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="confirmDelete(<?php echo $aula['id']; ?>)">
                                    <i class="bi bi-trash"></i>
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

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta aula?')) {
        fetch(`/aulas/delete/${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al eliminar el aula');
        });
    }
}
</script>
