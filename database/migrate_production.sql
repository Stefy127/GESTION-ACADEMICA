-- Migración para crear tabla docentes_info en producción
-- Esta migración se ejecutará automáticamente durante el despliegue

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
    dedicacion VARCHAR(50),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_docentes_info_usuario ON docentes_info(usuario_id);

