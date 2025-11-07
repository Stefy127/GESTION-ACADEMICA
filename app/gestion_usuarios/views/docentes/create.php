<!-- Registrar Docente -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Registrar Nuevo Docente
        </h1>
        <p class="text-muted mb-0">Registra un nuevo docente en el sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/docentes" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>
</div>

<!-- Formulario de Registro -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Docente</h5>
            </div>
            <div class="card-body">
                <form id="createDocenteForm">
                    <h6 class="text-muted mb-3">Información Personal</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Nombre del docente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" class="form-control" placeholder="Apellido del docente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cédula de Identidad <span class="text-danger">*</span></label>
                            <input type="text" name="ci" class="form-control" placeholder="12345678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="docente@universidad.edu" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" placeholder="555-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña Temporal <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" value="password" required>
                            <small class="text-muted">El docente podrá cambiarla después de iniciar sesión</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3">Información Académica</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Título Profesional</label>
                            <input type="text" name="titulo_profesional" class="form-control" placeholder="Ing. en Sistemas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grado Académico</label>
                            <input type="text" name="grado_academico" class="form-control" placeholder="Licenciado, Magister, Doctor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especialidad</label>
                            <input type="text" name="especialidad" class="form-control" placeholder="Matemáticas, Física, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Universidad de Egreso</label>
                            <input type="text" name="universidad_egresado" class="form-control" placeholder="Universidad Nacional">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Departamento</label>
                            <input type="text" name="departamento" class="form-control" placeholder="Matemáticas">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Años de Experiencia</label>
                            <input type="number" name="anos_experiencia" class="form-control" placeholder="5" min="0" max="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <input type="text" name="categoria" class="form-control" placeholder="Titular, Asociado">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dedicación</label>
                            <select name="dedicacion" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="tiempo_completo">Tiempo Completo</option>
                                <option value="medio_tiempo">Medio Tiempo</option>
                                <option value="por_horas">Por Horas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Registrar Docente
                        </button>
                        <a href="/docentes" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Información</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Nota:</strong> El docente recibirá un email con sus credenciales de acceso.
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ayuda</h5>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    <i class="bi bi-check-circle me-1"></i> Campos con <span class="text-danger">*</span> son obligatorios<br>
                    <i class="bi bi-shield-check me-1"></i> La contraseña temporal puede cambiarse después<br>
                    <i class="bi bi-person-badge me-1"></i> La información académica es opcional
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createDocenteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Registrando...';
    submitBtn.disabled = true;
    
    fetch('/docentes/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/docentes';
        } else {
            alert('Error: ' + data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al registrar el docente');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
