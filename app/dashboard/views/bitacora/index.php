<style>
/* Estilos para asegurar que el backdrop se elimine correctamente */
.modal-backdrop {
    z-index: 1050 !important;
}

.modal-backdrop.fade {
    opacity: 0.5;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Asegurar que el modal aparezca por encima de todo */
.modal {
    z-index: 1055 !important;
}

.modal.show {
    z-index: 1055 !important;
}

.modal-dialog {
    z-index: 1056 !important;
}

/* Asegurar que el body no quede bloqueado */
body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}

body:not(.modal-open) {
    overflow: auto !important;
    padding-right: 0 !important;
}

/* Limpiar backdrop residual */
.modal-backdrop:not(.show) {
    display: none !important;
}

/* Asegurar que los modales cerrados no tengan backdrop */
.modal:not(.show) ~ .modal-backdrop {
    display: none !important;
}

/* Hacer la tabla estática con altura fija y scroll interno */
.bitacora-table-container {
    max-height: 600px;
    overflow-y: auto;
    overflow-x: auto;
    position: relative !important;
    width: 100% !important;
    z-index: 1 !important;
    /* Bloquear completamente cualquier movimiento */
    transform: none !important;
    will-change: auto !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

.bitacora-table-container table {
    width: 100%;
    margin-bottom: 0;
    position: relative !important;
    transform: none !important;
}

.bitacora-table-container thead {
    position: sticky !important;
    top: 0 !important;
    z-index: 5 !important;
    background-color: #f8f9fa !important;
}

.bitacora-table-container thead th {
    background-color: #f8f9fa !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 6 !important;
    border-bottom: 2px solid #dee2e6 !important;
}

/* Asegurar que el contenedor de la tabla no se mueva */
.card-body {
    position: relative !important;
    overflow: hidden !important;
    transform: none !important;
    will-change: auto !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
}

/* Prevenir que la tabla se mueva cuando se abre el modal */
body.modal-open .bitacora-table-container,
body.modal-open .card,
body.modal-open .card-body,
body.modal-open .bitacora-table-container table,
body.modal-open .bitacora-table-container thead {
    position: relative !important;
    transform: none !important;
    will-change: auto !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Asegurar que el card no se mueva */
.card {
    position: relative !important;
    z-index: 1 !important;
    transform: none !important;
    will-change: auto !important;
    top: 0 !important;
    left: 0 !important;
}

/* Prevenir que cualquier contenedor padre se mueva */
body.modal-open .card,
body.modal-open .card-body,
body.modal-open .container-fluid,
body.modal-open .row,
body.modal-open .col-md-12 {
    position: relative !important;
    transform: none !important;
    will-change: auto !important;
}

/* Asegurar que el contenedor principal no se mueva */
.container-fluid,
.main-content {
    position: relative !important;
    z-index: 1 !important;
    transform: none !important;
}

/* Prevenir que el body cause movimiento */
body.modal-open {
    padding-right: 0 !important;
    overflow: hidden !important;
    position: relative !important;
}

body.modal-open * {
    /* No aplicar transformaciones a elementos dentro de la tabla */
}

body.modal-open .bitacora-table-container,
body.modal-open .bitacora-table-container * {
    transform: none !important;
    will-change: auto !important;
}
</style>

<!-- Bitácora de Actividades -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-journal-text me-3 text-primary"></i>Bitácora de Actividades
        </h1>
        <p class="text-muted mb-0">Registro de todas las acciones del sistema</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
            </button>
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i>Exportar
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('csv', null); return false;">
                    <i class="bi bi-file-earmark-text me-2"></i>CSV - Toda la información
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('xlsx', null); return false;">
                    <i class="bi bi-file-earmark-excel me-2"></i>Excel - Toda la información
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('csv', currentLoggedUserId); return false;">
                    <i class="bi bi-file-earmark-text me-2"></i>CSV - Usuario actual
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('xlsx', currentLoggedUserId); return false;">
                    <i class="bi bi-file-earmark-excel me-2"></i>Excel - Usuario actual
                </a></li>
                <?php if (!empty($filtros['usuario_id'])): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('csv', <?php echo $filtros['usuario_id']; ?>); return false;">
                    <i class="bi bi-file-earmark-text me-2"></i>CSV - Usuario filtrado
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportarBitacora('xlsx', <?php echo $filtros['usuario_id']; ?>); return false;">
                    <i class="bi bi-file-earmark-excel me-2"></i>Excel - Usuario filtrado
                </a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/bitacora" id="filtrosForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Usuario</label>
                            <select name="usuario_id" class="form-select" id="filtroUsuario">
                            <option value="">Todos los usuarios</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id']; ?>" <?php echo ($filtros['usuario_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Acción</label>
                            <select name="accion" class="form-select" id="filtroAccion">
                            <option value="">Todas las acciones</option>
                                <?php foreach ($acciones as $accion): ?>
                                <option value="<?php echo htmlspecialchars($accion); ?>" <?php echo ($filtros['accion'] == $accion) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($accion); ?>
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrar por Tabla</label>
                            <select name="tabla" class="form-select" id="filtroTabla">
                            <option value="">Todas las tablas</option>
                                <?php foreach ($tablas as $tabla): ?>
                                <option value="<?php echo htmlspecialchars($tabla); ?>" <?php echo ($filtros['tabla'] == $tabla) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tabla); ?>
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                            <div class="input-group">
                                <input type="text" name="busqueda" class="form-control" placeholder="Buscar en bitácora..." value="<?php echo htmlspecialchars($filtros['busqueda'] ?? ''); ?>" id="busquedaInput">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                            </button>
                            <a href="/bitacora" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                            </a>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Bitácora -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-check me-2"></i>
            Registro Completo
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive bitacora-table-container">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 ps-4">ID</th>
                        <th class="border-0">Usuario</th>
                        <th class="border-0">Acción</th>
                        <th class="border-0">Tabla/Recurso</th>
                        <th class="border-0">Fecha</th>
                        <th class="border-0">IP</th>
                        <th class="border-0">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $activitiesList = is_array($activities) ? ($activities['activities'] ?? $activities) : $activities;
                    $totalActivities = is_array($activities) && isset($activities['total']) ? $activities['total'] : count($activitiesList);
                    $currentPage = is_array($activities) && isset($activities['page']) ? $activities['page'] : 1;
                    $limit = is_array($activities) && isset($activities['limit']) ? $activities['limit'] : 50;
                    $totalPages = ceil($totalActivities / $limit);
                    ?>
                    <?php if (empty($activitiesList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No hay actividades registradas</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($activitiesList as $activity): ?>
                    <tr>
                        <td class="ps-4">
                            <code>#<?php echo $activity['id'] ?? 'N/A'; ?></code>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($activity['nombre_usuario'] ?? 'Sistema'); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($activity['accion'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($activity['tabla_afectada'] ?? 'N/A'); ?>
                            </span>
                            <?php if (!empty($activity['registro_id'])): ?>
                            <small class="text-muted d-block">ID: <?php echo $activity['registro_id']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock text-muted me-2"></i>
                                <span><?php echo $activity['created_at'] ?? 'N/A'; ?></span>
                            </div>
                        </td>
                        <td>
                            <code class="text-muted small"><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></code>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="openModal<?php echo $activity['id']; ?>()">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            <!-- Modal de Detalles -->
                            <div class="modal fade" id="detailsModal<?php echo $activity['id']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $activity['id']; ?>" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 1055 !important;">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="detailsModalLabel<?php echo $activity['id']; ?>">Detalles de Actividad #<?php echo $activity['id']; ?></h5>
                                            <button type="button" class="btn-close" onclick="closeModal<?php echo $activity['id']; ?>()" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row">
                                                <dt class="col-sm-4">Usuario:</dt>
                                                <dd class="col-sm-8"><?php echo htmlspecialchars($activity['nombre_usuario'] ?? 'Sistema'); ?></dd>
                                                
                                                <dt class="col-sm-4">Acción:</dt>
                                                <dd class="col-sm-8"><span class="badge bg-info"><?php echo htmlspecialchars($activity['accion'] ?? 'N/A'); ?></span></dd>
                                                
                                                <dt class="col-sm-4">Tabla:</dt>
                                                <dd class="col-sm-8"><?php echo htmlspecialchars($activity['tabla_afectada'] ?? 'N/A'); ?></dd>
                                                
                                                <dt class="col-sm-4">Registro ID:</dt>
                                                <dd class="col-sm-8"><?php echo $activity['registro_id'] ?? 'N/A'; ?></dd>
                                                
                                                <dt class="col-sm-4">Fecha:</dt>
                                                <dd class="col-sm-8"><?php echo $activity['created_at'] ?? 'N/A'; ?></dd>
                                                
                                                <dt class="col-sm-4">IP:</dt>
                                                <dd class="col-sm-8"><code><?php echo htmlspecialchars($activity['ip_address'] ?? 'N/A'); ?></code></dd>
                                                
                                                <?php if (!empty($activity['user_agent'])): ?>
                                                <dt class="col-sm-4">Navegador:</dt>
                                                <dd class="col-sm-8"><small class="text-muted"><?php echo htmlspecialchars($activity['user_agent']); ?></small></dd>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($activity['datos_anteriores'])): ?>
                                                <dt class="col-sm-4">Datos Anteriores:</dt>
                                                <dd class="col-sm-8">
                                                    <pre class="bg-light p-2 rounded small"><?php echo htmlspecialchars(json_encode(json_decode($activity['datos_anteriores']), JSON_PRETTY_PRINT)); ?></pre>
                                                </dd>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($activity['datos_nuevos'])): ?>
                                                <dt class="col-sm-4">Datos Nuevos:</dt>
                                                <dd class="col-sm-8">
                                                    <pre class="bg-light p-2 rounded small"><?php echo htmlspecialchars(json_encode(json_decode($activity['datos_nuevos']), JSON_PRETTY_PRINT)); ?></pre>
                                                </dd>
                                                <?php endif; ?>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" onclick="closeModal<?php echo $activity['id']; ?>()">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-transparent">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Mostrando <?php echo count($activitiesList); ?> de <?php echo $totalActivities; ?> registros
                <?php if ($totalPages > 1): ?>
                | Página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>
                <?php endif; ?>
            </small>
            <div class="btn-group btn-group-sm">
                <?php 
                // Construir query string manteniendo filtros
                $queryParams = [];
                if (!empty($filtros['usuario_id'])) $queryParams['usuario_id'] = $filtros['usuario_id'];
                if (!empty($filtros['accion'])) $queryParams['accion'] = $filtros['accion'];
                if (!empty($filtros['tabla'])) $queryParams['tabla'] = $filtros['tabla'];
                if (!empty($filtros['busqueda'])) $queryParams['busqueda'] = $filtros['busqueda'];
                $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                ?>
                <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?><?php echo $queryString; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?><?php echo $queryString; ?>" class="btn btn-outline-primary">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportarBitacora(formato, usuarioId) {
    let url = '/bitacora/exportar?formato=' + formato;
    if (usuarioId) {
        url += '&usuario_id=' + usuarioId;
    }
    // Agregar filtros actuales
    const params = new URLSearchParams(window.location.search);
    if (params.get('usuario_id')) {
        url += '&usuario_id=' + params.get('usuario_id');
    }
    if (params.get('accion')) {
        url += '&accion=' + params.get('accion');
    }
    if (params.get('tabla')) {
        url += '&tabla=' + params.get('tabla');
    }
    if (params.get('busqueda')) {
        url += '&busqueda=' + params.get('busqueda');
    }
    window.location.href = url;
}

