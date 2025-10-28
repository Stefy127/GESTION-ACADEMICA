-- Tabla para información adicional de docentes
CREATE TABLE IF NOT EXISTS docentes_info (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE UNIQUE NOT NULL,
    titulo_profesional VARCHAR(150),
    especialidad VARCHAR(100),
    departamento VARCHAR(100),
    anos_experiencia INTEGER DEFAULT 0,
    grado_academico VARCHAR(100),
    universidad_egresado VARCHAR(200),
    fecha_ingreso DATE,
    categoria VARCHAR(50),
    dedicacion VARCHAR(50), -- Tiempo completo, medio tiempo, etc.
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índice para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_docentes_info_usuario ON docentes_info(usuario_id);

-- Comentarios para documentación
COMMENT ON TABLE docentes_info IS 'Información adicional de los docentes (título, especialidad, etc.)';
COMMENT ON COLUMN docentes_info.usuario_id IS 'Referencia al usuario con rol docente';
COMMENT ON COLUMN docentes_info.dedicacion IS 'Tipos: tiempo_completo, medio_tiempo, por_horas';

