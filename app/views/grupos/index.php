<!-- Gestión de Grupos -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-collection me-3 text-primary"></i>Gestión de Grupos
        </h1>
        <p class="text-muted mb-0">Administra los grupos académicos</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/grupos/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Grupo
            </a>
        </div>
    </div>
</div>

<!-- Tabla de Grupos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Grupos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Grupo</th>
                        <th class="border-0">Semestre</th>
                        <th class="border-0">Turno</th>
                        <th class="border-0">Materia</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $grupo): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($grupo['numero']); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($grupo['semestre']); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $grupo['turno'] === 'Mañana' ? 'warning' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($grupo['turno']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($grupo['materia']); ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/grupos/edit/<?php echo $grupo['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
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
