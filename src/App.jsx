import { useEffect, useMemo, useState } from 'react';
import {
  Car,
  CheckCircle2,
  Database,
  Edit3,
  Plus,
  RefreshCw,
  Save,
  Search,
  Trash2,
  X,
} from 'lucide-react';
import { vehicleApi } from './services/vehicleApi.js';

const emptyVehicle = {
  plate: '',
  brand: '',
  model: '',
  year: new Date().getFullYear(),
  vin: '',
  ownerName: '',
  status: 'Activo',
  notes: '',
};

const demoVehicles = [
  {
    id: 1,
    plate: '1234-LMX',
    brand: 'Toyota',
    model: 'Corolla',
    year: 2020,
    vin: 'JTDBR32E520000001',
    ownerName: 'Cliente demo',
    status: 'Activo',
    notes: 'Revision anual completada.',
  },
  {
    id: 2,
    plate: '8871-KPG',
    brand: 'Seat',
    model: 'Leon',
    year: 2018,
    vin: 'VSSZZZ5FZJR000002',
    ownerName: 'Empresa Norte',
    status: 'Pendiente',
    notes: 'Documentacion pendiente de validar.',
  },
];

function normalizeVehicle(vehicle) {
  return {
    ...vehicle,
    year: Number(vehicle.year),
    plate: vehicle.plate.trim().toUpperCase(),
    vin: vehicle.vin.trim().toUpperCase(),
    brand: vehicle.brand.trim(),
    model: vehicle.model.trim(),
    ownerName: vehicle.ownerName.trim(),
    notes: vehicle.notes.trim(),
  };
}

