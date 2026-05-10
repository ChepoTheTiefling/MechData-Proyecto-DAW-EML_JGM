const API_URL = import.meta.env.VITE_API_URL || '/api';

async function request(path, options = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  });

  if (!response.ok) {
    throw new Error(`Error ${response.status}: ${response.statusText}`);
  }

  if (response.status === 204) {
    return null;
  }

  return response.json();
}

export const vehicleApi = {
  list() {
    return request('/vehicles');
  },
  create(vehicle) {
    return request('/vehicles', {
      method: 'POST',
      body: JSON.stringify(vehicle),
    });
  },
  update(id, vehicle) {
    return request(`/vehicles/${id}`, {
      method: 'PUT',
      body: JSON.stringify(vehicle),
    });
  },
  remove(id) {
    return request(`/vehicles/${id}`, {
      method: 'DELETE',
    });
  },
};
