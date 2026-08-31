import { FormEvent, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ArrowLeft } from 'lucide-react'
import { Link, Navigate, useParams } from 'react-router-dom'
import { getGridRows, gridApi, GridRow } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'

type Kind = 'cliente' | 'proveedor'

type PaymentForm = {
  numeroFactura: string
  fechaFactura: string
  valor: string
}

const emptyPaymentForm: PaymentForm = { numeroFactura: '', fechaFactura: '', valor: '' }

type Form = {
  tercero: string
  nombre: string
  tipoContrato: string
  estado: string
  objeto: string
  moneda: string
  monto: string
  iva: string
  responsable: string
  fechaInicio: string
  fechaVencimiento: string
  planPago: string
  numeroPagos: string
  responsablePago: string
  nombreResponsablePago: string
  emailResponsablePago: string
  renovacionAutomatica: string
  observaciones: string
}

const emptyForm: Form = {
  tercero: '',
  nombre: '',
  tipoContrato: '',
  estado: '',
  objeto: '',
  moneda: '',
  monto: '',
  iva: '',
  responsable: '',
  fechaInicio: '',
  fechaVencimiento: '',
  planPago: '',
  numeroPagos: '',
  responsablePago: '',
  nombreResponsablePago: '',
  emailResponsablePago: '',
  renovacionAutomatica: 'false',
  observaciones: '',
}

