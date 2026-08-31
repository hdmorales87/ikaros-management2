import { FormEvent, ReactNode, useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { publicApi } from './api'

function PublicShell({ children }: { children: ReactNode }) { return <main className="auth-shell"><section className="auth-card"><div className="brand">IKAROS / MANAGEMENT</div>{children}</section></main> }

export function PasswordPage() {
  const { token = '', user = '', opcion = '', uuid = '' } = useParams()
  const [password, setPassword] = useState(''); const [confirmation, setConfirmation] = useState(''); const [message, setMessage] = useState('')
  async function submit(event: FormEvent) { event.preventDefault(); if (password !== confirmation) { setMessage('Las contraseñas no coinciden.'); return } try { await publicApi.updatePassword({ email: atob(user), token, opcion: atob(opcion), password }, atob(uuid)); setMessage('Contraseña actualizada correctamente.') } catch { setMessage('No fue posible actualizar la contraseña.') } }
  return <PublicShell><h1>Activar cuenta</h1><p className="muted">Define una contraseña para continuar.</p><form onSubmit={submit}><label className="field">Nueva contraseña<input type="password" minLength={6} value={password} onChange={(event) => setPassword(event.target.value)} required /></label><label className="field">Confirmar contraseña<input type="password" minLength={6} value={confirmation} onChange={(event) => setConfirmation(event.target.value)} required /></label>{message && <p className="muted" role="status">{message}</p>}<button className="primary">Guardar contraseña</button></form></PublicShell>
}

export function SatisfactionPage() {
  const { idSolicitud = '0', tabla = '', uuid = '' } = useParams(); const [questions, setQuestions] = useState<{ id: number; nombre: string }[]>([]); const [answers, setAnswers] = useState<Record<string, number | string>>({}); const [message, setMessage] = useState('')
  useEffect(() => { publicApi.surveyStatus(tabla, Number(idSolicitud), uuid).then((data) => { if (Array.isArray(data.msg)) setQuestions(data.msg) }).catch(() => setMessage('No fue posible cargar la encuesta.')) }, [idSolicitud, tabla, uuid])
  async function submit(event: FormEvent) { event.preventDefault(); try { await publicApi.saveSurvey({ tabla, idSolicitud: Number(idSolicitud), jsonRespuestas: JSON.stringify({ 0: answers[0] || '', ...answers }) }, uuid); setMessage('Gracias por completar la encuesta.') } catch { setMessage('No fue posible guardar la encuesta.') } }
  return <PublicShell><h1>Encuesta de satisfacción</h1><form onSubmit={submit}>{questions.map((question) => <label className="field" key={question.id}>{question.nombre}<select value={answers[question.id] || ''} onChange={(event) => setAnswers({ ...answers, [question.id]: Number(event.target.value) })} required><option value="">Selecciona una calificación</option>{[1, 2, 3, 4, 5].map((score) => <option key={score} value={score}>{score}</option>)}</select></label>)}<label className="field">Comentarios<textarea rows={4} value={String(answers[0] || '')} onChange={(event) => setAnswers({ ...answers, 0: event.target.value })} /></label>{message && <p className="muted" role="status">{message}</p>}<button className="primary">Enviar encuesta</button></form></PublicShell>
}

export function RejectPage() {
  const { idSolicitud = '0', tabla = '', idUsuario = '0', uuid = '' } = useParams(); const [observation, setObservation] = useState(''); const [message, setMessage] = useState('')
  async function submit(event: FormEvent) { event.preventDefault(); try { await publicApi.reject({ idRow: Number(idSolicitud), tabla, idUser: Number(idUsuario), observacion: observation }, uuid); setMessage('La solución fue rechazada.') } catch { setMessage('No fue posible rechazar la solución.') } }
  return <PublicShell><h1>Rechazar solución</h1><p className="muted">Indica por qué la solución requiere revisión.</p><form onSubmit={submit}><label className="field">Observación<textarea rows={6} value={observation} onChange={(event) => setObservation(event.target.value)} required /></label>{message && <p className="muted" role="status">{message}</p>}<button className="primary">Rechazar solución</button></form></PublicShell>
}

export function ClientSurveyPage() {
  const { idCliente = '0', uuid = '' } = useParams(); const [questions, setQuestions] = useState<{ id: number; nombre: string }[]>([]); const [answers, setAnswers] = useState<Record<string, number | string>>({}); const [message, setMessage] = useState('')
  useEffect(() => { publicApi.clientQuestions(uuid).then(setQuestions).catch(() => setMessage('No fue posible cargar la encuesta.')) }, [uuid])
  async function submit(event: FormEvent) { event.preventDefault(); try { await publicApi.saveClientSurvey({ idTercero: Number(idCliente), opcion: 'cliente', jsonRespuestas: JSON.stringify({ 0: answers[0] || '', ...answers }) }, uuid); setMessage('Gracias por completar la encuesta.') } catch { setMessage('No fue posible guardar la encuesta.') } }
  return <PublicShell><h1>Encuesta de cliente</h1><form onSubmit={submit}>{questions.map((question) => <label className="field" key={question.id}>{question.nombre}<select value={answers[question.id] || ''} onChange={(event) => setAnswers({ ...answers, [question.id]: Number(event.target.value) })} required><option value="">Selecciona una calificación</option>{[1, 2, 3, 4, 5].map((score) => <option key={score} value={score}>{score}</option>)}</select></label>)}<label className="field">Comentarios<textarea rows={4} value={String(answers[0] || '')} onChange={(event) => setAnswers({ ...answers, 0: event.target.value })} /></label>{message && <p className="muted" role="status">{message}</p>}<button className="primary">Enviar encuesta</button></form></PublicShell>
}
