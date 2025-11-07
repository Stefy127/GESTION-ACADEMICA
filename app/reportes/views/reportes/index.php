<!-- Reportes del Sistema -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4">
    <div>
        <h1 class="h2 mb-1 fw-bold text-gray-800">
            <i class="bi bi-graph-up me-3 text-primary"></i>Reportes del Sistema
        </h1>
        <p class="text-muted mb-0">Genera y consulta reportes del sistema</p>
    </div>
</div>

<!-- Tarjetas de Reportes -->
<div class="row g-4">
    <?php foreach ($reportes as $reporte): ?>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="<?php echo $reporte['icono']; ?> text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title"><?php echo htmlspecialchars($reporte['nombre']); ?></h5>
                <p class="card-text text-muted"><?php echo htmlspecialchars($reporte['descripcion']); ?></p>
                <a href="/reportes/<?php echo $reporte['ruta']; ?>" class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i>Ver Reporte
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros de Reportes -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros de Reportes</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Período</label>
                <select class="form-select">
                    <option>Último mes</option>
                    <option>Últimos 3 meses</option>
                    <option>Último año</option>
                    <option>Personalizado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Formato</label>
                <select class="form-select">
                    <option>Pantalla</option>
                    <option>PDF</option>
                    <option>Excel</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Docente</label>
                <select class="form-select">
                    <option>Todos</option>
                    <option>Juan Pérez</option>
                    <option>María González</option>
                    <option>Carlos López</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
