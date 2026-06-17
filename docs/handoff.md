# Handoff tecnico

## Estado actual del proyecto
- Fase activa: FASE 2 en implementacion (bloques 1 y 2 completados: register/login + middleware/users/me).
- FASE 1: completada y validada en entorno real.
- Implementacion de negocio: usuarios/autenticacion con middleware y perfil `users/me` implementados y validados tecnicamente.
- Gobierno documental: `handoff.md` es el documento principal del estado del proyecto.
- Regla operativa activa: antes de cada tarea se validan `handoff.md`, `roadmap.md`, `database.md` y `api.md`.
- Regla de implementacion activa: priorizar simplicidad explicable para TFG DAW (legibilidad, mantenimiento y logica explicita).

## Arquitectura
- Patron: SPA React consumiendo API REST en PHP, con MySQL como persistencia.
- Responsabilidades:
  - Frontend React: UI y consumo HTTP.
  - Backend PHP: rutas API, configuracion, acceso a datos y logica de negocio futura.
  - MySQL: persistencia e integridad relacional.
- Regla clave: la logica de negocio vive en backend.

## Base de datos
- Esquema definido en `database/schema.sql`.
- Validacion de compatibilidad MySQL 8 completada.
- Ejecucion real de `schema.sql` completada correctamente en entorno local.

## Backend realizado (FASE 1)
- Estructura creada:
  - `backend/public/`
  - `backend/routes/`
  - `backend/src/Config/`
  - `backend/src/Http/`
  - `backend/src/Controllers/`
  - `backend/src/Services/`
  - `backend/src/Repositories/`
  - `backend/src/Middleware/`
  - `backend/src/Utils/`
  - `backend/tests/`
- Configuracion inicial PHP:
  - `backend/composer.json` (autoload PSR-4 `App\\`).
  - `backend/.env.example` y `backend/.gitignore`.
  - `backend/README.md` con arranque local previsto.
- Configuracion de entorno/DB:
  - `backend/src/Config/Env.php`
  - `backend/src/Config/Database.php`
- Sistema basico de rutas y punto de entrada:
  - `backend/src/Http/Request.php`
  - `backend/src/Http/Response.php`
  - `backend/src/Http/Router.php`
  - `backend/routes/api.php`
  - `backend/public/index.php`
  - `backend/public/.htaccess`
- Endpoint tecnico disponible en FASE 1:
  - `GET /api/v1/health`
- Mejoras estructurales aplicadas en revision:
  - `backend/public/index.php`: soporte para autoload de Composer cuando exista `vendor/autoload.php` y fallback a autoload interno.
  - `backend/src/Config/Env.php`: lectura de variables mas robusta (soporte de valores entre comillas y acceso seguro sin perder valores como `0` o cadena vacia).
  - `backend/src/Config/Database.php`: validacion explicita de extension `pdo_mysql` y casteo seguro de variables de entorno.
  - `backend/src/Http/Router.php`: handlers tipados como `callable`.
  - `backend/src/Http/Request.php`: lectura de cabeceras `CONTENT_TYPE` y `CONTENT_LENGTH`.
  - `backend/src/Http/Router.php`: simplificado eliminando soporte `HEAD/OPTIONS` no necesario en FASE 1.

## Backend realizado (FASE 2 - bloque 1)
- Utilidades de validacion simples:
  - `backend/src/Utils/Validator.php`
  - Reglas para `register` y `login` con errores por campo.
- Repositorios creados:
  - `backend/src/Repositories/UserRepository.php`
    - `findByEmail(email)`
    - `create(name, email, password_hash)`
  - `backend/src/Repositories/AuthTokenRepository.php`
    - `create(user_id, token_hash, expires_at)`
- Servicio de autenticacion:
  - `backend/src/Services/AuthService.php`
    - Registro con `password_hash()`
    - Login con `password_verify()`
    - Token opaco generado con `random_bytes()` + `bin2hex()`
    - Persistencia solo de `token_hash` (SHA-256)
    - Email normalizado con `trim + lowercase`
  - `backend/src/Services/AuthException.php` para errores de dominio controlados.
- Controlador:
  - `backend/src/Controllers/AuthController.php`
    - `register(Request)` con respuesta `201`
    - `login(Request)` con respuesta `200`
    - Errores claros sin exponer detalles sensibles.
- Rutas nuevas:
  - `POST /api/v1/auth/register`
  - `POST /api/v1/auth/login`
  - Definidas en `backend/routes/api.php`.
- Validacion tecnica:
  - Sintaxis PHP validada en todo `backend`: `ALL_OK:13`.

