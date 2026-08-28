import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useParams } from 'react-router-dom'
import { getGridRows, gridApi, GridRow } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'
import TechnicalSheetPanel from '../technical-sheets/TechnicalSheetPanel'

export default function AssetDetailPage() {
  const { id = '0' } = useParams(); const queryClient = useQueryClient(); const [message, setMessage] = useState(''); const asset = useQuery({ queryKey: ['asset', id], queryFn: () => getGridRows('activos', '', { id: Number(id) }) }); const row = asset.data?.[0]; const [form, setForm] = useState<Record<string, string>>({})
  const save = useMutation({ mutationFn: () => gridApi.update('activos', { id: Number(id), ...form }), onSuccess: () => { setMessage('Activo actualizado.'); queryClient.invalidateQueries({ queryKey: ['asset', id] }) }, onError: () => setMessage('No fue posible actualizar el activo.') })
  function submit(event: FormEvent) { event.preventDefault(); save.mutate() }
  if (!row) return <section className="panel"><p className="muted">{asset.isLoading ? 'Cargando activo...' : 'Activo no encontrado.'}</p></section>
  return <section><div className="topbar"><div><h1>Activo #{id}</h1><p className="muted">Detalle y edición del activo.</p></div></div><div className="panel"><form onSubmit={submit}><div className="form-grid">{['nombre', 'marca', 'id_departamento', 'id_ubicacion'].map((field) => <label className="field" key={field}>{field}<input value={form[field] ?? String(row[field] ?? '')} onChange={(event) => setForm({ ...form, [field]: event.target.value })} /></label>)}</div>{message && <p className="muted" role="status">{message}</p>}<button className="primary" disabled={save.isPending}>Guardar activo</button></form></div><TechnicalSheetPanel tabla="activos" idMaestro={Number(id)} /><AttachmentPanel table="activos" id={Number(id)} /></section>
}