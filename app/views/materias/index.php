<!-- Gestión de Materias -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-book me-3 text-primary"></i>Gestión de Materias
        </h1>
        <p class="text-muted mb-0">Administra las materias académicas</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/materias/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nueva Materia
            </a>
        </div>
    </div>
</div>

<!-- Tabla de Materias -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Materias</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Materia</th>
                        <th class="border-0">Código</th>
                        <th class="border-0">Nivel</th>
                        <th class="border-0">Carga Horaria</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias as $materia): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($materia['nombre']); ?></div>
                        </td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($materia['codigo']); ?></code>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $materia['nivel'] === 'Básico' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($materia['nivel']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo $materia['carga_horaria']; ?> horas</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/materias/edit/<?php echo $materia['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-info" data-bs-toggle="tooltip" title="Ver Grupos">
                                    <i class="bi bi-collection"></i>
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
