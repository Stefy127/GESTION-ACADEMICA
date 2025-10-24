<!-- Gestión de Docentes -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-person-badge me-3 text-primary"></i>Gestión de Docentes
        </h1>
        <p class="text-muted mb-0">Administra la información de los docentes</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/docentes/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Registrar Docente
            </a>
        </div>
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
                        <th class="border-0">Email</th>
                        <th class="border-0">CI</th>
                        <th class="border-0">Teléfono</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($docentes as $docente): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-initials bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr($docente['nombre'], 0, 1) . substr($docente['apellido'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($docente['email']); ?></span>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($docente['ci']); ?></code>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($docente['telefono']); ?></span>
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
