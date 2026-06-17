import { useContext } from 'react'
import { AppContext } from './appContextDefinition'

export function useAppContext() {
  return useContext(AppContext)
}
