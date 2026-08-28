import { Component, ErrorInfo, ReactNode } from 'react'

type Props = { children: ReactNode }
type State = { hasError: boolean }

export default class AppErrorBoundary extends Component<Props, State> {
  state: State = { hasError: false }

  static getDerivedStateFromError(): State {
    return { hasError: true }
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('Frontend error', error, info.componentStack)
  }

  render() {
    if (!this.state.hasError) return this.props.children
    return <main className="error-shell"><section className="panel"><h1>Algo salió mal</h1><p className="muted">No fue posible cargar esta sección.</p><button className="primary" onClick={() => window.location.reload()}>Recargar aplicación</button></section></main>
  }
}