function getCurrentUserId() {
    // Obtener el ID del usuario actual desde los filtros
    const params = new URLSearchParams(window.location.search);
    return params.get('usuario_id') || null;
}

// Obtener el ID del usuario logueado desde el servidor (si está disponible)
const currentLoggedUserId = <?php echo $user['id'] ?? 'null'; ?>;

// Aplicar filtros automáticamente al cambiar los selects
document.getElementById('filtroUsuario')?.addEventListener('change', function() {
    document.getElementById('filtrosForm').submit();
});

document.getElementById('filtroAccion')?.addEventListener('change', function() {
    document.getElementById('filtrosForm').submit();
});

document.getElementById('filtroTabla')?.addEventListener('change', function() {
    document.getElementById('filtrosForm').submit();
});

// Función para limpiar backdrop de forma agresiva
function cleanupModalBackdrop() {
    // Remover todos los backdrops inmediatamente
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(function(backdrop) {
        backdrop.remove();
    });
    
    // Remover clase modal-open del body
    document.body.classList.remove('modal-open');
    
    // Restaurar todos los estilos del body de forma forzada
    document.body.style.paddingRight = '';
    document.body.style.overflow = '';
    document.body.style.overflowX = '';
    document.body.style.overflowY = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    
    // Asegurar que todos los modales estén ocultos
    document.querySelectorAll('.modal').forEach(function(modal) {
        if (modal.classList.contains('show')) {
            modal.classList.remove('show');
        }
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.style.display = 'none';
        modal.style.paddingRight = '';
    });
    
    // Forzar remoción de cualquier backdrop que pueda quedar
    const remainingBackdrops = document.querySelectorAll('.modal-backdrop');
    remainingBackdrops.forEach(function(backdrop) {
        backdrop.parentNode?.removeChild(backdrop);
    });
    
    // Verificar si hay clase modal-open y removerla
    if (document.body.classList.contains('modal-open')) {
        document.body.classList.remove('modal-open');
    }
    
    // Asegurar que el contenedor de la tabla mantenga su posición
    const tableContainer = document.querySelector('.bitacora-table-container');
    const card = document.querySelector('.card');
    const cardBody = document.querySelector('.card-body');
    const table = tableContainer ? tableContainer.querySelector('table') : null;
    const thead = table ? table.querySelector('thead') : null;
    
    const elements = [tableContainer, card, cardBody, table, thead].filter(el => el !== null);
    
    elements.forEach(function(element) {
        if (element) {
            element.style.setProperty('position', 'relative', 'important');
            element.style.setProperty('top', '0', 'important');
            element.style.setProperty('left', '0', 'important');
            element.style.setProperty('right', '0', 'important');
            element.style.setProperty('bottom', '0', 'important');
            element.style.setProperty('transform', 'none', 'important');
            element.style.setProperty('will-change', 'auto', 'important');
            element.style.setProperty('margin', '0', 'important');
            element.style.setProperty('transition', 'none', 'important');
        }
    });
}

