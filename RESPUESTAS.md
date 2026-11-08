# Respuestas teóricas

## 1. ¿Cuál es la diferencia entre `hasMany` y `belongsToMany` en Eloquent?

- **`hasMany`** define una relación **uno a muchos**. El modelo "hijo" guarda la clave foránea del "padre" en su propia tabla.

- **`belongsToMany`** define una relación **muchos a muchos**. Ninguna de las dos tablas guarda la clave de la otra: se usa una **tabla pivote** intermedia (por ejemplo, `etiqueta_tarea` con `tarea_id` y `etiqueta_id`).

## 2. ¿Qué es una migration y cómo se utiliza?

Una migración es un control de versiones para el esquema de la base de datos. Cada migración es una clase PHP con dos métodos:

- `up()`: aplica el cambio (crear una tabla, añadir una columna, un índice…).
- `down()`: lo revierte.

```bash
php artisan make:migration create_tareas_table   # crea el archivo
php artisan migrate                              # ejecuta las pendientes
php artisan migrate:rollback                     # revierte el último lote
php artisan migrate:fresh --seed                 # recrea todo y siembra datos
```

## 3. ¿Cómo funciona el Service Container en Laravel?

Es el encargado de crear los objetos que necesita la aplicación y entregárselos a quien los pida. En lugar de que cada clase cree sus propias dependencias con `new`, solo indica qué necesita y Laravel se lo proporciona.

Por ejemplo, en este proyecto el método `store` del controlador recibe un `StoreTareaRequest`. Nadie lo crea a mano: Laravel ve que el método lo necesita, lo construye, valida los datos y lo pasa ya listo.

## 4. ¿Qué es un middleware y para qué se usa?

Un middleware es una **capa que intercepta la petición HTTP** antes de que llegue al controlador (y/o la respuesta antes de enviarla). Las peticiones atraviesan una cadena de middlewares, como capas de una cebolla, y cada una puede dejarla pasar (`$next($request)`), modificarla o cortarla devolviendo una respuesta.

Usos típicos:

- Autenticación: `auth:sanctum` en este proyecto, que devuelve 401 si no hay un token válido.
- Limitación de peticiones: `throttle:10,1` en `/api/login` y `/api/register`.
- CORS, CSRF, mantenimiento, recorte de strings, cabeceras de seguridad, logging, idioma, etc.

Se asignan de forma global, por grupo (`web`, `api`) o por ruta. En Laravel 11+ se configuran en `bootstrap/app.php` con `->withMiddleware()`.

## 5. ¿Qué ventajas ofrece usar Laravel Sanctum o Passport?

Ambos resuelven la **autenticación de APIs** sin tener que implementarla a mano (generación, almacenamiento hasheado y revocación de tokens, middleware, _abilities_/_scopes_).

**Sanctum** (el usado en este proyecto):

- Ligero y fácil de configurar.
- **Tokens de acceso personal** (Bearer) para APIs y apps móviles, con _abilities_ y expiración.
- **Autenticación de SPA mediante cookies de sesión** (mismo dominio raíz), con protección CSRF y sin exponer tokens en el navegador.
- Ideal para APIs propias, SPAs y apps móviles de primera parte.

**Passport:**

- Servidor **OAuth2 completo** (authorization code, client credentials, refresh tokens, PKCE…).
- Más complejo de configurar y mantener.
