<!-- Crear Aula -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Crear Nueva Aula
        </h1>
        <p class="text-muted mb-0">Registra una nueva aula en el sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/aulas" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información de la Aula</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Aula</label>
                            <input type="text" class="form-control" placeholder="Ej: Aula 101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacidad</label>
                            <input type="number" class="form-control" placeholder="30" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Aula</label>
                            <select class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="teoria">Teoría</option>
                                <option value="laboratorio">Laboratorio</option>
                                <option value="computacion">Computación</option>
                                <option value="audiovisual">Audiovisual</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" required>
                                <option value="disponible">Disponible</option>
                                <option value="mantenimiento">En Mantenimiento</option>
                                <option value="ocupada">Ocupada</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" rows="3" placeholder="Descripción adicional de la aula..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Crear Aula
                        </button>
                        <a href="/aulas" class="btn btn-outline-secondary ms-2">
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
                    <strong>Tipos de Aula:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check text-success me-2"></i><strong>Teoría:</strong> Para clases magistrales</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Laboratorio:</strong> Para prácticas</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Computación:</strong> Con equipos de cómputo</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Audiovisual:</strong> Con proyector y audio</li>
                </ul>
            </div>
        </div>
    </div>
</div>
