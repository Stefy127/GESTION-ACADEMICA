# Guía para Ejecutar la Migración de password_changed

## Opción 1: Usar el Script PHP (Más Fácil) ⭐

### Método A: Desde el Navegador
1. Abre tu navegador
2. Ve a: `http://localhost:8080/add_password_changed_column.php`
3. Verás un mensaje confirmando que la migración se ejecutó correctamente

### Método B: Desde la Línea de Comandos
```bash
# Desde el contenedor PHP
docker-compose exec php php add_password_changed_column.php

# O desde tu máquina local (si tienes PHP instalado)
php add_password_changed_column.php
```

---

## Opción 2: Usar PostgreSQL Directamente

### Método A: Usando el Makefile
```bash
make db
```

Luego, dentro de PostgreSQL, ejecuta:
```sql
\i database/migrations/004_add_password_changed_to_usuarios.sql
```

O copia y pega el contenido del archivo SQL directamente.

### Método B: Desde Docker Compose
```bash
# Copiar el archivo SQL al contenedor
docker cp database/migrations/004_add_password_changed_to_usuarios.sql gestion_academica_postgres:/tmp/

# Ejecutar el SQL
docker-compose exec postgres psql -U gestion_user -d gestion_academica -f /tmp/004_add_password_changed_to_usuarios.sql
```

### Método C: Ejecutar SQL Directamente
```bash
docker-compose exec -T postgres psql -U gestion_user -d gestion_academica << EOF
DO \$\$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'password_changed'
    ) THEN
        ALTER TABLE usuarios 
        ADD COLUMN password_changed BOOLEAN DEFAULT false;
        
        UPDATE usuarios 
        SET password_changed = true 
        WHERE password_changed = false;
    END IF;
END \$\$;
EOF
```

---

## Opción 3: Usar pgAdmin (Interfaz Gráfica)

1. Abre tu navegador y ve a: `http://localhost:8081`
2. Inicia sesión con:
   - Email: `admin@gestion.com`
   - Password: `admin123`
3. Conecta al servidor PostgreSQL:
   - Host: `postgres`
   - Puerto: `5432`
   - Usuario: `gestion_user`
   - Contraseña: `gestion_password`
4. Abre la base de datos `gestion_academica`
5. Ve a Tools > Query Tool
6. Copia y pega el contenido de `database/migrations/004_add_password_changed_to_usuarios.sql`
7. Ejecuta la consulta (F5 o botón Execute)

---

## Verificar que la Migración se Ejecutó

Para verificar que la columna fue agregada correctamente:

```bash
make db
```

Luego ejecuta:
```sql
SELECT column_name, data_type, column_default 
FROM information_schema.columns 
WHERE table_name = 'usuarios' 
AND column_name = 'password_changed';
```

Deberías ver una fila con la información de la columna.

---

## Recomendación

**Usa la Opción 1 - Método A** (desde el navegador) porque es la más sencilla y no requiere comandos adicionales.

