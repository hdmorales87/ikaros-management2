import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { UsersRound } from 'lucide-react'
import { Link } from 'react-router-dom'
import { getGridRows, gridApi, GridRow } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'

type TrainingForm = {
  nombre: string
  instructor: string
  intensidad: string
  fechaInicio: string
  horaInicio: string
  fechaFinal: string
  horaFinal: string
  lugar: string
  observaciones: string
}

const emptyForm: TrainingForm = { nombre: '', instructor: '', intensidad: '', fechaInicio: '', horaInicio: '', fechaFinal: '', horaFinal: '', lugar: '', observaciones: '' }

export default function TrainingPage() {
  const queryClient = useQueryClient()
  const [form, setForm] = useState<TrainingForm>(emptyForm)
  const [selected, setSelected] = useState<GridRow | null>(null)
  const [message, setMessage] = useState('')
  const trainings = useQuery({ queryKey: ['trainings'], queryFn: () => getGridRows('capacitaciones', '', {}, ['nombre', 'instructor']) })
  const save = useMutation({
    mutationFn: () => {
      const data = { nombre: form.nombre, instructor: form.instructor || null, intensidad: form.intensidad ? Number(form.intensidad) : null, fecha_inicio: form.fechaInicio || null, hora_inicio: form.horaInicio || null, fecha_final: form.fechaFinal || null, hora_final: form.horaFinal || null, lugar: form.lugar || null, observaciones: form.observaciones || null }
      return selected ? gridApi.update('capacitaciones', { ...data, id: selected.id }) : gridApi.insert('capacitaciones', { ...data, activo: 1 })
    },
    onSuccess: () => { setForm(emptyForm); setSelected(null); setMessage('Capacitación guardada.'); queryClient.invalidateQueries({ queryKey: ['trainings'] }) },
    onError: () => setMessage('No fue posible guardar la capacitación.'),
  })
  const deactivate = useMutation({ mutationFn: (id: number) => gridApi.deactivate('capacitaciones', id), onSuccess: () => queryClient.invalidateQueries({ queryKey: ['trainings'] }) })

  function field(name: keyof TrainingForm, value: string) { setForm((current) => ({ ...current, [name]: value })) }
  function edit(item: GridRow) {
    setSelected(item)
    setForm({ nombre: String(item.nombre ?? ''), instructor: String(item.instructor ?? ''), intensidad: String(item.intensidad ?? ''), fechaInicio: String(item.fecha_inicio ?? '').slice(0, 10), horaInicio: String(item.hora_inicio ?? '').slice(0, 5), fechaFinal: String(item.fecha_final ?? '').slice(0, 10), horaFinal: String(item.hora_final ?? '').slice(0, 5), lugar: String(item.lugar ?? ''), observaciones: String(item.observaciones ?? '') })
  }
  function submit(event: FormEvent) {
    event.preventDefault()
    if (form.fechaInicio && form.fechaFinal && (form.fechaFinal < form.fechaInicio || (form.fechaFinal === form.fechaInicio && form.horaInicio && form.horaFinal && form.horaFinal < form.horaInicio))) {
      setMessage('La fecha y hora final deben ser posteriores a las iniciales.')
      return
    }
    save.mutate()
  }

  return <section><div className="topbar"><div><h1>Capacitaciones</h1><p className="muted">Planifica actividades de formación y conserva su evidencia.</p></div></div><div className="panel"><form onSubmit={submit}><div className="form-grid"><label className="field">Nombre<input value={form.nombre} onChange={(event) => field('nombre', event.target.value)} required /></label><label className="field">Instructor<input value={form.instructor} onChange={(event) => field('instructor', event.target.value)} /></label><label className="field">Intensidad (horas)<input type="number" min="1" value={form.intensidad} onChange={(event) => field('intensidad', event.target.value)} /></label><label className="field">Lugar<input value={form.lugar} onChange={(event) => field('lugar', event.target.value)} /></label><label className="field">Inicio<input type="date" value={form.fechaInicio} onChange={(event) => field('fechaInicio', event.target.value)} /></label><label className="field">Hora inicio<input type="time" value={form.horaInicio} onChange={(event) => field('horaInicio', event.target.value)} /></label><label className="field">Fin<input type="date" min={form.fechaInicio || undefined} value={form.fechaFinal} onChange={(event) => field('fechaFinal', event.target.value)} /></label><label className="field">Hora fin<input type="time" value={form.horaFinal} onChange={(event) => field('horaFinal', event.target.value)} /></label><label className="field">Observaciones<textarea value={form.observaciones} onChange={(event) => field('observaciones', event.target.value)} /></label></div>{message && <p className="muted" role="status">{message}</p>}<button className="primary" disabled={save.isPending}>{selected ? 'Guardar cambios' : 'Crear capacitación'}</button>{selected && <button className="secondary" type="button" onClick={() => { setSelected(null); setForm(emptyForm) }}>Cancelar</button>}</form></div><div className="panel"><div className="table-wrap"><table><thead><tr><th>Nombre</th><th>Instructor</th><th>Inicio</th><th>Acciones</th></tr></thead><tbody>{trainings.data?.map((item, index) => <tr key={String(item.id ?? index)}><td>{String(item.nombre ?? '-')}</td><td>{String(item.instructor ?? '-')}</td><td>{String(item.fecha_inicio ?? '-')}</td><td className="training-actions"><Link className="attendees-link" to={`/capacitaciones/${item.id}/asistentes`}><UsersRound size={17} aria-hidden="true" />Asistentes</Link><button className="secondary" type="button" onClick={() => edit(item)}>Editar</button><button className="secondary" type="button" onClick={() => item.id && deactivate.mutate(Number(item.id))} disabled={deactivate.isPending}>Desactivar</button></td></tr>)}</tbody></table></div></div>{selected?.id && <AttachmentPanel table="capacitaciones" id={Number(selected.id)} />}</section>
}
