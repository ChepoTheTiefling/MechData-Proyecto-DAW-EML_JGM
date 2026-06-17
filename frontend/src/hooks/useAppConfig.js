import { useAppContext } from '../context/useAppContext'

export function useAppConfig() {
  const { appName } = useAppContext()
  return { appName }
}
