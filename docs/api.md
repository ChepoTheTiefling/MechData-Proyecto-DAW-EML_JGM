# Contrato API REST (FASE 0)

## Convenciones generales
- Base URL prevista: `/api/v1`
- Formato: `application/json`
- Auth: `Authorization: Bearer <token>`
- Fechas: ISO 8601 (`YYYY-MM-DD` o `YYYY-MM-DDTHH:MM:SSZ` segun caso)

## Estructura de respuesta
- Exito:
```json
{ "success": true, "data": {}, "message": "optional" }
```
- Error:
```json
{ "success": false, "error": { "code": "VALIDATION_ERROR", "message": "...", "details": [] } }
```

## Codigos HTTP
- `200` OK
- `201` Created
- `204` No Content
- `400` Bad Request
- `401` Unauthorized
- `403` Forbidden
- `404` Not Found
- `409` Conflict
- `422` Unprocessable Entity
- `500` Internal Server Error

## Endpoints por modulo

### Salud API
- `GET /health`
  - Uso: comprobar disponibilidad del backend.

### Usuarios y autenticacion
- `POST /auth/register`
  - Body: `name`, `email`, `password`.
- `POST /auth/login`
  - Body: `email`, `password`.
  - Respuesta: token y datos basicos de usuario.
- `POST /auth/logout`
  - Requiere token.
  - Invalida token actual.
- `GET /users/me`
  - Perfil del usuario autenticado.
- `PUT /users/me`
  - Actualiza perfil basico (`name`, opcional password con validacion).

## Flujo completo de autenticacion por token (diseno previo a implementacion)

### 1. Registro (`POST /auth/register`)
- Validar `name`, `email` y `password` en backend.
- Verificar unicidad de email.
- Guardar password con `password_hash()` (nunca en texto plano).
- No crear token automaticamente en registro (login explicito para simplificar trazabilidad).
- Respuesta esperada: `201 Created`.

### 2. Login (`POST /auth/login`)
- Buscar usuario por email.
- Verificar password con `password_verify()`.
- Si credenciales validas:
  - Generar token en bruto con `random_bytes()` (o equivalente criptograficamente seguro).
  - Serializar token para transporte HTTP (hex/base64url).
  - Calcular `token_hash` con SHA-256 del token en bruto.
  - Guardar solo `token_hash` en `auth_tokens` junto con `user_id`, `expires_at`, `created_at`, `revoked_at = NULL`.
  - Devolver al cliente solo el token en bruto (una unica vez) y datos basicos de usuario.
- Si credenciales invalidas: `401 Unauthorized`.

### 3. Uso del token en rutas protegidas
- Cliente envia `Authorization: Bearer <token>`.
- Middleware de autenticacion:
  - Extrae token del header.
  - Calcula hash SHA-256 del token recibido.
  - Busca coincidencia en `auth_tokens.token_hash`.
  - Verifica:
    - `revoked_at IS NULL`
    - `expires_at > NOW()`
  - Si es valido, carga contexto de usuario autenticado (`auth_user_id`).
  - Si falla cualquier validacion, responder `401 Unauthorized`.

### 4. Control de ownership (autorizacion por recurso)
- En rutas de negocio, cada consulta debe filtrar por `auth_user_id`.
- Reglas:
  - Vehiculos: solo `vehicles.user_id = auth_user_id`.
  - Mantenimientos: solo si el `vehicle_id` pertenece al usuario autenticado.
  - Carrito/pedidos: solo recursos del propio usuario.
- Recomendacion de respuesta para recurso ajeno/no existente: `404 Not Found` para evitar enumeracion.

### 5. Expiracion de token
- Cada token tiene `expires_at` obligatorio.
- Token expirado se trata como invalido (`401`).
- Renovacion: usuario vuelve a `POST /auth/login` para obtener un token nuevo.

### 6. Logout real (revocacion)
- Endpoint: `POST /auth/logout` autenticado.
- Accion: marcar token actual con `revoked_at = NOW()`.
- Resultado: el token deja de ser util inmediatamente en middleware.

### 7. Reglas de seguridad obligatorias
- `password_hash()` para almacenamiento de contrasenas.
- `password_verify()` para validacion.
- `random_bytes()` (o equivalente seguro) para generacion de token.
- No persistir tokens en texto plano.
- Persistir unicamente `token_hash` + metadatos (`expires_at`, `revoked_at`, `last_used_at` opcional).
- Middleware obligatorio en todas las rutas protegidas.
- Control de ownership obligatorio en acceso a recursos de usuario.

### Vehiculos (garaje)
- `GET /vehicles`
  - Lista vehiculos del usuario autenticado.
- `POST /vehicles`
  - Crea vehiculo.
  - Body: `brand`, `model`, `year`, `plate`, `current_km`, `image_url`.
- `GET /vehicles/{id}`
  - Detalle de vehiculo propio.
- `PUT /vehicles/{id}`
  - Edita vehiculo propio.
- `DELETE /vehicles/{id}`
  - Elimina vehiculo propio.

### Mantenimientos
- `GET /maintenance-types`
  - Lista catalogo de tipos.
- `POST /maintenance-types` (opcional admin en futuro)
  - Crea tipo de mantenimiento.
- `GET /vehicles/{vehicleId}/maintenances`
  - Lista mantenimientos configurados de un vehiculo.
- `POST /vehicles/{vehicleId}/maintenances`
  - Crea/configura mantenimiento por tipo.
  - Body: `maintenance_type_id`, `last_change_date`, `last_change_km`, `next_change_date`, `next_change_km`, `custom_interval_km`, `custom_interval_months`, `notes`.
- `PUT /vehicles/{vehicleId}/maintenances/{id}`
  - Actualiza mantenimiento.
- `DELETE /vehicles/{vehicleId}/maintenances/{id}`
  - Elimina mantenimiento.

### Categorias de producto
- `GET /product-categories`
  - Lista categorias disponibles para clasificar/filtrar productos.

### Productos
- `GET /products`
  - Lista catalogo, filtros por `category_id`, `search`, paginacion.
- `GET /products/{id}`
  - Detalle producto.

### Carrito
- `GET /cart`
  - Obtiene carrito activo y lineas.
- `POST /cart/items`
  - Anade producto al carrito.
  - Body: `product_id`, `quantity`.
- `PUT /cart/items/{itemId}`
  - Modifica cantidad.
- `DELETE /cart/items/{itemId}`
  - Elimina linea.
- `DELETE /cart/items`
  - Vacia carrito activo.

### Pedidos
- `POST /orders/checkout`
  - Convierte carrito activo en pedido.
  - Valida stock y congela precios.
- `GET /orders`
  - Lista pedidos del usuario autenticado.
- `GET /orders/{id}`
  - Detalle de pedido propio.

## Reglas de seguridad minimas
- Hash de password con `password_hash()` (bcrypt/argon2 segun disponibilidad).
- Verificacion de credenciales con `password_verify()`.
- Generacion de token con `random_bytes()` (o equivalente criptograficamente seguro).
- Tokens guardados como hash (no en texto plano).
- Persistencia de `token_hash` unicamente (sin token en claro).
- Expiracion de token por `expires_at`.
- Revocacion en logout por `revoked_at`.
- Middleware de autenticacion en rutas protegidas.
- Control de ownership por recurso (`user_id`).
- Validacion de entrada server-side en todos los endpoints.
- Respuestas de error sin filtrar detalles sensibles.

## Notas de fase
- Este documento define contrato objetivo. Implementacion se realiza desde FASE 2.
