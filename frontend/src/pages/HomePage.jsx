import { useAppConfig } from '../hooks/useAppConfig'

export function HomePage() {
  const { appName } = useAppConfig()

  return (
    <section className="card shadow-sm">
      <div className="card-body">
        <h2 className="h5">FASE 1 - Entorno preparado</h2>
        <p className="mb-0 text-secondary">
          {appName} tiene el frontend base configurado con React, Bootstrap y cliente HTTP.
        </p>
      </div>
    </section>
  )
}
