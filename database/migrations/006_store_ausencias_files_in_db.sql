-- Migración: almacenar soportes de ausencias en la base de datos
-- Agrega columnas para nombre, mime y contenido en base64
ALTER TABLE ausencias_docente
    ADD COLUMN IF NOT EXISTS archivo_soporte_name VARCHAR(255),
    ADD COLUMN IF NOT EXISTS archivo_soporte_mime VARCHAR(100),
    ADD COLUMN IF NOT EXISTS archivo_soporte_base64 TEXT;

-- Nota: no eliminamos la columna archivo_soporte (nombre en FS) para compatibilidad.