export default function ThirdPartyContractsPage({ kind }: { kind: Kind }) {
  const { id = '' } = useParams()
  const thirdPartyId = Number(id)
  const queryClient = useQueryClient()
  const [form, setForm] = useState<Form>({ ...emptyForm, tercero: String(thirdPartyId || '') })
  const [selected, setSelected] = useState<GridRow | null>(null)
  const [selectedContractId, setSelectedContractId] = useState<number | null>(null)
  const [paymentForm, setPaymentForm] = useState<PaymentForm>(emptyPaymentForm)
  const [message, setMessage] = useState('')

  const title = kind === 'cliente' ? 'Contratos de clientes' : 'Contratos de proveedores'
  const terceros = useQuery({
    queryKey: ['third-party-contracts-terceros', kind],
    queryFn: () => getGridRows('terceros', '', { [kind]: 'true' }, ['razon_social', 'nombre_comercial']),
  })
  const currencies = useQuery({ queryKey: ['contract-currencies'], queryFn: () => getGridRows('monedas', '', {}, ['nombre']) })
  const states = useQuery({ queryKey: ['contract-states'], queryFn: () => getGridRows('terceros_contratos_estados', '', {}, ['nombre']) })
  const paymentPlans = useQuery({ queryKey: ['contract-payment-plans'], queryFn: () => getGridRows('terceros_contratos_planes_pagos', '', {}, ['nombre']) })
  const users = useQuery({ queryKey: ['contract-users'], queryFn: () => getGridRows('users', '', { activo: true }, ['nombre', 'apellido']) })
  const contracts = useQuery({
    queryKey: ['third-party-contracts', kind, thirdPartyId],
    queryFn: () => getGridRows('terceros_contratos', '', { tipo: kind, id_tercero: thirdPartyId }, ['nombre', 'objeto_contrato', 'fecha_inicio', 'fecha_vencimiento']),
  })
  const payments = useQuery({
    queryKey: ['third-party-contract-payments', selectedContractId],
    queryFn: () => selectedContractId ? getGridRows('terceros_contratos_pagos', '', { id_contrato: selectedContractId }, ['numero_factura', 'fecha_factura', 'valor']) : Promise.resolve([]),
    enabled: Boolean(selectedContractId),
  })

  const activeContracts = useMemo(() => (contracts.data ?? []).filter((item) => String(item.estado ?? '').length > 0).length, [contracts.data])
  const expiringSoon = useMemo(() => (contracts.data ?? []).filter((item) => {
    const expiry = item.fecha_vencimiento ? new Date(String(item.fecha_vencimiento)) : null
    if (!expiry || Number.isNaN(expiry.getTime())) return false
    const diffDays = Math.ceil((expiry.getTime() - Date.now()) / (1000 * 60 * 60 * 24))
    return diffDays >= 0 && diffDays <= 30
  }).length, [contracts.data])
  const overdue = useMemo(() => (contracts.data ?? []).filter((item) => {
    const expiry = item.fecha_vencimiento ? new Date(String(item.fecha_vencimiento)) : null
    if (!expiry || Number.isNaN(expiry.getTime())) return false
    return expiry.getTime() < Date.now()
  }).length, [contracts.data])

  const terceroMap = useMemo(() => Object.fromEntries((terceros.data ?? []).map((item) => [String(item.id), String(item.razon_social ?? item.nombre_comercial ?? '-') ])), [terceros.data])

  const save = useMutation({
    mutationFn: () => {
      const payload = {
        id_tercero: Number(form.tercero) || null,
        id_area: null,
        nombre: form.nombre || null,
        tipo_contrato: form.tipoContrato || null,
        estado: form.estado ? Number(form.estado) : null,
        objeto_contrato: form.objeto || null,
        id_moneda: form.moneda ? Number(form.moneda) : null,
        monto: form.monto ? Number(form.monto) : null,
        iva: form.iva ? Number(form.iva) : null,
        id_responsable_ejecucion: form.responsable ? Number(form.responsable) : null,
        fecha_inicio: form.fechaInicio || null,
        fecha_vencimiento: form.fechaVencimiento || null,
        id_plan_pago: form.planPago ? Number(form.planPago) : null,
        numero_pagos: form.numeroPagos ? Number(form.numeroPagos) : null,
        id_responsable_pago: form.responsablePago ? Number(form.responsablePago) : null,
        nombre_responsable_pago: form.nombreResponsablePago || null,
        email_responsable_pago: form.emailResponsablePago || null,
        tasa_negociacion: null,
        renovacion_automatica: form.renovacionAutomatica === 'true' ? 'true' : 'false',
        observaciones: form.observaciones || null,
        tipo: kind,
        activo: 1,
      }
      return selected ? gridApi.update('terceros_contratos', { ...payload, id: selected.id }) : gridApi.insert('terceros_contratos', payload)
    },
    onSuccess: () => {
      setForm({ ...emptyForm, tercero: String(thirdPartyId) })
      setSelected(null)
      setMessage('Contrato guardado.')
      queryClient.invalidateQueries({ queryKey: ['third-party-contracts', kind, thirdPartyId] })
    },
    onError: () => setMessage('No fue posible guardar el contrato.'),
  })

  const deactivate = useMutation({
    mutationFn: (id: number) => gridApi.deactivate('terceros_contratos', id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['third-party-contracts', kind, thirdPartyId] }),
  })

  const savePayment = useMutation({
    mutationFn: () => {
      if (!selectedContractId) throw new Error('Debe seleccionar un contrato.')
      return gridApi.insert('terceros_contratos_pagos', {
        id_contrato: selectedContractId,
        numero_factura: paymentForm.numeroFactura || null,
        fecha_factura: paymentForm.fechaFactura || null,
        valor: paymentForm.valor || null,
        id_usuario: null,
        activo: 1,
      })
    },
    onSuccess: () => {
      setPaymentForm(emptyPaymentForm)
      queryClient.invalidateQueries({ queryKey: ['third-party-contract-payments', selectedContractId] })
      setMessage('Pago registrado.')
    },
    onError: () => setMessage('No fue posible registrar el pago.')
  })

  const deactivatePayment = useMutation({
    mutationFn: (id: number) => gridApi.deactivate('terceros_contratos_pagos', id),
    onSuccess: () => {
      if (selectedContractId) queryClient.invalidateQueries({ queryKey: ['third-party-contract-payments', selectedContractId] })
    },
  })

  function field(key: keyof Form, value: string) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  function submit(event: FormEvent) {
    event.preventDefault()
    if (form.fechaInicio && form.fechaVencimiento && form.fechaVencimiento < form.fechaInicio) {
      setMessage('La fecha de vencimiento debe ser posterior a la de inicio.')
      return
    }
    save.mutate()
  }

  function submitPayment(event: FormEvent) {
    event.preventDefault()
    if (!selectedContractId) {
      setMessage('Selecciona primero un contrato para registrar pagos.')
      return
    }
    savePayment.mutate()
  }

  function edit(item: GridRow) {
    setSelected(item)
    setForm({
      tercero: String(item.id_tercero ?? ''),
      nombre: String(item.nombre ?? ''),
      tipoContrato: String(item.tipo_contrato ?? ''),
      estado: String(item.estado ?? ''),
      objeto: String(item.objeto_contrato ?? ''),
      moneda: String(item.id_moneda ?? ''),
      monto: String(item.monto ?? ''),
      iva: String(item.iva ?? ''),
      responsable: String(item.id_responsable_ejecucion ?? ''),
      fechaInicio: String(item.fecha_inicio ?? '').slice(0, 10),
      fechaVencimiento: String(item.fecha_vencimiento ?? '').slice(0, 10),
      planPago: String(item.id_plan_pago ?? ''),
      numeroPagos: String(item.numero_pagos ?? ''),
      responsablePago: String(item.id_responsable_pago ?? ''),
      nombreResponsablePago: String(item.nombre_responsable_pago ?? ''),
      emailResponsablePago: String(item.email_responsable_pago ?? ''),
      renovacionAutomatica: String(item.renovacion_automatica ?? 'false'),
      observaciones: String(item.observaciones ?? ''),
    })
  }

  if (!Number.isInteger(thirdPartyId) || thirdPartyId < 1) return <Navigate to={`/${kind === 'cliente' ? 'clientes' : 'proveedores'}`} replace />

  return <section>
    <div className="context-back"><Link to={`/${kind === 'cliente' ? 'clientes' : 'proveedores'}`}><ArrowLeft size={17} aria-hidden="true" />Volver a {kind === 'cliente' ? 'Clientes' : 'Proveedores'}</Link></div>
    <div className="topbar"><div><h1>{title}</h1><p className="muted">Gestiona contratos y pagos de {terceroMap[String(thirdPartyId)] || (kind === 'cliente' ? 'este cliente' : 'este proveedor')}.</p></div></div>

    <div className="dashboard-grid">
      <article className="metric metric-blue"><span className="metric-label">Activos</span><strong>{activeContracts}</strong><span className="muted">contratos con estado definido</span></article>
      <article className="metric metric-amber"><span className="metric-label">Próximos a vencer</span><strong>{expiringSoon}</strong><span className="muted">en los próximos 30 días</span></article>
      <article className="metric metric-red"><span className="metric-label">Vencidos</span><strong>{overdue}</strong><span className="muted">contratos caducados</span></article>
    </div>

    <div className="panel">
      <form onSubmit={submit}>
        <div className="form-grid">
          <label className="field">{kind === 'cliente' ? 'Cliente' : 'Proveedor'}<input value={terceroMap[String(thirdPartyId)] || 'Cargando tercero...'} disabled /></label>
          <label className="field">Nombre del contrato<input value={form.nombre} onChange={(event) => field('nombre', event.target.value)} required /></label>
          <label className="field">Tipo de contrato<input value={form.tipoContrato} onChange={(event) => field('tipoContrato', event.target.value)} /></label>
          <label className="field">Estado<select value={form.estado} onChange={(event) => field('estado', event.target.value)}><option value="">Sin estado</option>{states.data?.map((item) => <option value={String(item.id)} key={String(item.id)}>{String(item.nombre)}</option>)}</select></label>
          <label className="field">Objeto<textarea value={form.objeto} onChange={(event) => field('objeto', event.target.value)} /></label>
          <label className="field">Moneda<select value={form.moneda} onChange={(event) => field('moneda', event.target.value)}><option value="">Sin moneda</option>{currencies.data?.map((item) => <option value={String(item.id)} key={String(item.id)}>{String(item.nombre)}</option>)}</select></label>
          <label className="field">Monto<input type="number" value={form.monto} onChange={(event) => field('monto', event.target.value)} /></label>
          <label className="field">IVA<input type="number" value={form.iva} onChange={(event) => field('iva', event.target.value)} /></label>
          <label className="field">Responsable<select value={form.responsable} onChange={(event) => field('responsable', event.target.value)}><option value="">Sin responsable</option>{users.data?.map((item) => <option value={String(item.id)} key={String(item.id)}>{String(item.nombre)} {String(item.apellido)}</option>)}</select></label>
          <label className="field">Inicio<input type="date" value={form.fechaInicio} onChange={(event) => field('fechaInicio', event.target.value)} /></label>
          <label className="field">Vencimiento<input type="date" value={form.fechaVencimiento} onChange={(event) => field('fechaVencimiento', event.target.value)} /></label>
          <label className="field">Plan de pago<select value={form.planPago} onChange={(event) => field('planPago', event.target.value)}><option value="">Sin plan</option>{paymentPlans.data?.map((item) => <option value={String(item.id)} key={String(item.id)}>{String(item.nombre)}</option>)}</select></label>
          <label className="field">Número de pagos<input type="number" value={form.numeroPagos} onChange={(event) => field('numeroPagos', event.target.value)} /></label>
          <label className="field">Responsable de pago<select value={form.responsablePago} onChange={(event) => field('responsablePago', event.target.value)}><option value="">Sin responsable</option>{users.data?.map((item) => <option value={String(item.id)} key={String(item.id)}>{String(item.nombre)} {String(item.apellido)}</option>)}</select></label>
          <label className="field">Nombre responsable<input value={form.nombreResponsablePago} onChange={(event) => field('nombreResponsablePago', event.target.value)} /></label>
          <label className="field">Email responsable<input type="email" value={form.emailResponsablePago} onChange={(event) => field('emailResponsablePago', event.target.value)} /></label>
          <label className="field">Renovación automática<select value={form.renovacionAutomatica} onChange={(event) => field('renovacionAutomatica', event.target.value)}><option value="false">No</option><option value="true">Sí</option></select></label>
          <label className="field">Observaciones<textarea value={form.observaciones} onChange={(event) => field('observaciones', event.target.value)} /></label>
        </div>
        {message && <p className="muted" role="status">{message}</p>}
        <button className="primary" disabled={save.isPending}>{selected ? 'Guardar cambios' : 'Crear contrato'}</button>
        {selected && <button className="secondary" type="button" onClick={() => { setSelected(null); setForm({ ...emptyForm, tercero: String(thirdPartyId) }) }}>Cancelar</button>}
      </form>
    </div>

    <div className="panel">
      <div className="table-wrap">
        <table>
          <thead>
            <tr><th>Tercero</th><th>Nombre</th><th>Inicio</th><th>Vencimiento</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            {contracts.data?.map((item, index) => <tr key={String(item.id ?? index)}>
              <td>{String(terceroMap[String(item.id_tercero)] ?? '-')}</td>
              <td>{String(item.nombre ?? '-')}</td>
              <td>{String(item.fecha_inicio ?? '-')}</td>
              <td>{String(item.fecha_vencimiento ?? '-')}</td>
              <td>
                <button className="secondary" onClick={() => { setSelectedContractId(Number(item.id)); setMessage(''); }}>Pagos</button>
                <button className="secondary" onClick={() => edit(item)}>Editar</button>
                <button className="secondary" onClick={() => item.id && deactivate.mutate(Number(item.id))}>Desactivar</button>
              </td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>

    {selectedContractId && (
      <>
        <div className="panel">
          <h2>Pagos del contrato #{selectedContractId}</h2>
          <form onSubmit={submitPayment}>
            <div className="form-grid">
              <label className="field">Número factura<input value={paymentForm.numeroFactura} onChange={(event) => setPaymentForm((current) => ({ ...current, numeroFactura: event.target.value }))} /></label>
              <label className="field">Fecha factura<input type="date" value={paymentForm.fechaFactura} onChange={(event) => setPaymentForm((current) => ({ ...current, fechaFactura: event.target.value }))} /></label>
              <label className="field">Valor<input type="number" step="0.01" value={paymentForm.valor} onChange={(event) => setPaymentForm((current) => ({ ...current, valor: event.target.value }))} /></label>
            </div>
            <button className="primary" disabled={savePayment.isPending}>Registrar pago</button>
          </form>

          <div className="table-wrap">
            <table>
              <thead>
                <tr><th>Factura</th><th>Fecha</th><th>Valor</th><th>Acción</th></tr>
              </thead>
              <tbody>
                {payments.data?.map((item, index) => <tr key={String(item.id ?? index)}>
                  <td>{String(item.numero_factura ?? '-')}</td>
                  <td>{String(item.fecha_factura ?? '-')}</td>
                  <td>{String(item.valor ?? '-')}</td>
                  <td><button className="secondary" onClick={() => item.id && deactivatePayment.mutate(Number(item.id))}>Desactivar</button></td>
                </tr>)}
              </tbody>
            </table>
          </div>
        </div>

        <AttachmentPanel table="terceros_contratos_adjuntos" id={selectedContractId} />
      </>
    )}
  </section>
}
