function normalizeBaseUrl(url) {
  return url.endsWith('/') ? url.slice(0, -1) : url
}

export const env = {
  apiBaseUrl: normalizeBaseUrl(import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1'),
  appName: import.meta.env.VITE_APP_NAME ?? 'Garage Manager TFG',
}
