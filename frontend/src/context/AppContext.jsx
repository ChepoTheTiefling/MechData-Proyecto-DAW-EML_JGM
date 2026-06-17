import { env } from '../config/env'
import { AppContext } from './appContextDefinition'

export function AppProvider({ children }) {
  return <AppContext.Provider value={{ appName: env.appName }}>{children}</AppContext.Provider>
}
