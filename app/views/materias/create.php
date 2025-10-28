<!-- Crear Materia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-plus-circle me-3 text-primary"></i>Crear Nueva Materia
        </h1>
        <p class="text-muted mb-0">Registra una nueva materia académica</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/materias" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Información de la Materia</h5>
            </div>
            <div class="card-body">
                <form id="formCrearMateria" onsubmit="return false;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Materia</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Matemáticas" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" placeholder="Ej: MAT101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel</label>
                            <select name="nivel" class="form-select" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="basico">Básico</option>
                                <option value="intermedio">Intermedio</option>
                                <option value="avanzado">Avanzado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carga Horaria</label>
                            <input type="number" name="carga_horaria" class="form-control" placeholder="4" min="1" max="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semestre</label>
                            <select name="semestre" class="form-select" required>
                                <option value="">Seleccionar semestre</option>
                                <option value="1">Primer Semestre</option>
                                <option value="2">Segundo Semestre</option>
                                <option value="3">Tercer Semestre</option>
                                <option value="4">Cuarto Semestre</option>
                                <option value="5">Quinto Semestre</option>
                                <option value="6">Sexto Semestre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="teorica">Teórica</option>
                                <option value="practica">Práctica</option>
                                <option value="mixta">Mixta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción de la materia..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="bi bi-check-circle me-1"></i>Crear Materia
                        </button>
                        <a href="/materias" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
                
<script>
document.getElementById('formCrearMateria').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creando...';
    
    const formData = new FormData(this);
    
    fetch('/materias/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect || '/materias';
        } else {
            alert('Error: ' + data.message);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crear Materia';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al crear la materia');
        btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle me-1"></i>Crear Materia';
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
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Tipos de Materia:</strong>
                </div>
                <ul class="list-unstyled">
                    <li><i class="bi bi-check text-success me-2"></i><strong>Teórica:</strong> Clases magistrales</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Práctica:</strong> Laboratorios y talleres</li>
                    <li><i class="bi bi-check text-success me-2"></i><strong>Mixta:</strong> Teoría y práctica</li>
                </ul>
            </div>
        </div>
    </div>
</div>
