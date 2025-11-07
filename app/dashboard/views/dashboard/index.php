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
                <!-- Gráfico de Barras - Reporte Porcentual -->
                <div class="mb-4">
                    <h6 class="mb-3 text-muted">Distribución de Asistencias (Últimos 30 días)</h6>
                    <div class="chart-container" style="height: 250px; width: 100%; position: relative;">
                        <canvas id="asistenciaBarrasChart" style="display: block; width: 100% !important; height: 100% !important;"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico de Línea - Evolución Diaria -->
                <div>
                    <h6 class="mb-3 text-muted">Evolución Diaria de Asistencias</h6>
                    <div class="chart-container" style="height: 250px; width: 100%; position: relative;">
                        <canvas id="asistenciaChart" style="display: block; width: 100% !important; height: 100% !important;"></canvas>
                    </div>
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

<?php if ($user['rol'] === 'docente' && !empty($clases)): ?>
<!-- Clases Asignadas -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1 fw-bold">
                        <i class="bi bi-book me-2"></i>Mis Clases Asignadas
                    </h5>
                    <p class="text-muted mb-0 small">Grupos y materias que impartes</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">Materia</th>
                                <th class="border-0">Grupo</th>
                                <th class="border-0">Semestre</th>
                                <th class="border-0">Turno</th>
                                <th class="border-0">Horarios</th>
                                <th class="border-0">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clases as $clase): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($clase['materia_nombre']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($clase['materia_codigo']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($clase['grupo_numero']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($clase['semestre'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($clase['turno'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $clase['total_horarios']; ?> horario(s)</span>
                                </td>
                                <td>
                                    <a href="/asistencia/registrar?grupo_id=<?php echo $clase['grupo_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check-circle me-1"></i>Registrar Asistencia
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($user['rol'] === 'docente' && !empty($horarios)): ?>
<!-- Horarios Semanales -->
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1 fw-bold">
                        <i class="bi bi-calendar-week me-2"></i>Mis Horarios Semanales
                    </h5>
                    <p class="text-muted mb-0 small">Horarios de clases y disponibilidad para marcar asistencia</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">Día</th>
                                <th class="border-0">Hora</th>
                                <th class="border-0">Materia</th>
                                <th class="border-0">Grupo</th>
                                <th class="border-0">Aula</th>
                                <th class="border-0">Estado</th>
                                <th class="border-0">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horarios as $horario): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($horario['dia_nombre']); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock text-muted me-2"></i>
                                        <span><?php echo htmlspecialchars($horario['hora_inicio']); ?> - <?php echo htmlspecialchars($horario['hora_fin']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($horario['materia_nombre']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($horario['materia_codigo']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($horario['grupo_numero']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($horario['aula_nombre'])): ?>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($horario['aula_nombre']); ?>
                                        (<?php echo htmlspecialchars($horario['aula_codigo']); ?>)
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($horario['asistencia_registrada_hoy']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Asistencia Registrada
                                    </span>
                                    <?php elseif ($horario['puede_marcar']): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i>Disponible para Marcar
                                    </span>
                                    <?php else: ?>
                                    <?php
                                    // Determinar estado basado en la hora actual
                                    $horaActual = new DateTime();
                                    $diaActual = (int)date('N');
                                    $estadoHorario = 'no_disponible';
                                    
                                    if ($horario['dia_semana'] == $diaActual) {
                                        $horaInicioStr = $horario['hora_inicio'];
                                        if (strlen($horaInicioStr) == 5) $horaInicioStr .= ':00';
                                        $horaFinStr = $horario['hora_fin'];
                                        if (strlen($horaFinStr) == 5) $horaFinStr .= ':00';
                                        
                                        $fechaActual = $horaActual->format('Y-m-d');
                                        $horaInicio = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaInicioStr);
                                        $horaFin = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaFinStr);
                                        
                                        if ($horaInicio && $horaFin) {
                                            $ventanaInicio = clone $horaInicio;
                                            $ventanaInicio->modify('-20 minutes');
                                            $ventanaFin = clone $horaInicio;
                                            $ventanaFin->modify('+10 minutes');
                                            
                                            if ($horaActual < $ventanaInicio) {
                                                $estadoHorario = 'pendiente';
                                            } elseif ($horaActual > $ventanaFin && $horaActual <= $horaFin) {
                                                $estadoHorario = 'fuera_ventana';
                                            } elseif ($horaActual > $horaFin) {
                                                $estadoHorario = 'incumplido';
                                            }
                                        }
                                    }
                                    ?>
                                    <?php if ($estadoHorario === 'pendiente'): ?>
                                    <span class="badge bg-info">
                                        <i class="bi bi-clock-history me-1"></i>Pendiente
                                    </span>
                                    <?php elseif ($estadoHorario === 'fuera_ventana'): ?>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Fuera de Ventana
                                    </span>
                                    <?php elseif ($estadoHorario === 'incumplido'): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>Incumplido
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>No Disponible
                                    </span>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($horario['puede_marcar'] && !$horario['asistencia_registrada_hoy']): ?>
                                    <button class="btn btn-sm btn-primary" onclick="marcarAsistencia(<?php echo $horario['id']; ?>)">
                                        <i class="bi bi-check-circle me-1"></i>Marcar Asistencia
                                    </button>
                                    <?php elseif ($estadoHorario === 'fuera_ventana' && !$horario['asistencia_registrada_hoy']): ?>
                                    <button class="btn btn-sm btn-warning" onclick="marcarAsistencia(<?php echo $horario['id']; ?>)">
                                        <i class="bi bi-clock-history me-1"></i>Marcar como Tarde
                                    </button>
                                    <?php elseif ($horario['asistencia_registrada_hoy']): ?>
                                    <span class="text-muted small">
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>Ya registrada
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted small">Fuera de horario</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Los gráficos se cargan desde app.js

function refreshDashboard() {
    location.reload();
}

function exportActivities() {
    alert('Función de exportación en desarrollo');
}

function exportChart(type) {
    // Implementar exportación de gráficos
    alert('Función de exportación en desarrollo');
}

<?php if ($user['rol'] === 'docente'): ?>
// Función para marcar asistencia desde el dashboard
function marcarAsistencia(horarioId) {
    if (!confirm('¿Deseas marcar tu asistencia para este horario?')) {
        return;
    }
    
    fetch('/asistencia/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'horario_id=' + horarioId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('¡Asistencia registrada exitosamente!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo registrar la asistencia'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al registrar la asistencia');
    });
}
<?php endif; ?>
</script>
