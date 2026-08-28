import { createContext, ReactNode, useContext, useEffect, useState } from 'react'
import { login as loginRequest, logout as clearSession, Session } from './api'

type AuthContextValue = {
  session: Session | null
  login: (username: string, password: string) => Promise<void>
  logout: () => void
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [session, setSession] = useState<Session | null>(() => {
    const saved = localStorage.getItem('ikaros.session')
    return saved ? JSON.parse(saved) : null
  })

  useEffect(() => {
    const onStorage = () => {
      const saved = localStorage.getItem('ikaros.session')
      setSession(saved ? JSON.parse(saved) : null)
    }
    window.addEventListener('storage', onStorage)
    return () => window.removeEventListener('storage', onStorage)
  }, [])

  async function login(username: string, password: string) {
    const nextSession = await loginRequest(username, password)
    setSession(nextSession)
  }

  function logout() {
    clearSession()
    localStorage.removeItem('ikaros.uuid')
    setSession(null)
  }

  return <AuthContext.Provider value={{ session, login, logout }}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const value = useContext(AuthContext)
  if (!value) throw new Error('useAuth debe usarse dentro de AuthProvider')
  return value
}
