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
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" placeholder="Nombre del docente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" class="form-control" placeholder="Apellido del docente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="docente@universidad.edu" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cédula de Identidad</label>
                            <input type="text" class="form-control" placeholder="12345678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" placeholder="555-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especialidad</label>
                            <input type="text" class="form-control" placeholder="Matemáticas, Física, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Título Profesional</label>
                            <input type="text" class="form-control" placeholder="Ingeniero, Licenciado, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Años de Experiencia</label>
                            <input type="number" class="form-control" placeholder="5" min="0" max="50">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea class="form-control" rows="2" placeholder="Dirección completa del docente"></textarea>
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
        <div class="card">
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
    </div>
</div>
