import { env } from '../config/env'

const DEFAULT_HEADERS = {
  Accept: 'application/json',
}

async function request(path, options = {}) {
  const cleanPath = path.startsWith('/') ? path : `/${path}`
  const url = `${env.apiBaseUrl}${cleanPath}`
  const hasBody = options.body !== undefined

  const response = await fetch(url, {
    ...options,
    headers: {
      ...DEFAULT_HEADERS,
      ...(hasBody ? { 'Content-Type': 'application/json' } : {}),
      ...(options.headers ?? {}),
    },
  })

  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    const message = payload?.error?.message ?? 'Request failed'
    throw new Error(message)
  }

  return payload
}

export const httpClient = {
  get: (path, options = {}) => request(path, { ...options, method: 'GET' }),
  post: (path, body, options = {}) =>
    request(path, {
      ...options,
      method: 'POST',
      body: JSON.stringify(body),
    }),
  put: (path, body, options = {}) =>
    request(path, {
      ...options,
      method: 'PUT',
      body: JSON.stringify(body),
    }),
  patch: (path, body, options = {}) =>
    request(path, {
      ...options,
      method: 'PATCH',
      body: JSON.stringify(body),
    }),
  delete: (path, options = {}) => request(path, { ...options, method: 'DELETE' }),
}