// Función para abrir modal específico
<?php foreach ($activitiesList as $activity): ?>
function openModal<?php echo $activity['id']; ?>() {
    const modalElement = document.getElementById('detailsModal<?php echo $activity['id']; ?>');
    if (modalElement) {
        // FORZAR posición de la tabla ANTES de que Bootstrap haga cualquier cambio
        const tableContainer = document.querySelector('.bitacora-table-container');
        const card = document.querySelector('.card');
        const cardBody = document.querySelector('.card-body');
        const table = tableContainer ? tableContainer.querySelector('table') : null;
        const thead = table ? table.querySelector('thead') : null;
        
        // Aplicar estilos forzados inmediatamente
        const elements = [tableContainer, card, cardBody, table, thead].filter(el => el !== null);
        
        elements.forEach(function(element) {
            if (element) {
                // Forzar posición antes de que Bootstrap pueda cambiarla
                element.style.setProperty('position', 'relative', 'important');
                element.style.setProperty('top', '0', 'important');
                element.style.setProperty('left', '0', 'important');
                element.style.setProperty('right', '0', 'important');
                element.style.setProperty('bottom', '0', 'important');
                element.style.setProperty('transform', 'none', 'important');
                element.style.setProperty('will-change', 'auto', 'important');
                element.style.setProperty('margin', '0', 'important');
                element.style.setProperty('transition', 'none', 'important');
            }
        });
        
        // Limpiar cualquier backdrop residual primero
        cleanupModalBackdrop();
        
        // Crear instancia del modal
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        
        // Asegurar z-index alto del modal
        modalElement.style.setProperty('z-index', '1055', 'important');
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.setProperty('z-index', '1056', 'important');
        }
        
        // Limpiar backdrop cuando se oculta
        modalElement.addEventListener('hidden.bs.modal', function() {
            cleanupModalBackdrop();
            // Reforzar posición después de cerrar
            maintainTablePosition();
        }, { once: true });
        
        // Mostrar modal
        modal.show();
        
        // Reforzar posición después de que Bootstrap intente abrir el modal
        setTimeout(function() {
            maintainTablePosition();
        }, 10);
        
        setTimeout(function() {
            maintainTablePosition();
        }, 50);
        
        setTimeout(function() {
            maintainTablePosition();
        }, 100);
    }
}

