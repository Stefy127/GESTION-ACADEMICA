<?php
/**
 * Archivo principal de la aplicación
 */

// Cargar configuración
require_once __DIR__ . '/../config/app.php';

// Cargar clases principales
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/Model.php';
require_once __DIR__ . '/../app/models/Auth.php';
require_once __DIR__ . '/../app/models/Middleware.php';
require_once __DIR__ . '/../app/views/View.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/helpers/ActivityLogger.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/BitacoraController.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/UsuariosController.php';
require_once __DIR__ . '/../app/controllers/DocentesController.php';
require_once __DIR__ . '/../app/controllers/MateriasController.php';
require_once __DIR__ . '/../app/controllers/GruposController.php';
require_once __DIR__ . '/../app/controllers/AulasController.php';
require_once __DIR__ . '/../app/controllers/HorariosController.php';
require_once __DIR__ . '/../app/controllers/AsistenciaController.php';
require_once __DIR__ . '/../app/controllers/ReportesController.php';
require_once __DIR__ . '/../app/controllers/CargaMasivaController.php';
require_once __DIR__ . '/../app/Router.php';

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Crear instancia del router
$router = new Router();

// Definir rutas
$router->get('/', 'Home@index');
$router->get('/about', 'Home@about');

// Rutas de autenticación
$router->get('/login', 'Auth@login');
$router->post('/auth/login', 'Auth@processLogin');
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

// Rutas de gestión de usuarios
$router->get('/usuarios', 'Usuarios@index');
$router->get('/usuarios/create', 'Usuarios@create');
$router->post('/usuarios/store', 'Usuarios@store');
$router->get('/usuarios/edit/{id}', 'Usuarios@edit');
$router->post('/usuarios/update/{id}', 'Usuarios@update');
$router->post('/usuarios/delete/{id}', 'Usuarios@delete');

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

// Rutas de gestión de grupos
$router->get('/grupos', 'Grupos@index');
$router->get('/grupos/create', 'Grupos@create');
$router->post('/grupos/store', 'Grupos@store');
$router->get('/grupos/edit/{id}', 'Grupos@edit');
$router->post('/grupos/update/{id}', 'Grupos@update');
$router->post('/grupos/delete/{id}', 'Grupos@delete');

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

// Rutas de reportes
$router->get('/reportes', 'Reportes@index');
$router->get('/reportes/asistencia', 'Reportes@asistencia');
$router->get('/reportes/horarios', 'Reportes@horarios');

// Rutas de carga masiva
$router->get('/carga-masiva', 'CargaMasiva@index');
$router->get('/carga-masiva/procesar', 'CargaMasiva@procesar');

// Ejecutar el router
$router->run();
