import { FormEvent, useState } from 'react'
import { mailApi } from '../../api'

export default function SmtpPage() {
  const [email, setEmail] = useState(''); const [message, setMessage] = useState(''); const [loading, setLoading] = useState(false)
  async function submit(event: FormEvent) { event.preventDefault(); setLoading(true); setMessage(''); try { await mailApi.checkSmtp(email); setMessage('Prueba SMTP enviada correctamente.') } catch { setMessage('No fue posible enviar la prueba SMTP.') } finally { setLoading(false) } }
  return <section><div className="topbar"><div><h1>Configuración SMTP</h1><p className="muted">Verifica el envío de correo de la organización.</p></div></div><div className="panel"><form onSubmit={submit}><label className="field">Correo de prueba<input type="email" value={email} onChange={(event) => setEmail(event.target.value)} required /></label>{message && <p className="muted" role="status">{message}</p>}<button className="primary" disabled={loading}>{loading ? 'Enviando...' : 'Enviar prueba'}</button></form></div></section>
}