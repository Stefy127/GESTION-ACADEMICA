<!-- Gestión de Horarios -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-calendar-week me-3 text-primary"></i>Gestión de Horarios
        </h1>
        <p class="text-muted mb-0">Administra los horarios académicos</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/horarios/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Horario
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-calendar3 me-1"></i>Vista
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Tabla</a></li>
                <li><a class="dropdown-item" href="#">Calendario</a></li>
                <li><a class="dropdown-item" href="#">Por Docente</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Tabla de Horarios -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Horarios Semanales</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Día</th>
                        <th class="border-0">Horario</th>
                        <th class="border-0">Grupo</th>
                        <th class="border-0">Aula</th>
                        <th class="border-0">Docente</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $horario): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($horario['dia']); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($horario['hora_inicio'] . ' - ' . $horario['hora_fin']); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($horario['grupo']); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($horario['aula']); ?></span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <div class="avatar-initials bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr(explode(' ', $horario['docente'])[0], 0, 1) . substr(explode(' ', $horario['docente'])[1], 0, 1)); ?>
                                    </div>
                                </div>
                                <span class="text-muted"><?php echo htmlspecialchars($horario['docente']); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/horarios/edit/<?php echo $horario['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="confirmDelete(<?php echo $horario['id']; ?>)">
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
