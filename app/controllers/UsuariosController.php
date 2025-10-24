<?php
/**
 * Controlador para gestión de usuarios
 */
class UsuariosController extends Controller
{
    public function index()
    {
        // Verificar permisos
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Gestión de Usuarios',
            'user' => $this->getCurrentUser(),
            'usuarios' => $this->getUsuarios()
        ];

        return $this->view->renderWithLayout('usuarios/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Usuario',
            'user' => $this->getCurrentUser(),
            'roles' => $this->getRoles()
        ];

        return $this->view->renderWithLayout('usuarios/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Usuario',
            'user' => $this->getCurrentUser(),
            'usuario' => $this->getUsuario($id),
            'roles' => $this->getRoles()
        ];

        return $this->view->renderWithLayout('usuarios/edit', $data);
    }

    private function getUsuarios()
    {
        // Simular datos de usuarios
        return [
            ['id' => 1, 'nombre' => 'Admin', 'apellido' => 'Sistema', 'email' => 'admin@sistema.edu', 'rol' => 'administrador'],
            ['id' => 2, 'nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan.perez@universidad.edu', 'rol' => 'docente'],
            ['id' => 3, 'nombre' => 'María', 'apellido' => 'González', 'email' => 'maria.gonzalez@universidad.edu', 'rol' => 'docente'],
            ['id' => 4, 'nombre' => 'Carlos', 'apellido' => 'López', 'email' => 'carlos.lopez@universidad.edu', 'rol' => 'docente']
        ];
    }

    private function getRoles()
    {
        return [
            ['id' => 1, 'nombre' => 'administrador'],
            ['id' => 2, 'nombre' => 'coordinador'],
            ['id' => 3, 'nombre' => 'docente'],
            ['id' => 4, 'nombre' => 'autoridad']
        ];
    }

    private function getUsuario($id)
    {
        $usuarios = $this->getUsuarios();
        foreach ($usuarios as $usuario) {
            if ($usuario['id'] == $id) {
                return $usuario;
            }
        }
        return null;
    }
}
