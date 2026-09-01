import { FormEvent, useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useParams } from 'react-router-dom'
import { assetApi } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'
import TechnicalSheetPanel from '../technical-sheets/TechnicalSheetPanel'

type Form = {
  nombre: string
  codigo: string
  marca: string
  tipo: string
  departamento: string
  proveedor: string
  estado: string
  asignado: string
  precio: string
  compra: string
  factura: string
  ubicacion: string
}

const emptyForm: Form = { nombre: '', codigo: '', marca: '', tipo: '', departamento: '', proveedor: '', estado: '', asignado: '', precio: '', compra: '', factura: '', ubicacion: '' }

export default function AssetDetailPage() {
  const { id = '0' } = useParams()
  const assetId = Number(id)
  const queryClient = useQueryClient()
  const [form, setForm] = useState<Form>(emptyForm)
  const [message, setMessage] = useState('')
  const asset = useQuery({ queryKey: ['v1-asset', id], queryFn: () => assetApi.find(assetId), enabled: assetId > 0 })
  const options = useQuery({ queryKey: ['v1-asset-form-options'], queryFn: assetApi.formOptions })

  useEffect(() => {
    const row = asset.data
    if (!row) return
    setForm({
      nombre: row.nombre || '',
      codigo: row.codigo || '',
      marca: row.marca || '',
      tipo: String(row.id_tipo || ''),
      departamento: String(row.id_departamento || ''),
      proveedor: String(row.id_proveedor || ''),
      estado: String(row.estado || ''),
      asignado: String(row.id_asignado || ''),
      precio: String(row.precio_compra || ''),
      compra: row.fecha_compra?.slice(0, 10) || '',
      factura: row.numero_factura || '',
      ubicacion: String(row.id_ubicacion || ''),
    })
  }, [asset.data])

  const save = useMutation({
    mutationFn: () => assetApi.update(assetId, {
      nombre: form.nombre,
      codigo: form.codigo || null,
      marca: form.marca || null,
      id_tipo: form.tipo ? Number(form.tipo) : null,
      id_departamento: form.departamento ? Number(form.departamento) : null,
      id_proveedor: Number(form.proveedor),
      estado: form.estado ? Number(form.estado) : null,
      id_asignado: form.asignado ? Number(form.asignado) : null,
      precio_compra: form.precio ? Number(form.precio) : null,
      fecha_compra: form.compra || null,
      numero_factura: form.factura || null,
      id_ubicacion: form.ubicacion ? Number(form.ubicacion) : null,
    }),
    onSuccess: () => {
      setMessage('Activo actualizado.')
      queryClient.invalidateQueries({ queryKey: ['v1-asset', id] })
      queryClient.invalidateQueries({ queryKey: ['v1-assets'] })
    },
    onError: () => setMessage('No fue posible actualizar el activo.'),
  })

  function field(key: keyof Form, value: string) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  function submit(event: FormEvent) {
    event.preventDefault()
    save.mutate()
  }

  if (!asset.data) {
    return <section className="panel"><p className="muted">{asset.isLoading ? 'Cargando activo...' : 'Activo no encontrado.'}</p></section>
  }

  return <section>
    <div className="topbar"><div><h1>Activo #{id}</h1><p className="muted">Detalle, asignación, compra y ficha técnica.</p></div></div>
    <div className="panel"><form onSubmit={submit}>
      <div className="form-grid">
        <label className="field">Nombre<input value={form.nombre} onChange={(event) => field('nombre', event.target.value)} required /></label>
        <label className="field">Código<input value={form.codigo} onChange={(event) => field('codigo', event.target.value)} /></label>
        <label className="field">Marca<input value={form.marca} onChange={(event) => field('marca', event.target.value)} /></label>
        <label className="field">Tipo<select value={form.tipo} onChange={(event) => field('tipo', event.target.value)}><option value="">Sin tipo</option>{options.data?.types.map((option) => <option value={option.id} key={option.id}>{option.nombre}</option>)}</select></label>
        <label className="field">Departamento<select value={form.departamento} onChange={(event) => field('departamento', event.target.value)}><option value="">Sin departamento</option>{options.data?.departments.map((option) => <option value={option.id} key={option.id}>{option.nombre}</option>)}</select></label>
        <label className="field">Proveedor<select value={form.proveedor} onChange={(event) => field('proveedor', event.target.value)} required><option value="">Selecciona proveedor</option>{options.data?.providers.map((option) => <option value={option.id} key={option.id}>{option.nombre}</option>)}</select></label>
        <label className="field">Estado<select value={form.estado} onChange={(event) => field('estado', event.target.value)}><option value="">Sin estado</option>{options.data?.states.map((option) => <option value={option.id} key={option.id}>{option.nombre}</option>)}</select></label>
        <label className="field">Asignado a<select value={form.asignado} onChange={(event) => field('asignado', event.target.value)}><option value="">Sin asignación</option>{options.data?.users.map((option) => <option value={option.id} key={option.id}>{option.nombre} {option.apellido || ''}</option>)}</select></label>
        <label className="field">Precio de compra<input type="number" min="0" value={form.precio} onChange={(event) => field('precio', event.target.value)} /></label>
        <label className="field">Fecha de compra<input type="date" value={form.compra} onChange={(event) => field('compra', event.target.value)} /></label>
        <label className="field">Factura<input value={form.factura} onChange={(event) => field('factura', event.target.value)} /></label>
        <label className="field">Ubicación<input value={form.ubicacion} onChange={(event) => field('ubicacion', event.target.value)} /></label>
      </div>
      <button className="primary" disabled={save.isPending}>{save.isPending ? 'Guardando...' : 'Guardar cambios'}</button>
      {message && <p className="muted" role="status">{message}</p>}
    </form></div>
    <TechnicalSheetPanel tabla="activos" idMaestro={assetId} />
    <AttachmentPanel table="activos" id={assetId} />
  </section>
}
