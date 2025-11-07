-- Script de inicialización de la base de datos
-- Sistema de Gestión Académica Completo

-- Tabla de roles de usuario
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    permisos JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios (incluye docentes y personal administrativo)
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    ci VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_nacimiento DATE,
    password_hash VARCHAR(255) NOT NULL,
    rol_id INTEGER REFERENCES roles(id),
    activo BOOLEAN DEFAULT true,
    ultimo_acceso TIMESTAMP,
    token_reset VARCHAR(255),
    token_expires TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de materias
CREATE TABLE IF NOT EXISTS materias (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    nivel VARCHAR(50),
    carga_horaria INTEGER DEFAULT 0,
    creditos INTEGER DEFAULT 0,
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de grupos
CREATE TABLE IF NOT EXISTS grupos (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(20) NOT NULL,
    semestre VARCHAR(20),
    turno VARCHAR(20),
    materia_id INTEGER REFERENCES materias(id),
    docente_id INTEGER REFERENCES usuarios(id),
    capacidad_maxima INTEGER DEFAULT 30,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de aulas
CREATE TABLE IF NOT EXISTS aulas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    capacidad INTEGER NOT NULL,
    tipo VARCHAR(50),
    ubicacion VARCHAR(200),
    equipamiento TEXT,
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de horarios
CREATE TABLE IF NOT EXISTS horarios (
    id SERIAL PRIMARY KEY,
    grupo_id INTEGER REFERENCES grupos(id),
    aula_id INTEGER REFERENCES aulas(id),
    docente_id INTEGER REFERENCES usuarios(id),
    dia_semana INTEGER NOT NULL CHECK (dia_semana >= 1 AND dia_semana <= 7), -- 1=Lunes, 7=Domingo
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(aula_id, dia_semana, hora_inicio, hora_fin),
    UNIQUE(docente_id, dia_semana, hora_inicio, hora_fin)
);

-- Tabla de asistencia docente
CREATE TABLE IF NOT EXISTS asistencia_docente (
    id SERIAL PRIMARY KEY,
    docente_id INTEGER REFERENCES usuarios(id),
    horario_id INTEGER REFERENCES horarios(id),
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado VARCHAR(20) DEFAULT 'presente', -- presente, ausente, justificado, tardanza
    hora_marcacion TIMESTAMP NOT NULL,
    tiempo_marcacion INTERVAL,
    observaciones TEXT,
    registrado_por INTEGER REFERENCES usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(docente_id, horario_id, fecha)
);

-- Tabla de ausencias justificadas de docentes
CREATE TABLE IF NOT EXISTS ausencias_docente (
    id SERIAL PRIMARY KEY,
    docente_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    asistencia_id INTEGER REFERENCES asistencia_docente(id) ON DELETE SET NULL,
    fecha DATE NOT NULL,
    justificacion TEXT,
    archivo_soporte VARCHAR(255),
    estado VARCHAR(20) DEFAULT 'pendiente',
    creado_por INTEGER REFERENCES usuarios(id),
    actualizado_por INTEGER REFERENCES usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ausencias_docente_docente ON ausencias_docente(docente_id);
CREATE INDEX IF NOT EXISTS idx_ausencias_docente_fecha ON ausencias_docente(fecha);
CREATE INDEX IF NOT EXISTS idx_ausencias_docente_asistencia ON ausencias_docente(asistencia_id);

-- Tabla de logs de actividad
CREATE TABLE IF NOT EXISTS logs_actividad (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INTEGER,
    datos_anteriores JSONB,
    datos_nuevos JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para carga masiva de datos
CREATE TABLE IF NOT EXISTS carga_masiva (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- usuarios, materias, grupos, aulas
    archivo_nombre VARCHAR(255),
    total_registros INTEGER DEFAULT 0,
    registros_exitosos INTEGER DEFAULT 0,
    registros_fallidos INTEGER DEFAULT 0,
    errores TEXT,
    procesado_por INTEGER REFERENCES usuarios(id),
    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, procesando, completado, error
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de sesiones
CREATE TABLE IF NOT EXISTS sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    ip_address INET,
    user_agent TEXT,
    datos_sesion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);

-- Índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_activo ON usuarios(activo);
CREATE INDEX IF NOT EXISTS idx_grupos_materia ON grupos(materia_id);
CREATE INDEX IF NOT EXISTS idx_grupos_docente ON grupos(docente_id);
CREATE INDEX IF NOT EXISTS idx_horarios_grupo ON horarios(grupo_id);
CREATE INDEX IF NOT EXISTS idx_horarios_aula ON horarios(aula_id);
CREATE INDEX IF NOT EXISTS idx_horarios_docente ON horarios(docente_id);
CREATE INDEX IF NOT EXISTS idx_horarios_dia_hora ON horarios(dia_semana, hora_inicio);
CREATE INDEX IF NOT EXISTS idx_asistencia_docente ON asistencia_docente(docente_id);
CREATE INDEX IF NOT EXISTS idx_asistencia_fecha ON asistencia_docente(fecha);
CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_actividad(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_fecha ON logs_actividad(created_at);

-- Insertar roles por defecto
INSERT INTO roles (nombre, descripcion, permisos) VALUES
('administrador', 'Acceso total al sistema', '{"usuarios": "all", "docentes": "all", "materias": "all", "grupos": "all", "aulas": "all", "horarios": "all", "asistencia": "all", "reportes": "all"}'),
('coordinador', 'Gestión de horarios y docentes', '{"usuarios": "read", "docentes": "all", "materias": "all", "grupos": "all", "aulas": "read", "horarios": "all", "asistencia": "all", "reportes": "read"}'),
('docente', 'Solo su carga horaria y asistencia', '{"usuarios": "read", "docentes": "read", "materias": "read", "grupos": "read", "aulas": "read", "horarios": "read", "asistencia": "own", "reportes": "own"}'),
('autoridad', 'Solo visualización de reportes', '{"usuarios": "read", "docentes": "read", "materias": "read", "grupos": "read", "aulas": "read", "horarios": "read", "asistencia": "read", "reportes": "read"}');

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (ci, nombre, apellido, email, password_hash, rol_id) VALUES
('12345678', 'Admin', 'Sistema', 'admin@sistema.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insertar materias de ejemplo
INSERT INTO materias (codigo, nombre, descripcion, nivel, carga_horaria, creditos) VALUES
('MAT101', 'Matemáticas I', 'Fundamentos de matemáticas básicas', 'Primer Semestre', 4, 4),
('FIS101', 'Física General', 'Fundamentos de física clásica', 'Primer Semestre', 3, 3),
('QUIM101', 'Química General', 'Fundamentos de química', 'Primer Semestre', 3, 3),
('PROG101', 'Programación I', 'Fundamentos de programación', 'Segundo Semestre', 4, 4),
('BD101', 'Bases de Datos', 'Fundamentos de bases de datos', 'Tercer Semestre', 3, 3);

-- Insertar aulas de ejemplo
INSERT INTO aulas (nombre, codigo, capacidad, tipo, ubicacion) VALUES
('Aula 101', 'A101', 30, 'Teórica', 'Edificio A - Primer Piso'),
('Aula 102', 'A102', 25, 'Teórica', 'Edificio A - Primer Piso'),
('Laboratorio 201', 'L201', 20, 'Laboratorio', 'Edificio B - Segundo Piso'),
('Aula 301', 'A301', 35, 'Teórica', 'Edificio A - Tercer Piso'),
('Sala de Conferencias', 'SC01', 50, 'Auditorio', 'Edificio Principal');

-- Insertar docentes de ejemplo
INSERT INTO usuarios (ci, nombre, apellido, email, telefono, password_hash, rol_id) VALUES
('87654321', 'Juan', 'Pérez', 'juan.perez@universidad.edu', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('11223344', 'María', 'González', 'maria.gonzalez@universidad.edu', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('55667788', 'Carlos', 'López', 'carlos.lopez@universidad.edu', '555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- Insertar grupos de ejemplo
INSERT INTO grupos (numero, semestre, turno, materia_id, docente_id, capacidad_maxima) VALUES
('G1-MAT101', '2024-1', 'Mañana', 1, 2, 30),
('G1-FIS101', '2024-1', 'Mañana', 2, 3, 25),
('G1-QUIM101', '2024-1', 'Tarde', 3, 4, 20),
('G1-PROG101', '2024-2', 'Mañana', 4, 2, 30),
('G1-BD101', '2024-3', 'Tarde', 5, 3, 25);

-- Insertar horarios de ejemplo
INSERT INTO horarios (grupo_id, aula_id, docente_id, dia_semana, hora_inicio, hora_fin) VALUES
(1, 1, 2, 1, '08:00:00', '10:00:00'), -- Lunes 8-10
(1, 1, 2, 3, '08:00:00', '10:00:00'), -- Miércoles 8-10
(2, 2, 3, 2, '10:00:00', '12:00:00'), -- Martes 10-12
(2, 2, 3, 4, '10:00:00', '12:00:00'), -- Jueves 10-12
(3, 3, 4, 1, '14:00:00', '16:00:00'), -- Lunes 14-16
(3, 3, 4, 3, '14:00:00', '16:00:00'); -- Miércoles 14-16