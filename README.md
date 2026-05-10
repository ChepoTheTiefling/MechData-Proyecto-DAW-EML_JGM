# MechData

MechData es un proyecto TFG para registrar, consultar y editar informacion de vehiculos desde una aplicacion web. La primera version incluye un frontend en React preparado para consumir una API REST desarrollada con Spring Boot, que sera la encargada de persistir los datos en MySQL.

## Modulos relacionados

- Despliegue de aplicaciones
- Desarrollo web en entorno servidor
- Desarrollo backend con Java y Spring
- Acceso a datos con MySQL
- Interfaces web con React
- Posible ampliacion con blockchain/NFT e IA asistente

## Funcionalidades iniciales

- Alta de vehiculos con matricula, marca, modelo, año, VIN, propietario, estado y observaciones.
- Listado y busqueda de vehiculos registrados.
- Edicion y eliminacion desde la interfaz.
- Cliente API configurable para conectar con un backend Spring.
- Modo demo temporal si la API todavia no esta disponible.

## Puesta en marcha

Instala dependencias:

```bash
npm install
```

Arranca el frontend:

```bash
npm run dev
```

La aplicacion se abre por defecto en `http://localhost:5173`.

## Conexion con Spring y MySQL

El frontend no se conecta directamente a MySQL. La conexion correcta es:

```text
React -> API REST Spring Boot -> MySQL
```

Crea un archivo `.env` tomando como referencia `.env.example`:

```bash
VITE_API_URL=http://localhost:8080/api
```

Endpoints esperados por la web:

- `GET /api/vehicles`
- `POST /api/vehicles`
- `PUT /api/vehicles/{id}`
- `DELETE /api/vehicles/{id}`

Modelo JSON esperado:

```json
{
  "id": 1,
  "plate": "1234-LMX",
  "brand": "Toyota",
  "model": "Corolla",
  "year": 2020,
  "vin": "JTDBR32E520000001",
  "ownerName": "Cliente demo",
  "status": "Activo",
  "notes": "Revision anual completada."
}
```

## Seguimiento del TFG

Cada entrega semanal debe apuntar al commit correspondiente y marcarse con un tag:

```bash
git tag entrega-semana-0
git push origin entrega-semana-0
```
