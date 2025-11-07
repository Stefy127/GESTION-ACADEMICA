<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Gestión Académica'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar Moderno -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-mortarboard-fill"></i>
                <span>Gestión Académica</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($user)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="avatar-sm me-2">
                                    <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold"><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></span>
                                    <small class="text-muted"><?php echo ucfirst($user['rol']); ?></small>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile">
                                    <i class="bi bi-person me-2"></i>Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="/settings">
                                    <i class="bi bi-gear me-2"></i>Configuración
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="/login">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container-fluid">
        <?php if (isset($user)): ?>
            <!-- Sidebar -->
            <div class="row">
                <div class="col-md-2 sidebar">
                    <div class="position-sticky pt-4">
                        <div class="px-3 mb-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3">Principal</h6>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false) ? 'active' : ''; ?>" href="/dashboard">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        </ul>
                        
                        <?php if (in_array($user['rol'], ['administrador', 'coordinador'])): ?>
                            <div class="px-3 mb-4 mt-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3">Gestión</h6>
                            </div>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/usuarios') !== false) ? 'active' : ''; ?>" href="/usuarios">
                                        <i class="bi bi-people"></i>
                                        <span>Usuarios</span>
                                    </a>
                                </li>
                                <?php if (in_array($user['rol'], ['administrador', 'coordinador'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/docentes') !== false) ? 'active' : ''; ?>" href="/docentes">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Docentes</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/materias') !== false) ? 'active' : ''; ?>" href="/materias">
                                        <i class="bi bi-book"></i>
                                        <span>Materias</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/grupos') !== false) ? 'active' : ''; ?>" href="/grupos">
                                        <i class="bi bi-collection"></i>
                                        <span>Grupos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/aulas') !== false) ? 'active' : ''; ?>" href="/aulas">
                                        <i class="bi bi-building"></i>
                                        <span>Aulas</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/horarios') !== false) ? 'active' : ''; ?>" href="/horarios">
                                        <i class="bi bi-calendar-week"></i>
                                        <span>Horarios</span>
                                    </a>
                                </li>
                            </ul>
                        <?php endif; ?>
                        
                        <div class="px-3 mb-4 mt-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3">Actividades</h6>
                        </div>
                        <ul class="nav flex-column">
                            <?php if ($user['rol'] === 'docente'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/asistencia/registrar') !== false) ? 'active' : ''; ?>" href="/asistencia/registrar">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Registrar Asistencia</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php 
                                    $isAsistenciaActive = (strpos($_SERVER['REQUEST_URI'], '/asistencia') !== false && strpos($_SERVER['REQUEST_URI'], '/asistencia/registrar') === false);
                                    echo $isAsistenciaActive ? 'active' : ''; 
                                ?>" href="/asistencia">
                                    <i class="bi bi-list-check"></i>
                                    <span>Mis Asistencias</span>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/asistencia') !== false) ? 'active' : ''; ?>" href="/asistencia">
                                    <i class="bi bi-check-circle"></i>
                                    <span>Asistencia</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php if (in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/reportes') !== false) ? 'active' : ''; ?>" href="/reportes">
                                        <i class="bi bi-graph-up"></i>
                                        <span>Reportes</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($user['rol'] === 'administrador'): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/bitacora') !== false) ? 'active' : ''; ?>" href="/bitacora">
                                        <i class="bi bi-journal-text"></i>
                                        <span>Bitácora</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if ($user['rol'] === 'administrador'): ?>
                            <div class="px-3 mb-4 mt-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3">Administración</h6>
                            </div>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/carga-masiva') !== false) ? 'active' : ''; ?>" href="/carga-masiva">
                                        <i class="bi bi-upload"></i>
                                        <span>Carga Masiva</span>
                                    </a>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contenido principal -->
                <div class="col-md-10 ms-sm-auto px-md-4">
                    <div class="pt-4 pb-4">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Contenido para docentes o usuarios no autenticados -->
            <div class="container mt-4">
                <?php echo $content; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2024 Sistema de Gestión Académica. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Modal para cambio de contraseña obligatorio (solo para docentes en primer login) -->
    <?php if (isset($user) && isset($user['needs_password_change']) && $user['needs_password_change']): ?>
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="bi bi-shield-lock me-2"></i>Cambio de Contraseña Requerido
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Bienvenido al sistema.</strong> Por seguridad, debes cambiar tu contraseña antes de continuar.
                    </div>
                    <form id="changePasswordForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div id="changePasswordError" class="alert alert-danger d-none"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitChangePassword">
                        <i class="bi bi-check-circle me-1"></i>Cambiar Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/app.js"></script>
    
    <?php if (isset($user) && isset($user['needs_password_change']) && $user['needs_password_change']): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar modal automáticamente
        const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        changePasswordModal.show();
        
        // Toggle password visibility
        document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
            const input = document.getElementById('current_password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const input = document.getElementById('new_password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const input = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Validar que las contraseñas coincidan
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword && confirmPassword !== '') {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Submit form
        document.getElementById('submitChangePassword').addEventListener('click', function() {
            const form = document.getElementById('changePasswordForm');
            const formData = new FormData(form);
            const submitBtn = this;
            const originalText = submitBtn.innerHTML;
            
            // Validar formulario
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Procesando...';
            
            // Ocultar errores anteriores
            document.getElementById('changePasswordError').classList.add('d-none');
            
            fetch('/auth/change-password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    const modalBody = document.querySelector('#changePasswordModal .modal-body');
                    modalBody.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                    
                    // Cerrar modal y recargar página después de 1 segundo
                    setTimeout(() => {
                        changePasswordModal.hide();
                        window.location.reload();
                    }, 1000);
                } else {
                    // Mostrar error
                    const errorDiv = document.getElementById('changePasswordError');
                    errorDiv.textContent = data.message || 'Error al cambiar la contraseña';
                    errorDiv.classList.remove('d-none');
                    
                    // Mostrar errores de campos si existen
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback && feedback.classList.contains('invalid-feedback')) {
                                    feedback.textContent = data.errors[field];
                                }
                            }
                        });
                    }
                    
                    // Habilitar botón
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.getElementById('changePasswordError');
                errorDiv.textContent = 'Ocurrió un error al cambiar la contraseña. Por favor, intenta nuevamente.';
                errorDiv.classList.remove('d-none');
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>