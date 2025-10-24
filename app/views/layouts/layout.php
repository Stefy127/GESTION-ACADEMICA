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
        <?php if (isset($user) && $user['rol'] !== 'docente'): ?>
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
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/docentes') !== false) ? 'active' : ''; ?>" href="/docentes">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Docentes</span>
                                    </a>
                                </li>
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
                            <li class="nav-item">
                                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/asistencia') !== false) ? 'active' : ''; ?>" href="/asistencia">
                                    <i class="bi bi-check-circle"></i>
                                    <span>Asistencia</span>
                                </a>
                            </li>
                            
                            <?php if (in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/reportes') !== false) ? 'active' : ''; ?>" href="/reportes">
                                        <i class="bi bi-graph-up"></i>
                                        <span>Reportes</span>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>