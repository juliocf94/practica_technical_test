# API de Tareas · Laravel 12

> **Prueba técnica: Programador PHP con Laravel**
>
> |                   |                                |
> | ----------------- | ------------------------------ |
> | **Entregado por** | **Julio César Flores Fuentes** |
> | **DUI**           | 049739727                      |

API RESTful para gestionar tareas, con autenticación por token (Laravel Sanctum), validación con Form Requests, respuestas con API Resources y tests funcionales.

- **Stack:** PHP 8.3 · Laravel 12 · SQLite · Sanctum · PHPUnit
- **Respuestas teóricas:** [RESPUESTAS.md](RESPUESTAS.md)
- **Colección de Postman:** [docs/API_Tareas.postman_collection.json](docs/API_Tareas.postman_collection.json)

---

## Ejecutar el proyecto

### Opción A: Docker (recomendada)

Solo necesitas [Docker](https://docs.docker.com/get-docker/). No hace falta PHP, Composer ni una base de datos.

```bash
git clone https://github.com/juliocf94/practica_technical_test.git api-tareas
cd api-tareas
docker compose up --build
```

La API queda disponible en **http://localhost:8000/api**. La primera vez el contenedor instala dependencias, genera la `APP_KEY`, crea la base SQLite, ejecuta las migraciones y carga datos de prueba. No hay que configurar nada.

| Acción                                     | Comando                                                                                 |
| ------------------------------------------ | --------------------------------------------------------------------------------------- |
| Ejecutar los tests                         | `docker compose exec app php artisan test`                                              |
| Usar otro puerto (si el 8000 está ocupado) | `APP_PORT=8080 docker compose up` (PowerShell: `$env:APP_PORT=8080; docker compose up`) |
| Reiniciar con datos limpios                | `docker compose down -v && docker compose up`                                           |
| Detener                                    | `docker compose down`                                                                   |

### Opción B: local con PHP

Requisitos: PHP ≥ 8.2 con la extensión `pdo_sqlite`, y Composer.

```bash
git clone https://github.com/juliocf94/practica_technical_test.git api-tareas
cd api-tareas
composer install
cp .env.example .env            # Windows (cmd): copy .env.example .env
php artisan key:generate
php artisan migrate --seed      # crea database/database.sqlite si no existe (responde "yes")
php artisan serve
```

Tests: `php artisan test`

---

## Probar la API

Los datos de prueba incluyen este usuario con 12 tareas:

| Email              | Contraseña |
| ------------------ | ---------- |
| `test@example.com` | `password` |

**Con Postman:** importar `docs/API_Tareas.postman_collection.json` y ejecutar primero **Auth › Login**. El token se guarda solo y el resto de peticiones ya van autenticadas. Al crear una tarea, el id se guarda en `tarea_id`.

**Con curl:**

```bash
# 1. Obtener token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 2. Usarlo
curl http://localhost:8000/api/tareas -H "Authorization: Bearer <token>"
```

---

## Endpoints

Todas las rutas de tareas requieren `Authorization: Bearer <token>`.

### Autenticación

| Método | Ruta            | Descripción                                                                                |
| ------ | --------------- | ------------------------------------------------------------------------------------------ |
| POST   | `/api/register` | Crea un usuario y devuelve un token (`name`, `email`, `password`, `password_confirmation`) |
| POST   | `/api/login`    | Devuelve un token (`email`, `password`)                                                    |
| GET    | `/api/user`     | Usuario autenticado                                                                        |
| POST   | `/api/logout`   | Revoca el token actual                                                                     |

### Tareas

| Método      | Ruta               | Descripción                                                            | Éxito |
| ----------- | ------------------ | ---------------------------------------------------------------------- | ----- |
| GET         | `/api/tareas`      | Listar (paginado). Filtros opcionales: `?estado=pendiente&per_page=15` | 200   |
| POST        | `/api/tareas`      | Crear                                                                  | 201   |
| GET         | `/api/tareas/{id}` | Ver detalle                                                            | 200   |
| PUT / PATCH | `/api/tareas/{id}` | Actualizar (admite actualización parcial)                              | 200   |
| DELETE      | `/api/tareas/{id}` | Eliminar (devuelve un mensaje de confirmación)                         | 200   |

**Campos de una tarea**

| Campo          | Tipo   | Reglas                                                        |
| -------------- | ------ | ------------------------------------------------------------- |
| `titulo`       | string | obligatorio, máx. 255                                         |
| `descripcion`  | text   | opcional                                                      |
| `estado`       | enum   | obligatorio: `pendiente`, `en_progreso`, `completada`         |
| `fecha_limite` | date   | obligatorio, formato `AAAA-MM-DD`, no anterior a hoy al crear |

**Ejemplo de respuesta** (`POST /api/tareas` → 201)

```json
{
    "data": {
        "id": 16,
        "titulo": "Preparar la entrega",
        "descripcion": "Subir el proyecto a GitHub",
        "estado": "pendiente",
        "fecha_limite": "2030-12-31",
        "created_at": "2026-11-08T21:13:38+00:00",
        "updated_at": "2026-11-08T21:13:38+00:00"
    }
}
```

**Ejemplo de respuesta** (`DELETE /api/tareas/{id}` → 200)

```json
{ "message": "Tarea eliminada correctamente." }
```

**Códigos de error:** `401` sin token o token inválido · `403` la tarea pertenece a otro usuario · `404` no existe · `422` error de validación (con el detalle por campo en `errors`). Las rutas `/api/*` responden siempre en JSON, aunque el cliente no envíe `Accept: application/json`.