<!-- Crear Usuario -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Crear Nuevo Usuario
        </h1>
        <p class="text-muted mb-0">Registra un nuevo usuario en el sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Creación -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <form id="formCrearUsuario" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cédula de Identidad</label>
                            <input type="text" name="ci" class="form-control" placeholder="CI del usuario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Nombre del usuario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" placeholder="Apellido del usuario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="usuario@universidad.edu" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['nombre']; ?>"><?php echo ucfirst($rol['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Contraseña temporal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirm" class="form-control" placeholder="Confirmar contraseña" required>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-1"></i>Crear Usuario
                        </button>
                        <a href="/usuarios" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
                
<script>
document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = this.querySelector('[name="password"]').value;
    const passwordConfirm = this.querySelector('[name="password_confirm"]').value;
    
    if (password !== passwordConfirm) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creando...';
    
    const formData = new FormData(this);
    formData.delete('password_confirm'); // No enviar confirmación
    
    fetch('/usuarios/store', {
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
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crear Usuario';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear el usuario');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crear Usuario';
    });
});
</script>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Roles del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Permisos por rol:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-shield-check text-danger me-2"></i><strong>Administrador:</strong> Acceso completo</li>
                    <li><i class="bi bi-shield text-warning me-2"></i><strong>Coordinador:</strong> Gestión académica</li>
                    <li><i class="bi bi-person text-info me-2"></i><strong>Docente:</strong> Solo asistencia</li>
                    <li><i class="bi bi-eye text-secondary me-2"></i><strong>Autoridad:</strong> Solo reportes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
