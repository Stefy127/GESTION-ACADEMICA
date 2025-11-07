-- Tabla para ausencias justificadas de docentes
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_name = 'ausencias_docente'
    ) THEN
        CREATE TABLE ausencias_docente (
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
        
        CREATE INDEX idx_ausencias_docente_docente ON ausencias_docente(docente_id);
        CREATE INDEX idx_ausencias_docente_fecha ON ausencias_docente(fecha);
        CREATE INDEX idx_ausencias_docente_asistencia ON ausencias_docente(asistencia_id);
    END IF;
END $$;

