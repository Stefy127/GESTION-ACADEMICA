<?php
// Obtener mensajes de la sesión
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

// Limpiar mensajes de la sesión
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors'], $_SESSION['old_input']);

// Función para obtener valor anterior o actual
function oldValue($field) {
    global $oldInput, $user;
    return $oldInput[$field] ?? $user[$field] ?? '';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Mi Perfil</h1>
                    <p class="text-muted mb-0">Gestiona tu información personal y configuración de cuenta</p>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Información Personal -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-circle me-2"></i>Información Personal
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nombre</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($user['nombre']); ?></p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Apellido</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($user['apellido']); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Email</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Teléfono</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($user['telefono'] ?? 'No especificado'); ?></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Rol</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-secondary fs-6"><?php echo ucfirst($user['rol']); ?></span>
                                    </p>
                                </div>
                                
                                <?php if (isset($user['ci']) && !empty($user['ci'])): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Cédula de Identidad</label>
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($user['ci']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Campos adicionales para docentes -->
                            <?php if ($user['rol'] === 'docente' && $docenteInfo): ?>
                                <hr class="my-4">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-mortarboard me-2"></i>Información Académica
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Especialidad</label>
                                        <p class="form-control-plaintext"><?php echo htmlspecialchars($docenteInfo['especialidad'] ?? 'No especificada'); ?></p>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Departamento</label>
                                        <p class="form-control-plaintext"><?php echo htmlspecialchars($docenteInfo['departamento'] ?? 'No especificado'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Teléfono de Oficina</label>
                                        <p class="form-control-plaintext"><?php echo htmlspecialchars($docenteInfo['telefono_oficina'] ?? 'No especificado'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Información de la Cuenta -->
                <div class="col-lg-4">
                    <!-- Avatar y datos básicos -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <?php echo strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1)); ?>
                                </div>
                            </div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></h5>
                            <p class="text-muted mb-2"><?php echo ucfirst($user['rol']); ?></p>
                            <small class="text-muted">
                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($user['email']); ?>
                            </small>
                        </div>
                    </div>

                    <!-- Información de la cuenta -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2"></i>Información de la Cuenta
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Usuario desde:</span>
                                    <span><?php 
                                        $fechaCreacion = new DateTime($user['created_at']);
                                        echo $fechaCreacion->format('d/m/Y'); 
                                    ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Última actualización:</span>
                                    <span><?php 
                                        $fechaActualizacion = new DateTime($user['updated_at']);
                                        echo $fechaActualizacion->format('d/m/Y H:i'); 
                                    ?></span>
                                </div>
                                
                                <?php if (isset($user['ultimo_acceso']) && !empty($user['ultimo_acceso'])): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Último acceso:</span>
                                    <span><?php 
                                        $ultimoAcceso = new DateTime($user['ultimo_acceso']);
                                        echo $ultimoAcceso->format('d/m/Y H:i'); 
                                    ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Estado:</span>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

