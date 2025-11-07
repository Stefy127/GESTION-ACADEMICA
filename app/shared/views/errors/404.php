<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? '404 - Página no encontrada'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <div class="error-page">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                    <h1 class="display-1 fw-bold text-gray-800 mt-4">404</h1>
                    <h2 class="h4 mb-3 text-gray-600"><?php echo $title ?? 'Página no encontrada'; ?></h2>
                    <p class="text-muted mb-4">La página que buscas no existe o ha sido movida.</p>
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

