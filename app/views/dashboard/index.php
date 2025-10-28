<!-- Header del Dashboard -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-speedometer2 me-3 text-primary"></i>Dashboard
        </h1>
        <p class="text-muted mb-0">Bienvenido de vuelta, <?php echo htmlspecialchars($user['nombre']); ?></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-calendar3 me-1"></i>Período
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="changePeriod('today')">Hoy</a></li>
                <li><a class="dropdown-item" href="#" onclick="changePeriod('week')">Esta Semana</a></li>
                <li><a class="dropdown-item" href="#" onclick="changePeriod('month')">Este Mes</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Tarjetas de Estadísticas Modernas -->
<div class="row g-4 mb-5">
    <?php if ($user['rol'] === 'administrador'): ?>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_usuarios']); ?></div>
                <div class="stat-label">Total Usuarios</div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="bi bi-arrow-up me-1"></i>+12% este mes
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-value">
                <?php 
                $key = ($user['rol'] === 'docente') ? 'grupos_asignados' : 'total_docentes';
                echo number_format($stats[$key] ?? 0); 
                ?>
            </div>
            <div class="stat-label">
                <?php echo ($user['rol'] === 'docente') ? 'Mis Grupos' : 'Total Docentes'; ?>
            </div>
            <div class="mt-2">
                <small class="text-info">
                    <i class="bi bi-check-circle me-1"></i>Activos
                </small>
            </div>
        </div>
    </div>

    <?php if (in_array($user['rol'], ['administrador', 'coordinador'])): ?>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['horarios_activos']); ?></div>
                <div class="stat-label">Horarios Activos</div>
                <div class="mt-2">
                    <small class="text-warning">
                        <i class="bi bi-clock me-1"></i>En curso
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo number_format($stats['asistencia_hoy']); ?></div>
            <div class="stat-label">Asistencia Hoy</div>
            <div class="mt-2">
                <small class="text-success">
                    <i class="bi bi-graph-up me-1"></i>+5% vs ayer
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos Modernos -->
<div class="row g-4 mb-5">
    <?php if ($user['rol'] === 'docente'): ?>
    <!-- Vista para Docentes -->
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1 fw-bold">Mis Asistencias</h5>
                    <p class="text-muted mb-0 small">Últimos 30 días</p>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="asistenciaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1 fw-bold">Mis Horarios</h5>
                <p class="text-muted mb-0 small">Distribución semanal</p>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="horariosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Vista para Administradores/Coordinadores -->
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1 fw-bold">Asistencia Mensual</h5>
                    <p class="text-muted mb-0 small">Tendencia de los últimos 30 días</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportChart('asistencia_mensual')">
                            <i class="bi bi-download me-2"></i>Exportar
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="refreshChart('asistencia_mensual')">
                            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                        </a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="asistenciaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1 fw-bold">Reportes del Sistema</h5>
                <p class="text-muted mb-0 small">Estadísticas generales</p>
            </div>
            <div class="card-body">
                <p class="text-muted text-center py-5">
                    <i class="bi bi-graph-up fs-3"></i><br>
                    Visualización de reportes
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tabla de Actividades Recientes -->
<?php if (in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1 fw-bold">Actividades Recientes</h5>
                    <p class="text-muted mb-0 small">Últimas acciones del sistema</p>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="refreshActivities()">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <button class="btn btn-outline-secondary" onclick="exportActivities()">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person me-2 text-primary"></i>
                                        Usuario
                                    </div>
                                </th>
                                <th class="border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-lightning me-2 text-warning"></i>
                                        Acción
                                    </div>
                                </th>
                                <th class="border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-2 text-info"></i>
                                        Fecha
                                    </div>
                                </th>
                                <th class="border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-geo-alt me-2 text-secondary"></i>
                                        IP
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="activitiesTableBody">
                            <!-- Se cargará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando las últimas 10 actividades</small>
                    <a href="/logs" class="btn btn-sm btn-outline-primary">
                        Ver Todas <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Los gráficos se cargan desde app.js
// Solo cargar actividades recientes aquí
document.addEventListener('DOMContentLoaded', function() {
    loadRecentActivities();
});

function loadRecentActivities() {
    <?php if (!isset($user) || !in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
    return; // No cargar actividades para docentes
    <?php endif; ?>
    
    // Simular carga de actividades recientes
    const activities = [
        { usuario: 'Admin Sistema', accion: 'Inició sesión', fecha: 'Hace 5 minutos', ip: '192.168.1.100' },
        { usuario: 'Juan Pérez', accion: 'Registró asistencia', fecha: 'Hace 15 minutos', ip: '192.168.1.101' },
        { usuario: 'María González', accion: 'Actualizó horario', fecha: 'Hace 1 hora', ip: '192.168.1.102' },
        { usuario: 'Carlos López', accion: 'Creó nuevo grupo', fecha: 'Hace 2 horas', ip: '192.168.1.103' }
    ];

    const tbody = document.getElementById('activitiesTableBody');
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td>${activity.usuario}</td>
            <td>${activity.accion}</td>
            <td>${activity.fecha}</td>
            <td>${activity.ip}</td>
        </tr>
    `).join('');
}

function refreshDashboard() {
    location.reload();
}

function exportChart(type) {
    // Implementar exportación de gráficos
    alert('Función de exportación en desarrollo');
}
</script>
