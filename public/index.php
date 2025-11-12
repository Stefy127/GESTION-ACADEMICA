<?php
/**
 * Archivo principal de la aplicación
 */

// Inicializar sesión ANTES de cualquier output o require
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Cargar configuración
require_once __DIR__ . '/../config/app.php';

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar zona horaria
date_default_timezone_set('America/La_Paz'); // Bolivia UTC-4

// Cargar clases core (infraestructura básica)
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Middleware.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/helpers/ActivityLogger.php';
require_once __DIR__ . '/../app/core/Router.php';

// Cargar modelos específicos
require_once __DIR__ . '/../app/gestion_usuarios/models/Auth.php';

// Crear instancia del router
$router = new Router();

// Definir rutas
$router->get('/', 'Home@index');
$router->get('/about', 'Home@about');

// Rutas de autenticación
$router->get('/login', 'Auth@login');
$router->post('/auth/login', 'Auth@processLogin');
$router->post('/auth/change-password', 'Auth@changePassword');
$router->get('/logout', 'Auth@logout');
$router->get('/forgot-password', 'Auth@forgotPassword');
$router->post('/auth/forgot-password', 'Auth@processForgotPassword');
$router->get('/reset-password', 'Auth@resetPassword');
$router->post('/auth/reset-password', 'Auth@processResetPassword');

// Rutas del dashboard
$router->get('/dashboard', 'Dashboard@index');
$router->get('/dashboard/chart-data', 'Dashboard@getChartData');

// Rutas de bitácora
$router->get('/bitacora', 'Bitacora@index');
$router->get('/bitacora/exportar', 'Bitacora@exportar');

// Rutas de gestión de usuarios
$router->get('/usuarios', 'Usuarios@index');
$router->get('/usuarios/create', 'Usuarios@create');
$router->post('/usuarios/store', 'Usuarios@store');
$router->get('/usuarios/edit/{id}', 'Usuarios@edit');
$router->post('/usuarios/update/{id}', 'Usuarios@update');
$router->post('/usuarios/delete/{id}', 'Usuarios@delete');

// Rutas de perfil de usuario
$router->get('/profile', 'Profile@index');

// Rutas de gestión de docentes
$router->get('/docentes', 'Docentes@index');
$router->get('/docentes/edit/{id}', 'Docentes@edit');
$router->post('/docentes/update/{id}', 'Docentes@update');
$router->post('/docentes/delete/{id}', 'Docentes@delete');

// Rutas de gestión de materias
$router->get('/materias', 'Materias@index');
$router->get('/materias/create', 'Materias@create');
$router->post('/materias/store', 'Materias@store');
$router->get('/materias/edit/{id}', 'Materias@edit');
$router->post('/materias/update/{id}', 'Materias@update');
$router->post('/materias/delete/{id}', 'Materias@delete');
$router->get('/materias/grupos/{id}', 'Materias@grupos');

// Rutas de gestión de grupos
$router->get('/grupos', 'Grupos@index');
$router->get('/grupos/create', 'Grupos@create');
$router->post('/grupos/store', 'Grupos@store');
$router->get('/grupos/edit/{id}', 'Grupos@edit');
$router->post('/grupos/update/{id}', 'Grupos@update');
$router->post('/grupos/delete/{id}', 'Grupos@delete');
$router->get('/grupos/horarios/{id}', 'Grupos@horarios');

// Rutas de gestión de aulas
$router->get('/aulas', 'Aulas@index');
$router->get('/aulas/create', 'Aulas@create');
$router->post('/aulas/store', 'Aulas@store');
$router->get('/aulas/edit/{id}', 'Aulas@edit');
$router->post('/aulas/update/{id}', 'Aulas@update');
$router->post('/aulas/delete/{id}', 'Aulas@delete');
$router->get('/aulas/horarios/{id}', 'Aulas@horarios');

// Rutas de gestión de horarios
$router->get('/horarios', 'Horarios@index');
$router->get('/horarios/create', 'Horarios@create');
$router->post('/horarios/store', 'Horarios@store');
$router->get('/horarios/edit/{id}', 'Horarios@edit');
$router->post('/horarios/update/{id}', 'Horarios@update');
$router->post('/horarios/delete/{id}', 'Horarios@delete');

// Rutas de asistencia
$router->get('/asistencia', 'Asistencia@index');
$router->get('/asistencia/registrar', 'Asistencia@registrar');
$router->post('/asistencia/registrar', 'Asistencia@processRegistro');
$router->get('/asistencia/reportes', 'Asistencia@reportes');

// Rutas de ausencias
$router->post('/ausencias/store', 'Ausencias@store');
$router->post('/ausencias/update/{id}', 'Ausencias@update');
$router->post('/ausencias/delete/{id}', 'Ausencias@delete');
$router->get('/ausencias/download/{id}', 'Ausencias@download');

// Rutas de reportes
$router->get('/reportes', 'Reportes@index');
$router->get('/reportes/asistencia', 'Reportes@asistencia');
$router->get('/reportes/horarios', 'Reportes@horarios');
$router->get('/reportes/docentes', 'Reportes@docentes');
$router->get('/reportes/aulas', 'Reportes@aulas');
$router->get('/reportes/exportar/asistencia', 'Reportes@exportarAsistencia');
$router->get('/reportes/exportar/horarios', 'Reportes@exportarHorarios');
$router->get('/reportes/exportar/docentes', 'Reportes@exportarDocentes');
$router->get('/reportes/exportar/aulas', 'Reportes@exportarAulas');

// Rutas de carga masiva
$router->get('/carga-masiva', 'CargaMasiva@index');
$router->get('/carga-masiva/plantilla/{tipo}', 'CargaMasiva@descargarPlantilla');
$router->get('/carga-masiva/plantilla/{tipo}/{formato}', 'CargaMasiva@descargarPlantilla');
$router->post('/carga-masiva/procesar', 'CargaMasiva@procesar');

// Ejecutar el router
$router->run();