export default function App() {
  const [vehicles, setVehicles] = useState([]);
  const [form, setForm] = useState(emptyVehicle);
  const [editingId, setEditingId] = useState(null);
  const [query, setQuery] = useState('');
  const [apiStatus, setApiStatus] = useState('checking');
  const [message, setMessage] = useState('');

  const filteredVehicles = useMemo(() => {
    const normalizedQuery = query.trim().toLowerCase();

    if (!normalizedQuery) {
      return vehicles;
    }

    return vehicles.filter((vehicle) =>
      [vehicle.plate, vehicle.brand, vehicle.model, vehicle.vin, vehicle.ownerName]
        .join(' ')
        .toLowerCase()
        .includes(normalizedQuery),
    );
  }, [query, vehicles]);

  const stats = useMemo(() => {
    const active = vehicles.filter((vehicle) => vehicle.status === 'Activo').length;
    const pending = vehicles.filter((vehicle) => vehicle.status === 'Pendiente').length;

    return { active, pending, total: vehicles.length };
  }, [vehicles]);

  useEffect(() => {
    loadVehicles();
  }, []);

  async function loadVehicles() {
    setApiStatus('checking');
    setMessage('');

    try {
      const data = await vehicleApi.list();
      setVehicles(data);
      setApiStatus('connected');
      setMessage('Datos cargados desde la API.');
    } catch (error) {
      setVehicles(demoVehicles);
      setApiStatus('demo');
      setMessage('API no disponible. Trabajando con datos de ejemplo.');
    }
  }

  function handleChange(event) {
    const { name, value } = event.target;
    setForm((currentForm) => ({
      ...currentForm,
      [name]: value,
    }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    const vehicle = normalizeVehicle(form);

    if (!vehicle.plate || !vehicle.brand || !vehicle.model || !vehicle.ownerName) {
      setMessage('Completa matricula, marca, modelo y propietario.');
      return;
    }

    if (editingId) {
      await saveExistingVehicle(editingId, vehicle);
      return;
    }

    await saveNewVehicle(vehicle);
  }

  async function saveNewVehicle(vehicle) {
    if (apiStatus === 'connected') {
      try {
        const createdVehicle = await vehicleApi.create(vehicle);
        setVehicles((currentVehicles) => [createdVehicle, ...currentVehicles]);
        resetForm();
        setMessage('Vehiculo registrado en la API.');
        return;
      } catch (error) {
        setApiStatus('demo');
        setMessage('No se pudo guardar en la API. Guardado temporal en pantalla.');
      }
    }

    setVehicles((currentVehicles) => [{ ...vehicle, id: Date.now() }, ...currentVehicles]);
    resetForm();
  }

  async function saveExistingVehicle(id, vehicle) {
    if (apiStatus === 'connected') {
      try {
        const updatedVehicle = await vehicleApi.update(id, vehicle);
        setVehicles((currentVehicles) =>
          currentVehicles.map((currentVehicle) =>
            currentVehicle.id === id ? updatedVehicle : currentVehicle,
          ),
        );
        resetForm();
        setMessage('Vehiculo actualizado en la API.');
        return;
      } catch (error) {
        setApiStatus('demo');
        setMessage('No se pudo actualizar en la API. Cambio temporal en pantalla.');
      }
    }

    setVehicles((currentVehicles) =>
      currentVehicles.map((currentVehicle) =>
        currentVehicle.id === id ? { ...vehicle, id } : currentVehicle,
      ),
    );
    resetForm();
  }

  async function handleDelete(vehicleId) {
    if (apiStatus === 'connected') {
      try {
        await vehicleApi.remove(vehicleId);
        setVehicles((currentVehicles) =>
          currentVehicles.filter((vehicle) => vehicle.id !== vehicleId),
        );
        setMessage('Vehiculo eliminado de la API.');
        return;
      } catch (error) {
        setApiStatus('demo');
        setMessage('No se pudo eliminar en la API. Eliminado temporal en pantalla.');
      }
    }

    setVehicles((currentVehicles) => currentVehicles.filter((vehicle) => vehicle.id !== vehicleId));
  }

  function startEditing(vehicle) {
    setEditingId(vehicle.id);
    setForm({
      plate: vehicle.plate,
      brand: vehicle.brand,
      model: vehicle.model,
      year: vehicle.year,
      vin: vehicle.vin,
      ownerName: vehicle.ownerName,
      status: vehicle.status,
      notes: vehicle.notes,
    });
    setMessage(`Editando ${vehicle.plate}.`);
  }

  function resetForm() {
    setForm(emptyVehicle);
    setEditingId(null);
  }

  return (
    <main className="app-shell">
      <section className="workspace">
        <header className="topbar">
          <div>
            <span className="eyebrow">TFG DAW</span>
            <h1>MechData</h1>
            <p>Registro web de vehiculos preparado para API Spring y persistencia en MySQL.</p>
          </div>
          <div className={`connection ${apiStatus}`}>
            <Database size={18} aria-hidden="true" />
            <span>{apiStatus === 'connected' ? 'API conectada' : 'Modo demo'}</span>
          </div>
        </header>

        <section className="stats-grid" aria-label="Resumen del registro">
          <article>
            <span>Total</span>
            <strong>{stats.total}</strong>
          </article>
          <article>
            <span>Activos</span>
            <strong>{stats.active}</strong>
          </article>
          <article>
            <span>Pendientes</span>
            <strong>{stats.pending}</strong>
          </article>
        </section>

        <section className="content-grid">
          <form className="vehicle-form" onSubmit={handleSubmit}>
            <div className="section-heading">
              <div>
                <span className="eyebrow">Ficha tecnica</span>
                <h2>{editingId ? 'Editar vehiculo' : 'Nuevo vehiculo'}</h2>
              </div>
              {editingId && (
                <button type="button" className="icon-button secondary" onClick={resetForm}>
                  <X size={18} aria-hidden="true" />
                  <span>Cancelar</span>
                </button>
              )}
            </div>

            <div className="form-grid">
              <label>
                Matricula
                <input
                  name="plate"
                  value={form.plate}
                  onChange={handleChange}
                  placeholder="1234-ABC"
                  required
                />
              </label>
              <label>
                Marca
                <input
                  name="brand"
                  value={form.brand}
                  onChange={handleChange}
                  placeholder="Toyota"
                  required
                />
              </label>
              <label>
                Modelo
                <input
                  name="model"
                  value={form.model}
                  onChange={handleChange}
                  placeholder="Corolla"
                  required
                />
              </label>
              <label>
                Año
                <input
                  name="year"
                  type="number"
                  min="1950"
                  max="2100"
                  value={form.year}
                  onChange={handleChange}
                />
              </label>
              <label>
                Bastidor VIN
                <input
                  name="vin"
                  value={form.vin}
                  onChange={handleChange}
                  placeholder="VSSZZZ..."
                />
              </label>
              <label>
                Propietario
                <input
                  name="ownerName"
                  value={form.ownerName}
                  onChange={handleChange}
                  placeholder="Nombre o empresa"
                  required
                />
              </label>
              <label>
                Estado
                <select name="status" value={form.status} onChange={handleChange}>
                  <option>Activo</option>
                  <option>Pendiente</option>
                  <option>Archivado</option>
                </select>
              </label>
              <label className="wide-field">
                Observaciones
                <textarea
                  name="notes"
                  value={form.notes}
                  onChange={handleChange}
                  placeholder="Seguro, ITV, documentacion o incidencias."
                  rows="4"
                />
              </label>
            </div>

            <button type="submit" className="primary-action">
              {editingId ? <Save size={18} aria-hidden="true" /> : <Plus size={18} aria-hidden="true" />}
              <span>{editingId ? 'Guardar cambios' : 'Registrar vehiculo'}</span>
            </button>
          </form>

          <section className="vehicle-panel">
            <div className="section-heading">
              <div>
                <span className="eyebrow">Inventario</span>
                <h2>Vehiculos registrados</h2>
              </div>
              <button type="button" className="icon-button" onClick={loadVehicles}>
                <RefreshCw size={18} aria-hidden="true" />
                <span>Actualizar</span>
              </button>
            </div>

            <label className="search-box">
              <Search size={18} aria-hidden="true" />
              <input
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                placeholder="Buscar por matricula, marca, VIN o propietario"
              />
            </label>

            {message && (
              <div className="message" role="status">
                <CheckCircle2 size={18} aria-hidden="true" />
                <span>{message}</span>
              </div>
            )}

            <div className="vehicle-list">
              {filteredVehicles.map((vehicle) => (
                <article className="vehicle-card" key={vehicle.id}>
                  <div className="vehicle-main">
                    <div className="vehicle-icon">
                      <Car size={22} aria-hidden="true" />
                    </div>
                    <div>
                      <h3>{vehicle.plate}</h3>
                      <p>
                        {vehicle.brand} {vehicle.model} · {vehicle.year}
                      </p>
                    </div>
                  </div>
                  <dl>
                    <div>
                      <dt>Propietario</dt>
                      <dd>{vehicle.ownerName}</dd>
                    </div>
                    <div>
                      <dt>VIN</dt>
                      <dd>{vehicle.vin || 'Sin registrar'}</dd>
                    </div>
                    <div>
                      <dt>Estado</dt>
                      <dd>
                        <span className={`status-pill ${vehicle.status.toLowerCase()}`}>
                          {vehicle.status}
                        </span>
                      </dd>
                    </div>
                  </dl>
                  {vehicle.notes && <p className="notes">{vehicle.notes}</p>}
                  <div className="card-actions">
                    <button type="button" onClick={() => startEditing(vehicle)}>
                      <Edit3 size={16} aria-hidden="true" />
                      <span>Editar</span>
                    </button>
                    <button type="button" className="danger" onClick={() => handleDelete(vehicle.id)}>
                      <Trash2 size={16} aria-hidden="true" />
                      <span>Eliminar</span>
                    </button>
                  </div>
                </article>
              ))}
            </div>
          </section>
        </section>
      </section>
    </main>
  );
}