## Backend realizado (FASE 2 - bloque 2)
- Middleware de autenticacion implementado:
  - `backend/src/Middleware/AuthMiddleware.php`
  - Validaciones aplicadas:
    - Header `Authorization` obligatorio.
    - Formato `Bearer <token>` obligatorio.
    - Hash SHA-256 del token recibido.
    - Verificacion en `auth_tokens` de token no revocado y no expirado.
  - Respuesta de error estandar `401 Unauthorized` para token ausente/invalido.
- Endpoint protegido implementado:
  - `GET /api/v1/users/me` en `backend/routes/api.php`.
  - Flujo: middleware autentica -> se resuelve `user_id` -> `UserController::me()` devuelve perfil basico.
- Archivos creados/modificados en bloque 2:
  - `backend/src/Middleware/AuthMiddleware.php` (nuevo)
  - `backend/src/Controllers/UserController.php` (nuevo)
  - `backend/src/Repositories/AuthTokenRepository.php` (metodo `findValidByHash`)
  - `backend/src/Repositories/UserRepository.php` (metodo `findById`)
  - `backend/routes/api.php` (ruta protegida `GET /api/v1/users/me`)
- Validacion tecnica:
  - Sintaxis PHP validada en todo `backend`: `ALL_OK:15`.
- Pruebas tecnicas realizadas sobre `users/me`:
  - Con token valido: `200 OK`.
  - Sin token: `401 Unauthorized`.
  - Con token invalido: `401 Unauthorized`.
- Validacion manual real sobre servidor HTTP (`php -S localhost:8000 -t public`) usando PowerShell:
  - Login real: `POST /api/v1/auth/login` -> `200 OK` y token Bearer valido.
  - Acceso real con token: `GET /api/v1/users/me` -> `200 OK`.
  - Acceso real sin token: `GET /api/v1/users/me` -> `401 Unauthorized`.
  - Acceso real con token invalido: `GET /api/v1/users/me` -> `401 Unauthorized`.
  - Confirmacion: la validacion del bloque 2 se realizo contra servidor HTTP real, no solo con pruebas internas.

## Frontend realizado (FASE 1)
- Proyecto React generado con Vite en `frontend/`.
- Dependencias instaladas:
  - `react`, `react-dom`, `vite`
  - `bootstrap`
- Estructura base creada:
  - `frontend/src/api/`
  - `frontend/src/components/layout/`
  - `frontend/src/pages/`
  - `frontend/src/hooks/`
  - `frontend/src/context/`
  - `frontend/src/styles/`
  - `frontend/src/config/`
- Configuracion bootstrap:
  - Import en `frontend/src/main.jsx`.
- Cliente HTTP base:
  - `frontend/src/api/httpClient.js`
- Configuracion de entorno:
  - `frontend/.env.example`
  - `frontend/src/config/env.js`
- Verificacion tecnica:
  - `npm run build` ejecutado correctamente.
- Mejoras estructurales aplicadas en revision:
  - `frontend/package.json`: nombre del proyecto ajustado a `garage-manager-frontend`.
  - `frontend/src/api/httpClient.js`: normalizacion de rutas y `Content-Type` solo cuando hay body JSON.
  - `frontend/src/config/env.js`: normalizacion de `VITE_API_BASE_URL` para evitar dobles slashes.
  - `frontend/src/context/`: separacion de contexto y hook (`appContextDefinition.js`, `AppContext.jsx`, `useAppContext.js`) para cumplir regla de Fast Refresh/ESLint.
  - Limpieza de plantilla Vite no usada: eliminados `src/App.css`, `src/index.css` y `src/assets/`.

## Tareas completadas
- FASE 0 aprobada y congelada.
- Scaffolding backend PHP sin logica de negocio.
- Scaffolding frontend React + Bootstrap sin logica de negocio.
- Cliente HTTP base para API.
- Revision de `schema.sql` para compatibilidad MySQL 8 (analisis estatico).
- Actualizacion documental de avance FASE 1.
- Revision tecnica final del scaffold FASE 1 completada.
- Lint frontend validado sin errores (`npm run lint`).
- Regla de simplicidad para TFG DAW aplicada a partir de esta iteracion.
- Verificacion de ignore en Git:
  - `frontend/node_modules` ignorado.
  - `frontend/dist` ignorado.
  - `backend/.env` y `frontend/.env` ignorados.
- Verificacion de nomenclatura de entorno:
  - `backend/.env.example` correcto.
  - `frontend/.env.example` correcto.
