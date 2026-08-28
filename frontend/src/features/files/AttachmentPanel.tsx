import { ChangeEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { fileApi } from '../../api'

export default function AttachmentPanel({ table, id }: { table: string; id: number }) {
  const [file, setFile] = useState<File | null>(null); const [message, setMessage] = useState(''); const queryClient = useQueryClient(); const files = useQuery({ queryKey: ['files', table, id], queryFn: () => fileApi.list(table) }); const upload = useMutation({ mutationFn: () => file ? fileApi.upload(file, table, id) : Promise.reject(new Error('Archivo requerido')), onSuccess: () => { setFile(null); setMessage('Archivo cargado correctamente.'); queryClient.invalidateQueries({ queryKey: ['files', table, id] }) }, onError: () => setMessage('No fue posible cargar el archivo.') })
  function select(event: ChangeEvent<HTMLInputElement>) { setFile(event.target.files?.[0] || null) }
  const uuid = localStorage.getItem('ikaros.uuid') || ''
  async function download(name: string) { try { const blob = await fileApi.download(uuid, table, name); const url = URL.createObjectURL(blob); const anchor = document.createElement('a'); anchor.href = url; anchor.download = name; anchor.click(); URL.revokeObjectURL(url) } catch { setMessage('No fue posible descargar el archivo.') } }
  return <div className="panel attachment-panel"><h2>Adjuntos</h2><div className="attachment-actions"><input type="file" onChange={select} /><button className="secondary" onClick={() => upload.mutate()} disabled={!file || upload.isPending}>Subir archivo</button></div>{message && <p className="muted" role="status">{message}</p>}{files.data?.map((name) => <button className="attachment-link" onClick={() => download(name)} key={name}>{name}</button>)}<p className="muted">Los archivos se almacenan asociados a la solicitud.</p></div>
}