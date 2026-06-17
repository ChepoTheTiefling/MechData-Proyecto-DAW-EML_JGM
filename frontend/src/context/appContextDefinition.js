import { createContext } from 'react'
import { env } from '../config/env'

export const AppContext = createContext({
  appName: env.appName,
})