- Diseno funcional completo de FASE 2 (usuarios y autenticacion) sin implementacion de codigo.
- Definido checklist operativo para validar entorno real (PHP, Composer, MySQL, backend y frontend) antes de implementar FASE 2.
- Entregado procedimiento paso a paso para validacion de entorno real antes de iniciar implementacion de FASE 2.
- Recomendado entorno local unico para el TFG: instalacion por separado de PHP + Composer + MySQL (instaladores oficiales).
- Corregido error de arranque en `backend/public/index.php`: eliminado BOM UTF-8 y colocado `declare(strict_types=1);` inmediatamente despues de `<?php`.
- Correccion global aplicada en backend PHP:
  - todos los archivos `.php` guardados en UTF-8 sin BOM;
  - todos inician directamente con `<?php`;
  - `declare(strict_types=1);` colocado inmediatamente despues de `<?php`.
- Validacion de sintaxis ejecutada sobre todos los `.php` de `backend`: `ALL_OK:7`.
- Validacion real de entorno completada:
  - PHP validado.
  - Composer validado.
  - MySQL validado.
  - `schema.sql` ejecutado correctamente.
  - endpoint `GET /api/v1/health` validado.
- FASE 2 bloque 1 implementado:
  - utilidades de validacion,
  - `UserRepository`,
  - `AuthTokenRepository`,
  - `AuthService`,
  - `POST /auth/register`,
  - `POST /auth/login`.
- Correccion aplicada en `backend/routes/api.php` tras prueba real de `POST /api/v1/auth/register`:
  - eliminado `use Throwable;` en archivo sin `namespace` (linea 13);
  - actualizado a `catch (\Throwable)` en cierres de rutas;
  - validacion de sintaxis posterior en backend: `ALL_OK:13`.
- Diagnostico de `500` en `POST /api/v1/auth/register`:
  - detectada ausencia de `backend/.env`;
  - al usar valores por defecto, `Database::getConnection()` fallaba con credenciales invalidas;
  - error real observado: `RuntimeException: No se pudo conectar a MySQL.` en `backend/src/Config/Database.php:46` (causa previa `PDOException` en `backend/src/Config/Database.php:40`, `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`).
- Correccion minima aplicada:
  - creado `backend/.env` local a partir de `backend/.env.example`;
  - ajustado `DB_PASSWORD` con la credencial local correcta;
  - verificada conexion y tabla `users` (`DB_OK users=0`);
  - validacion de sintaxis posterior en backend: `ALL_OK:13`.
- Pruebas funcionales reales completadas (FASE 2 bloque 1):
  - `POST /api/v1/auth/register`: alta de usuario nueva validada (`201 Created`).
  - `POST /api/v1/auth/register`: email existente validado (`409 Conflict`).
  - `POST /api/v1/auth/login`: credenciales correctas validadas (`200 OK`) con devolucion de token tipo Bearer.
  - No se almacenan ni documentan tokens en claro fuera de la respuesta de login.
- FASE 2 bloque 2 implementado:
  - `AuthMiddleware` Bearer token.
  - `GET /api/v1/users/me` protegido.
  - Verificacion tecnica de acceso:
    - `200` con token valido.
    - `401` sin token.
    - `401` con token invalido.
  - Verificacion manual real en servidor HTTP:
    - `POST /api/v1/auth/login`: `200`.
    - `GET /api/v1/users/me` con token valido: `200`.
    - `GET /api/v1/users/me` sin token: `401`.
    - `GET /api/v1/users/me` con token invalido: `401`.

## Tareas pendientes
- Implementar en siguiente bloque (tras validacion):
  - `POST /auth/logout`
  - `PUT /users/me`
- Mantener bloqueo de alcance:
  - no implementar vehiculos, mantenimientos, productos, carrito ni pedidos en FASE 2.

## Decisiones tecnicas
- Backend sin framework en arranque para reducir tiempo de bootstrap.
- Router HTTP propio minimo para habilitar crecimiento incremental por fases.
- Carga de entorno propia (`Env.php`) sin dependencias externas en FASE 1.
- Configuracion DB centralizada en `Database.php`.
- Frontend con Vite + React por rapidez de arranque.
- Bootstrap integrado desde `main.jsx`.
- Cliente HTTP base con `fetch` y `VITE_API_BASE_URL`.
- Compatibilidad dual de autoload en backend:
  - Prioridad a Composer autoload si existe.
  - Fallback interno para no bloquear arranque del scaffold en FASE 1.
