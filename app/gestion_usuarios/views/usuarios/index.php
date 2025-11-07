<!-- Gestión de Usuarios -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-people me-3 text-primary"></i>Gestión de Usuarios
        </h1>
        <p class="text-muted mb-0">Administra los usuarios del sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/usuarios/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Usuario
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Exportar
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Excel</a></li>
                <li><a class="dropdown-item" href="#">PDF</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Tabla de Usuarios -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lista de Usuarios</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">Usuario</th>
                        <th class="border-0">Email</th>
                        <th class="border-0">Rol</th>
                        <th class="border-0">Estado</th>
                        <th class="border-0">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($usuario['rol'] ?? 'docente') === 'administrador' ? 'danger' : (($usuario['rol'] ?? 'docente') === 'coordinador' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($usuario['rol'] ?? 'docente'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success">Activo</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/usuarios/edit/<?php echo $usuario['id']; ?>" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="confirmDelete(<?php echo $usuario['id']; ?>)">
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
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        fetch(`/usuarios/delete/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario eliminado exitosamente');
                location.reload();
            } else {
                alert('Error al eliminar el usuario: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el usuario');
        });
    }
}
</script>
