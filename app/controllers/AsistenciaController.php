<?php
class AsistenciaController extends Controller
{
    public function index()
    {
        $user = $this->getCurrentUser();
        $data = [
            'title' => 'Control de Asistencia',
            'user' => $user,
            'asistencias' => $this->getAsistencias($user),
            'horarios' => $this->getHorariosUsuario($user),
            'esDocente' => $user['rol'] === 'docente'
        ];
        return $this->view->renderWithLayout('asistencia/index', $data);
    }

    public function registrar()
    {
        $user = $this->getCurrentUser();
        $data = [
            'title' => 'Registrar Asistencia',
            'user' => $user,
            'horarios' => $this->getHorariosUsuario($user),
            'esDocente' => $user['rol'] === 'docente'
        ];
        return $this->view->renderWithLayout('asistencia/registrar', $data);
    }

    public function reportes()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }
        $data = [
            'title' => 'Reportes de Asistencia',
            'user' => $this->getCurrentUser(),
            'reportes' => $this->getReportes()
        ];
        return $this->view->renderWithLayout('asistencia/reportes', $data);
    }
    
    public function processRegistro()
    {
        $user = $this->getCurrentUser();
        
        if ($user['rol'] !== 'docente') {
            return json_encode([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta accion'
            ]);
        }
        
        if (!isset($_POST['horario_id']) || empty($_POST['horario_id'])) {
            return json_encode([
                'success' => false,
                'message' => 'Horario no especificado'
            ]);
        }
        
        $horarioId = intval($_POST['horario_id']);
        $horarios = $this->getHorariosConDisponibilidad($user);
        
        $horarioSeleccionado = null;
        foreach ($horarios as $h) {
            if ($h['id'] === $horarioId) {
                $horarioSeleccionado = $h;
                break;
            }
        }
        
        if (!$horarioSeleccionado) {
            return json_encode([
                'success' => false,
                'message' => 'Horario no encontrado'
            ]);
        }
        
        $resultado = $this->registrarAsistencia($user, $horarioSeleccionado);
        return json_encode($resultado);
    }
    
    private function registrarAsistencia($docente, $horario)
    {
        $horaMarcacion = new DateTime();
        $horaProgramada = DateTime::createFromFormat('H:i', $horario['hora_inicio']);
        
        $estado = 'presente';
        $mensaje = 'Asistencia registrada exitosamente';
        
        if ($horaMarcacion > $horaProgramada) {
            $diferencia = $horaMarcacion->diff($horaProgramada);
            $minutosTarde = $diferencia->i + ($diferencia->h * 60);
            $estado = 'tardanza';
            $mensaje = 'Llegada tardia registrada. Llegaste ' . $minutosTarde . ' minutos tarde.';
        }
        
        return [
            'success' => true,
            'message' => $mensaje,
            'data' => [
                'hora_marcacion' => $horaMarcacion->format('H:i:s'),
                'fecha' => $horaMarcacion->format('Y-m-d'),
                'estado' => $estado,
                'materia' => $horario['materia'],
                'grupo' => $horario['grupo'],
                'aula' => $horario['aula']
            ]
        ];
    }

    private function getAsistencias($user)
    {
        $nombreDocente = $user['nombre'] . ' ' . $user['apellido'];
        
        if ($user['rol'] === 'docente') {
            return [
                ['id' => 1, 'fecha' => date('Y-m-d'), 'hora' => '08:00', 'docente' => $nombreDocente, 'materia' => 'Matemáticas', 'grupo' => 'A1', 'estado' => 'presente', 'hora_marcacion' => '08:05'],
                ['id' => 2, 'fecha' => date('Y-m-d'), 'hora' => '14:00', 'docente' => $nombreDocente, 'materia' => 'Física', 'grupo' => 'A2', 'estado' => 'tardanza', 'hora_marcacion' => '14:20']
            ];
        }
        return [
            ['id' => 1, 'fecha' => date('Y-m-d'), 'hora' => '08:00', 'docente' => 'Juan Pérez', 'materia' => 'Matemáticas', 'grupo' => 'A1', 'estado' => 'presente'],
            ['id' => 2, 'fecha' => date('Y-m-d'), 'hora' => '10:00', 'docente' => 'María González', 'materia' => 'Física', 'grupo' => 'A2', 'estado' => 'presente']
        ];
    }

    private function getHorariosUsuario($user)
    {
        if ($user['rol'] === 'docente') {
            return $this->getHorariosConDisponibilidad($user);
        }
        return [];
    }
    
    private function getHorariosConDisponibilidad($user)
    {
        $horarios = [
            ['id' => 1, 'dia' => 'Lunes', 'dia_numero' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'materia' => 'Matemáticas I', 'grupo' => 'G1-MAT101', 'aula' => 'A101'],
            ['id' => 2, 'dia' => 'Miércoles', 'dia_numero' => 3, 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'materia' => 'Matemáticas I', 'grupo' => 'G1-MAT101', 'aula' => 'A101'],
            ['id' => 3, 'dia' => 'Martes', 'dia_numero' => 2, 'hora_inicio' => '14:00', 'hora_fin' => '16:00', 'materia' => 'Programación I', 'grupo' => 'G1-PROG101', 'aula' => 'L201']
        ];
        $horaActual = new DateTime();
        $diaActual = (int)date('N');
        foreach ($horarios as &$horario) {
            $horario['disponible'] = false;
            $horario['estado'] = 'no_disponible';
            $horario['mensaje'] = 'Fuera del horario';
            if ($horario['dia_numero'] !== $diaActual) continue;
            $horaInicio = DateTime::createFromFormat('H:i', $horario['hora_inicio']);
            $ventanaInicio = clone $horaInicio;
            $ventanaInicio->modify('-15 minutes');
            $ventanaFin = clone $horaInicio;
            if ($horaActual >= $ventanaInicio && $horaActual <= $ventanaFin) {
                $horario['disponible'] = true;
                $horario['estado'] = 'disponible';
                $horario['mensaje'] = 'Disponible para marcar';
            } elseif ($horaActual < $ventanaInicio) {
                $horario['estado'] = 'pendiente';
                $horario['mensaje'] = 'Disponible a las ' . $ventanaInicio->format('H:i');
            } elseif ($horaActual > $ventanaFin) {
                $horario['estado'] = 'vencido';
            }
        }
        return $horarios;
    }

    private function getReportes()
    {
        return [
            ['docente' => 'Juan Pérez', 'total_clases' => 40, 'asistencias' => 38, 'ausencias' => 2, 'porcentaje' => 95.0],
            ['docente' => 'María González', 'total_clases' => 40, 'asistencias' => 40, 'ausencias' => 0, 'porcentaje' => 100.0]
        ];
    }
}