- Politica de implementacion:
  - Preferir la solucion mas simple cuando el resultado funcional sea equivalente.
  - Evitar patrones enterprise, abstracciones innecesarias y optimizaciones prematuras.
  - Mantener funciones cortas, nombres claros y flujo explicito.
- Entorno de desarrollo recomendado para este TFG:
  - Opcion elegida: instalacion separada (PHP + Composer + MySQL oficial).
  - Motivo: alinea exactamente con el stack del proyecto (MySQL real, no MariaDB), reduce sorpresas de compatibilidad y es facil de defender academicamente.
- Estrategia aplicada en implementacion:
  - token opaco en claro solo en respuesta de login;
  - almacenamiento exclusivo de `token_hash` en BD;
  - expiracion configurada por `TOKEN_TTL_HOURS` (default 24h);
  - mensajes de error de login genericos para no filtrar informacion sensible.

## Propuesta FASE 2 (diseno + implementacion parcial)

### Objetivo de la fase
- Implementar modulo de usuarios y autenticacion con tokens opacos hasheados, manteniendo codigo simple y explicable para TFG DAW.

### 1) Flujo completo de registro
1. Cliente envia `POST /auth/register` con `name`, `email`, `password`.
2. Backend valida formato y reglas de campos.
3. Backend normaliza email (trim + lowercase) y verifica unicidad.
4. Backend genera `password_hash()` y crea registro en `users`.
5. Respuesta `201 Created` con datos basicos de usuario (sin password, sin token).
6. Login queda como paso separado para mantener flujo explicito y sencillo.

### 2) Flujo completo de login
1. Cliente envia `POST /auth/login` con `email`, `password`.
2. Backend valida campos minimos y busca usuario por email normalizado.
3. Backend valida password con `password_verify()`.
4. Si es correcto:
   - genera token en bruto con `random_bytes()`;
   - calcula `token_hash` (SHA-256);
   - guarda en `auth_tokens`: `user_id`, `token_hash`, `expires_at`, `revoked_at = NULL`.
5. Respuesta `200 OK` con token en claro (solo esa vez) y usuario basico.
6. Si falla credencial: `401 Unauthorized` con mensaje generico.

### 3) Flujo completo de logout
1. Cliente envia `POST /auth/logout` con `Authorization: Bearer <token>`.
2. Middleware autentica el token y adjunta `auth_user_id` + token actual.
3. Backend marca `revoked_at = NOW()` en ese token.
4. Respuesta `204 No Content` (o `200 OK` con mensaje simple).
5. Token revocado deja de funcionar inmediatamente.

### 4) Diseno del middleware de autenticacion
1. Verifica header `Authorization`.
2. Rechaza si no cumple formato `Bearer <token>`.
3. Calcula hash del token recibido.
4. Busca en `auth_tokens` por hash.
5. Valida:
   - token existente;
   - `revoked_at IS NULL`;
   - `expires_at > NOW()`.
6. Si pasa validacion:
   - carga contexto autenticado (`auth_user_id`, `auth_token_id`).
7. Si no pasa:
   - responde `401 Unauthorized`.

### 5) Diseno del control de ownership de recursos
- Regla general: todo recurso de usuario se consulta/edita filtrando por `user_id = auth_user_id`.
- Para recursos anidados (ejemplo futuro: mantenimiento de vehiculo):
  - primero validar que el vehiculo pertenece al usuario autenticado;
  - luego operar sobre el recurso hijo.
- Respuesta recomendada ante recurso ajeno o inexistente: `404 Not Found` para no exponer IDs validos.

### 6) Endpoints a implementar primero (orden)
1. `POST /auth/register`
2. `POST /auth/login`
3. Middleware de autenticacion (infraestructura)
4. `GET /users/me`
5. `POST /auth/logout`
6. `PUT /users/me` (cierre de modulo usuario basico)

### 6.1) Orden tecnico de implementacion (estado actual)
1. Preparar utilidades comunes de respuesta y validacion de entrada. (completado)
2. Implementar acceso a datos de usuario/token. (completado)
3. Implementar `POST /auth/register`. (completado)
4. Implementar `POST /auth/login`. (completado)
5. Implementar middleware de autenticacion Bearer token. (completado)
6. Implementar `GET /users/me`. (completado)
7. Implementar `POST /auth/logout`. (pendiente)
8. Implementar `PUT /users/me`. (pendiente)
9. Pruebas manuales de flujo completo (register -> login -> me -> logout -> token invalido). (pendiente)