function closeModal<?php echo $activity['id']; ?>() {
    const modalElement = document.getElementById('detailsModal<?php echo $activity['id']; ?>');
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
        // Limpiar backdrop inmediatamente
        setTimeout(function() {
            cleanupModalBackdrop();
        }, 150);
    }
}
<?php endforeach; ?>

// Asegurar que los modales se cierren correctamente
document.addEventListener('DOMContentLoaded', function() {
    // Función para mantener la tabla estática - más agresiva
    function maintainTablePosition() {
        const tableContainer = document.querySelector('.bitacora-table-container');
        const card = document.querySelector('.card');
        const cardBody = document.querySelector('.card-body');
        const table = tableContainer ? tableContainer.querySelector('table') : null;
        const thead = table ? table.querySelector('thead') : null;
        
        // Aplicar estilos forzados a todos los elementos con !important
        const elements = [tableContainer, card, cardBody, table, thead].filter(el => el !== null);
        
        elements.forEach(function(element) {
            if (element) {
                element.style.setProperty('position', 'relative', 'important');
                element.style.setProperty('top', '0', 'important');
                element.style.setProperty('left', '0', 'important');
                element.style.setProperty('right', '0', 'important');
                element.style.setProperty('bottom', '0', 'important');
                element.style.setProperty('transform', 'none', 'important');
                element.style.setProperty('will-change', 'auto', 'important');
                element.style.setProperty('margin', '0', 'important');
                element.style.setProperty('transition', 'none', 'important');
            }
        });
        
        // También aplicar a todos los elementos hijos de la tabla
        if (tableContainer) {
            const allChildren = tableContainer.querySelectorAll('*');
            allChildren.forEach(function(child) {
                if (child.tagName !== 'TH' || !child.classList.contains('sticky')) {
                    // No aplicar a th que deben ser sticky
                    child.style.setProperty('transform', 'none', 'important');
                    child.style.setProperty('will-change', 'auto', 'important');
                }
            });
        }
    }
    
    // Observar cuando se abre un modal
    document.querySelectorAll('.modal').forEach(function(modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            maintainTablePosition();
        });
        
        modalElement.addEventListener('shown.bs.modal', function() {
            maintainTablePosition();
            // Asegurar z-index alto
            modalElement.style.zIndex = '1055';
            const modalDialog = modalElement.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.zIndex = '1056';
            }
        });
        
        modalElement.addEventListener('hidden.bs.modal', function() {
            maintainTablePosition();
            cleanupModalBackdrop();
        });
    });
    
    // Observar cambios en el DOM para mantener la tabla estática
    const tableObserver = new MutationObserver(function(mutations) {
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length > 0) {
            // Si hay modales abiertos, forzar que la tabla no se mueva
            maintainTablePosition();
        }
    });
    
    // Observar cambios en el body y en la tabla
    const bodyElement = document.body;
    const tableContainer = document.querySelector('.bitacora-table-container');
    
    if (bodyElement) {
        tableObserver.observe(bodyElement, {
            attributes: true,
            attributeFilter: ['class', 'style'],
            childList: true,
            subtree: true
        });
    }
    
    if (tableContainer) {
        tableObserver.observe(tableContainer, {
            attributes: true,
            attributeFilter: ['class', 'style'],
            childList: true,
            subtree: true
        });
    }
    
    // Limpiar backdrop y mantener posición periódicamente
    setInterval(function() {
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length === 0) {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 0) {
                cleanupModalBackdrop();
            }
        } else {
            // Si hay modales abiertos, mantener la tabla estática
            maintainTablePosition();
            
            // También prevenir que el body cause movimiento
            if (document.body.classList.contains('modal-open')) {
                document.body.style.paddingRight = '0';
                document.body.style.overflow = 'hidden';
            }
        }
    }, 100);
    
    // Ejecutar inmediatamente al cargar
    maintainTablePosition();
    
    // Limpiar backdrop al presionar ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            setTimeout(function() {
                cleanupModalBackdrop();
            }, 150);
        }
    });
    
    // Limpiar backdrop cuando se hace clic fuera del modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(function(modal) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
            setTimeout(function() {
                cleanupModalBackdrop();
            }, 150);
        }
    });
    
    // Observar cambios en el DOM para detectar backdrops residuales
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                        // Verificar si hay modales abiertos
                        const modals = document.querySelectorAll('.modal.show');
                        if (modals.length === 0) {
                            // Si no hay modales abiertos pero hay backdrop, limpiarlo
                            setTimeout(function() {
                                cleanupModalBackdrop();
                            }, 100);
                        }
                    }
                });
            }
        });
    });
    
    // Observar cambios en el body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>
