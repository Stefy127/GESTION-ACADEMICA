<!-- Editar Usuario -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-pencil me-3 text-primary"></i>Editar Usuario
        </h1>
        <p class="text-muted mb-0">Modifica la información del usuario</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Edición -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <form id="formEditarUsuario" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['nombre']; ?>" <?php echo isset($usuario['rol']) && $usuario['rol'] === $rol['nombre'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($rol['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar vacío para mantener actual">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirm" class="form-control" placeholder="Confirmar nueva contraseña">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                        <a href="/usuarios" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
                
<script>
document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = this.querySelector('[name="password"]').value;
    const passwordConfirm = this.querySelector('[name="password_confirm"]').value;
    
    if (password && password !== passwordConfirm) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Guardando...';
    
    const formData = new FormData(this);
    formData.delete('password_confirm'); // No enviar confirmación
    
    fetch('/usuarios/update/<?php echo htmlspecialchars($usuario['id'] ?? ''); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/usuarios';
        } else {
            alert('Error: ' + data.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al actualizar el usuario');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Guardar Cambios';
    });
});
</script>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Nota:</strong> Los cambios de rol afectarán los permisos del usuario.
                </div>
            </div>
        </div>
    </div>
</div>