### 6.2) Archivos a crear/modificar (plan)
- Crear:
  - `backend/src/Controllers/AuthController.php`
  - `backend/src/Controllers/UserController.php`
  - `backend/src/Middleware/AuthMiddleware.php`
  - `backend/src/Repositories/UserRepository.php`
  - `backend/src/Repositories/AuthTokenRepository.php`
  - `backend/src/Services/AuthService.php`
  - `backend/src/Utils/Validator.php`
- Modificar:
  - `backend/routes/api.php` (alta de endpoints de FASE 2)
  - `backend/src/Http/Request.php` (si hace falta helper sencillo para body/atributos auth)
  - `backend/src/Http/Router.php` (si hace falta encadenar middleware de forma minima y explicable)
  - `backend/.env.example` (agregar `TOKEN_TTL_HOURS` si no estuviera definido)

### 6.3) Tablas que se utilizaran
- `users`
  - registro, login, perfil (`GET/PUT /users/me`)
- `auth_tokens`
  - login (alta de token hasheado)
  - middleware (validacion de token)
  - logout (revocacion de token)

### 7) Validaciones previstas
- `POST /auth/register`
  - `name`: obligatorio, string, 2-120 caracteres.
  - `email`: obligatorio, formato email valido, max 190, unico.
  - `password`: obligatorio, 8-72 caracteres.
- `POST /auth/login`
  - `email`: obligatorio, formato valido.
  - `password`: obligatorio.
- Middleware/token
  - header `Authorization` obligatorio en rutas protegidas.
  - formato `Bearer` obligatorio.
  - token no revocado y no expirado.
- `PUT /users/me`
  - `name` opcional con mismas reglas de longitud.
  - `password` opcional con mismas reglas de seguridad.
- Ownership
  - id de recurso siempre validado contra `auth_user_id`.

### 8) Criterios de simplicidad (regla DAW aplicada)
- No usar refresh tokens en FASE 2.
- No usar RBAC/permisos avanzados en FASE 2.
- No añadir capas adicionales si no aportan valor directo al alcance obligatorio.
- Priorizar consultas SQL y flujos directos, con mensajes de error claros.
- Mantener un maximo de responsabilidad clara por archivo (controlador, repositorio, middleware, util).

## Riesgos detectados
- Riesgo medio: desviacion de alcance en FASE 2 (anadir extras como refresh tokens o permisos avanzados).
- Riesgo medio: inconsistencias de validacion entre endpoints si no se centralizan reglas basicas.
- Riesgo medio: fallos de ownership si no se filtra siempre por `auth_user_id` en recursos de usuario.
- Riesgo bajo-medio: sesiones no limpiadas si no se revocan tokens correctamente en logout.
- Riesgo medio: desalineacion documental si no se actualiza `handoff.md` al cerrar cada hito.

## Problemas encontrados
- Error reportado en entorno real: `strict_types declaration must be the very first statement` en `backend/public/index.php`.
- Causa: archivo con BOM UTF-8 al inicio.
- Correccion aplicada: reescritura de `backend/public/index.php` en UTF-8 sin BOM y estructura valida de cabecera.
- Nuevo error reportado en entorno real: mismo problema en `backend/src/Config/Env.php`.
- Causa raiz confirmada: inconsistencia de cabecera/codificacion en multiples archivos PHP.
- Correccion definitiva: normalizacion global de cabeceras y codificacion en todo `backend`.
- Error reportado en prueba de `POST /api/v1/auth/register`: `500` con warning en `backend/routes/api.php` linea 13.
- Causa: `use Throwable;` en archivo global (sin `namespace`) genera warning en cada request (`The use statement with non-compound name 'Throwable' has no effect`).
- Correccion aplicada: eliminar ese `use` y usar `catch (\Throwable)` de forma explicita.
- Nuevo error reportado en prueba de `POST /api/v1/auth/register`: `500 Internal Server Error` con endpoint `health` funcionando.
- Causa raiz confirmada: falta de `backend/.env` + credencial por defecto no valida (`DB_PASSWORD` vacio), provocando fallo de conexion en `backend/src/Config/Database.php:46` (excepcion previa `PDOException` en linea 40).
- Correccion aplicada: crear `backend/.env` local y configurar credenciales reales de MySQL.
- Estado actual: sin bloqueos tecnicos para iniciar FASE 2 tras aprobar el plan.

## Proximos pasos recomendados
1. Implementar `POST /api/v1/auth/logout` con revocacion real de token.
2. Implementar `PUT /api/v1/users/me` para actualizacion de perfil basico.
3. Ejecutar prueba manual completa del flujo: register -> login -> users/me -> logout -> acceso denegado.
