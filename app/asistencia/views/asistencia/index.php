<!-- Control de Asistencia -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-check-circle me-3 text-primary"></i>Control de Asistencia
        </h1>
        <p class="text-muted mb-0">Registra y consulta la asistencia docente</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/asistencia/registrar" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Registrar Asistencia
            </a>
        </div>
        <?php if (in_array($user['rol'], ['administrador', 'coordinador', 'autoridad'])): ?>
        <div class="btn-group">
            <a href="/asistencia/reportes" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>Ver Reportes
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$puedeGestionarAusencias = in_array($user['rol'], ['docente', 'administrador', 'coordinador']);
$tieneIncumplimientos = !empty($incumplimientos ?? []);
$ausencias = $ausencias ?? [];
?>

<?php if ($puedeGestionarAusencias): ?>
    <?php if ($esDocente && $tieneIncumplimientos): ?>
    <div class="alert alert-warning mt-4" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <h6 class="alert-heading mb-1">Tienes ausencias pendientes por justificar</h6>
                <p class="mb-2 small">Justifica tus ausencias para notificar al coordinador. Selecciona cada registro pendiente y adjunta el soporte correspondiente.</p>
                <ul class="mb-0 ps-3 small">
                    <?php foreach ($incumplimientos as $incumplimiento): ?>
                    <li>
                        <?php echo date('d/m/Y', strtotime($incumplimiento['fecha'])); ?> ·
                        <?php echo htmlspecialchars($incumplimiento['materia_nombre'] ?? 'Materia'); ?>
                        (Grupo <?php echo htmlspecialchars($incumplimiento['grupo_numero'] ?? ''); ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mt-5 mb-5" id="ausenciasSection" style="margin-top: 3rem !important;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Ausencias y Justificaciones</h5>
                <small class="text-muted">Gestiona las ausencias reportadas y sus soportes</small>
            </div>
            <div class="d-flex gap-2">
                <?php if (($esDocente && $tieneIncumplimientos) || (!$esDocente)): ?>
                <button type="button" class="btn btn-primary" id="btnNuevaAusencia"
                        data-mode="create">
                    <i class="bi bi-plus-circle me-1"></i>Registrar Justificación
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaAusencias">
                    <thead class="table-light">
                        <tr>
                            <?php if (!$esDocente): ?>
                            <th class="border-0 ps-4">Docente</th>
                            <?php endif; ?>
                            <th class="border-0">Fecha</th>
                            <th class="border-0">Materia / Grupo</th>
                            <th class="border-0">Estado</th>
                            <th class="border-0">Soporte</th>
                            <th class="border-0 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ausencias)): ?>
                        <tr>
                            <td colspan="<?php echo $esDocente ? '5' : '6'; ?>" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox me-2"></i>No hay ausencias registradas
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ausencias as $ausencia): ?>
                        <tr>
                            <?php if (!$esDocente): ?>
                            <td class="ps-4">
                                <div class="fw-semibold"><?php echo htmlspecialchars($ausencia['docente_nombre'] ?? 'N/A'); ?></div>
                            </td>
                            <?php endif; ?>
                            <td><?php echo isset($ausencia['fecha']) ? date('d/m/Y', strtotime($ausencia['fecha'])) : 'N/A'; ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($ausencia['materia_nombre'] ?? 'N/A'); ?></div>
                                <small class="text-muted">Grupo <?php echo htmlspecialchars($ausencia['grupo_numero'] ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <?php $estado = $ausencia['estado'] ?? 'pendiente'; ?>
                                <?php if ($estado === 'aprobado'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aprobado</span>
                                <?php elseif ($estado === 'rechazado'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rechazado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($ausencia['archivo_soporte'])): ?>
                                <a href="/ausencias/download/<?php echo $ausencia['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-paperclip me-1"></i>Descargar
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Sin archivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group" role="group">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-editar-ausencia"
                                            data-id="<?php echo $ausencia['id']; ?>"
                                            data-docente-id="<?php echo $ausencia['docente_id']; ?>"
                                            data-docente-nombre="<?php echo htmlspecialchars($ausencia['docente_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-asistencia-id="<?php echo $ausencia['asistencia_id'] ?? ''; ?>"
                                            data-fecha="<?php echo $ausencia['fecha']; ?>"
                                            data-justificacion="<?php echo htmlspecialchars($ausencia['justificacion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-estado="<?php echo $estado; ?>"
                                            data-archivo="<?php echo $ausencia['archivo_soporte'] ?? ''; ?>">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-eliminar-ausencia ms-2"
                                            data-id="<?php echo $ausencia['id']; ?>">
                                        <i class="bi bi-trash me-1"></i>Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Ausencia -->
    <div class="modal fade" id="ausenciaModal" tabindex="-1" aria-labelledby="ausenciaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ausenciaModalLabel"><i class="bi bi-plus-circle me-2"></i>Registrar justificación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ausenciaForm" method="post" enctype="multipart/form-data" action="/ausencias/store">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        <input type="hidden" name="ausencia_id" id="ausencia_id">
                        <?php if (!$esDocente): ?>
                        <div class="mb-3">
                            <label class="form-label">Docente</label>
                            <input type="text" class="form-control" id="ausencia_docente_display" placeholder="Selecciona un registro" disabled>
                            <input type="hidden" name="docente_id" id="ausencia_docente_id">
                            <small class="text-muted">El docente se asigna automáticamente al seleccionar un registro de asistencia incumplida.</small>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="ausencia_asistencia" class="form-label">Registro de asistencia <span class="text-muted">(Opcional)</span></label>
                            <select class="form-select" name="asistencia_id" id="ausencia_asistencia">
                                <option value="">Selecciona un registro pendiente (opcional)</option>
                                <?php if (!empty($incumplimientos)): ?>
                                    <?php foreach ($incumplimientos as $incumplimiento): ?>
                                    <option value="<?php echo $incumplimiento['id']; ?>"
                                            data-docente-id="<?php echo $incumplimiento['docente_id']; ?>"
                                            data-docente-nombre="<?php echo htmlspecialchars($incumplimiento['docente_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-fecha="<?php echo $incumplimiento['fecha']; ?>">
                                        <?php echo date('d/m/Y', strtotime($incumplimiento['fecha'])); ?> ·
                                        <?php echo htmlspecialchars($incumplimiento['materia_nombre'] ?? 'Materia'); ?>
                                        (Grupo <?php echo htmlspecialchars($incumplimiento['grupo_numero'] ?? ''); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Este campo es obligatorio para justificar ausencias derivadas de incumplimientos registrados.</small>
                        </div>
                        <div class="mb-3">
                            <label for="ausencia_fecha" class="form-label">Fecha de la ausencia <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha" id="ausencia_fecha" required>
                            <small class="text-muted">Si seleccionas un registro de asistencia, la fecha se completará automáticamente. Si no seleccionas un registro, debes especificar la fecha manualmente.</small>
                        </div>
                        <div class="mb-3">
                            <label for="ausencia_justificacion" class="form-label">Justificación <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="justificacion" id="ausencia_justificacion" rows="4" placeholder="Describe el motivo de la ausencia" required></textarea>
                            <small class="text-muted">Este campo es obligatorio.</small>
                        </div>
                        <div class="mb-3">
                            <label for="ausencia_archivo" class="form-label">Soporte (PDF, JPG, PNG)</label>
                            <input type="file" class="form-control" name="archivo_soporte" id="ausencia_archivo" accept=".pdf,.png,.jpg,.jpeg">
                            <div class="form-text">Opcional, máximo 10 MB. Si ya existe un archivo podrás reemplazarlo.</div>
                            <div class="form-text d-none" id="ausenciaArchivoActual"></div>
                        </div>
                        <?php if (!$esDocente): ?>
                        <div class="mb-3">
                            <label for="ausencia_estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="ausencia_estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="estado" id="ausencia_estado" value="pendiente">
                        <?php endif; ?>
                        <div class="alert alert-danger d-none" id="ausenciaError"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="ausenciaSubmitBtn">
                            <i class="bi bi-check-circle me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal eliminar ausencia -->
    <div class="modal fade" id="deleteAusenciaModal" tabindex="-1" aria-labelledby="deleteAusenciaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAusenciaModalLabel"><i class="bi bi-trash me-2"></i>Eliminar ausencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ausenciaDeleteForm" method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                        <p class="mb-0">¿Estás seguro de que deseas eliminar esta ausencia? Esta acción no se puede deshacer.</p>
                        <input type="hidden" name="ausencia_delete_id" id="ausencia_delete_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" id="ausenciaDeleteBtn">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Tarjetas de Estadísticas -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo isset($asistenciasHoy) ? (int)$asistenciasHoy : count($asistencias); ?></div>
            <div class="stat-label">Registros Hoy</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value">
                <?php 
                $presentes = array_filter($asistencias, fn($a) => isset($a['estado']) && $a['estado'] === 'presente');
                echo count($presentes); 
                ?>
            </div>
            <div class="stat-label">Presentes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-value">
                <?php 
                $tardanzas = array_filter($asistencias, fn($a) => isset($a['estado']) && $a['estado'] === 'tardanza');
                echo count($tardanzas); 
                ?>
            </div>
            <div class="stat-label">Tardanzas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-value">
                <?php 
                $total = count($asistencias);
                $presentesYTardanzas = count(array_filter($asistencias, fn($a) => isset($a['estado']) && in_array($a['estado'], ['presente', 'tardanza'])));
                echo $total > 0 ? round(($presentesYTardanzas / $total) * 100, 1) : 0; 
                ?>%
            </div>
            <div class="stat-label">Porcentaje</div>
        </div>
    </div>
</div>

<!-- Tabla de Asistencias -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Registros de Asistencia</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if (!in_array($user['rol'], ['docente'])): ?>
                        <th class="border-0 ps-4">Docente</th>
                        <?php endif; ?>
                        <th class="border-0">Materia</th>
                        <th class="border-0">Grupo</th>
                        <th class="border-0">Aula</th>
                        <th class="border-0">Fecha</th>
                        <th class="border-0">Hora</th>
                        <th class="border-0">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asistencias)): ?>
                    <tr>
                        <td colspan="<?php echo in_array($user['rol'], ['docente']) ? '6' : '7'; ?>" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox me-2"></i>No se encontraron asistencias registradas
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($asistencias as $asistencia): ?>
                    <tr>
                        <?php if (!in_array($user['rol'], ['docente'])): ?>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($asistencia['docente_nombre'] ?? 'N/A'); ?></div>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span class="fw-semibold"><?php echo htmlspecialchars($asistencia['materia_nombre'] ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($asistencia['grupo_numero'] ?? 'N/A'); ?>
                                <?php if (!empty($asistencia['semestre'])): ?>
                                <span class="text-muted">(<?php echo htmlspecialchars($asistencia['semestre']); ?>)</span>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($asistencia['aula_nombre'])): ?>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($asistencia['aula_nombre']); ?>
                                <?php if (!empty($asistencia['aula_codigo'])): ?>
                                (<?php echo htmlspecialchars($asistencia['aula_codigo']); ?>)
                                <?php endif; ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo isset($asistencia['fecha']) ? date('d/m/Y', strtotime($asistencia['fecha'])) : 'N/A'; ?></span>
                        </td>
                        <td>
                            <span class="text-muted">
                                <?php 
                                if (!empty($asistencia['hora_inicio']) && !empty($asistencia['hora_fin'])) {
                                    echo htmlspecialchars(substr($asistencia['hora_inicio'], 0, 5) . ' - ' . substr($asistencia['hora_fin'], 0, 5));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $estado = $asistencia['estado'] ?? 'ausente';
                            if ($estado === 'presente'): 
                            ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Presente
                                </span>
                            <?php elseif ($estado === 'tardanza'): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock-history me-1"></i>Tardanza
                                </span>
                            <?php elseif ($estado === 'asistido_tarde'): ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Asistido Tarde
                                </span>
                            <?php elseif ($estado === 'incumplido'): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>Incumplido
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-question-circle me-1"></i><?php echo htmlspecialchars(ucfirst($estado)); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
