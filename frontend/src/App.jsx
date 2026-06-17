import { AppShell } from './components/layout/AppShell'
import { HomePage } from './pages/HomePage'
import { useAppConfig } from './hooks/useAppConfig'

function App() {
  const { appName } = useAppConfig()

  return (
    <AppShell title={appName}>
      <HomePage />
    </AppShell>
  )
}

export default App
