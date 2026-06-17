# Roadmap del proyecto

## Enfoque general
- Objetivo prioritario: entregar una version funcional end-to-end antes de anadir extras.
- Regla de oro: ninguna funcionalidad opcional puede bloquear funcionalidades obligatorias.
- Metodo: fases secuenciales con criterio de salida (Definition of Done) por fase.
- Regla de implementacion: codigo al nivel de un TFG DAW mantenible por dos estudiantes (simple, legible y explicable).

## Estado por fases

| Fase | Alcance | Estado | Criterio de salida |
| --- | --- | --- | --- |
| FASE 0 | Analisis, arquitectura, modelo de datos, endpoints, estructura | Completada | Documentos tecnicos base y schema inicial coherentes |
| FASE 1 | Configuracion de proyecto, Git/GitHub, base de datos inicial | Completada | Entorno validado: PHP, Composer, MySQL, schema.sql y `/api/v1/health` |
| FASE 2 | Usuarios y autenticacion | En curso | Registro/login/logout/perfil funcionando en API + UI minima |
| FASE 3 | Garaje de vehiculos | Pendiente | CRUD de vehiculos por usuario autenticado |
| FASE 4 | Mantenimientos | Pendiente | CRUD de mantenimientos por vehiculo + calculo proximo cambio |
| FASE 5 | Productos | Pendiente | Catalogo de productos consultable |
| FASE 6 | Carrito | Pendiente | Carrito funcional con total y control de stock |
| FASE 7 | Pedidos | Pendiente | Confirmacion de compra y persistencia de pedido |
| FASE 8 | Testing, correccion y memoria tecnica final | Pendiente | Pruebas minimas, bugfix y documentacion final cerrada |

## Alcance funcional obligatorio
- Usuarios: registro, login, logout, perfil basico.
- Garaje: alta, edicion, baja, consulta de vehiculos.
- Mantenimiento: configuracion por vehiculo con intervalos editables.
- Productos: catalogo propio en base de datos.
- Carrito: anadir, eliminar, cambiar cantidades y calcular total.
- Pedidos: simulacion de compra sin pasarela de pago.

## Decisiones cerradas en FASE 0
- Matricula unica global.
- Categorias de producto mediante tabla dedicada (`product_categories`).
- Borrado restringido cuando exista historico relacionado.

## Avance real de FASE 1
- Backend base creado (estructura, configuracion, rutas y entrypoint).
- Frontend React + Bootstrap creado con cliente HTTP base y entorno.
- Validacion real completada de entorno: PHP, Composer y MySQL.
- `database/schema.sql` ejecutado correctamente en MySQL 8 local.
- Endpoint `GET /api/v1/health` validado en entorno real.

## Criterios de priorizacion
- Prioridad 1: flujo funcional completo (auth -> vehiculo -> mantenimiento -> carrito -> pedido).
- Prioridad 2: robustez minima (validaciones, manejo de errores, seguridad basica).
- Prioridad 3: mejoras UX y extras no obligatorios.

## Riesgos de planificacion detectados
- Tiempo limitado para dos personas: riesgo alto de dispersion.
- Dependencia frontend/backend: riesgo medio de bloqueos por contrato API no definido.
- Cambios de requisitos durante desarrollo: riesgo medio-alto.

## Mitigacion
- Congelar alcance obligatorio por fase.
- Definir contrato API antes de codificar frontend.
- Cerrar cada fase con revision tecnica y actualizacion documental.

## Proxima fase propuesta
- Revisar y aprobar el plan tecnico detallado de FASE 2 (usuarios y autenticacion) antes de implementar codigo.
