export function AppShell({ title, children }) {
  return (
    <div className="min-vh-100 bg-light">
      <header className="border-bottom bg-white">
        <div className="container py-3">
          <h1 className="h4 m-0">{title}</h1>
        </div>
      </header>
      <main className="container py-4">{children}</main>
    </div>
  )
}
