# Diseno de base de datos (FASE 0)

## Objetivo
Definir un modelo relacional coherente con el alcance funcional del TFG, priorizando simplicidad de implementacion y trazabilidad academica.

## Supuestos de diseno
- Motor objetivo: MySQL 8.x.
- Zona horaria gestionada por servidor/API.
- Todos los datos de negocio se validan en backend.
- Un usuario puede tener multiples vehiculos.
- Un vehiculo tiene mantenimientos por tipo.
- El catalogo de productos es interno (no integracion externa).
- La compra es simulada (sin pasarela de pago).

## Entidades principales

### `users`
- Guarda cuentas de usuario.
- Claves: `id` PK, `email` UNIQUE.

### `auth_tokens`
- Sesiones de API por token opaco hasheado.
- Permite logout real (revocacion).
- Relacion: N tokens por usuario.
- Seguridad:
  - Solo se almacena `token_hash` (SHA-256 del token en bruto).
  - `expires_at` obligatorio para expiracion.
  - `revoked_at` para invalidacion inmediata en logout.

### `vehicles`
- Vehiculos del garaje por usuario.
- Relacion: N vehiculos por usuario.
- Restriccion: matricula unica global (`plate` unico en toda la aplicacion).

### `maintenance_types`
- Catalogo de tipos de mantenimiento (aceite, filtro, etc.).
- Incluye intervalos por defecto (km/meses).

### `vehicle_maintenances`
- Estado de mantenimiento por vehiculo y tipo.
- Relacion: N mantenimientos por vehiculo.
- Restriccion: unico por (`vehicle_id`, `maintenance_type_id`).

### `product_categories`
- Catalogo cerrado de categorias de producto.
- Relacion: una categoria puede tener multiples productos.

### `products`
- Catalogo de productos de automocion.
- Stock y precio actual.
- Referencia obligatoria a `product_categories`.

### `carts`
- Carrito activo del usuario.
- Se conserva historico de carritos (activo/convertido/abandonado).

### `cart_items`
- Lineas del carrito.
- Guarda `unit_price_snapshot` para consistencia de totales.

### `orders`
- Pedido confirmado (simulacion de compra).
- Total final congelado en el momento de confirmar.

### `order_items`
- Lineas de pedido.
- Guarda `unit_price` y `product_name_snapshot` para historico.

## Relaciones
- `users (1) -> (N) vehicles`
- `users (1) -> (N) auth_tokens`
- `users (1) -> (N) carts`
- `users (1) -> (N) orders`
- `vehicles (1) -> (N) vehicle_maintenances`
- `maintenance_types (1) -> (N) vehicle_maintenances`
- `product_categories (1) -> (N) products`
- `carts (1) -> (N) cart_items`
- `products (1) -> (N) cart_items`
- `orders (1) -> (N) order_items`
- `products (1) -> (N) order_items`

## Reglas de integridad y negocio
- Borrado de usuario restringido si existe historico relacionado (pedido/s asociados).
- Borrado de vehiculo en cascada sobre sus mantenimientos.
- Cantidad en carrito y pedido siempre > 0.
- Precio nunca negativo.
- Stock nunca negativo.
- Solo 1 carrito activo por usuario (indice unico parcial implementado en logica + indice compuesto).
- Las rutas protegidas aplican control de ownership por `user_id` desde backend.

## Indices recomendados
- `users(email)` unico.
- `vehicles(plate)` unico global.
- `vehicle_maintenances(vehicle_id, maintenance_type_id)` unico.
- `product_categories(name)` unico.
- `products(category_id)`.
- `carts(user_id, status)`.
- `orders(user_id, created_at)`.

## Decisiones de diseno cerradas en FASE 0
- Matricula unica global.
- Categorias de producto en tabla dedicada `product_categories`.
- Borrado de usuario restringido cuando exista historico relacionado.

## Archivo SQL asociado
- Esquema base disponible en `database/schema.sql`.
