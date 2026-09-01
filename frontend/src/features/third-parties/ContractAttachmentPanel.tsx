import { ChangeEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { contractApi } from '../../api'

type Kind = 'cliente' | 'proveedor'

export default function ContractAttachmentPanel({ kind, thirdPartyId, contractId }: { kind: Kind; thirdPartyId: number; contractId: number }) {
  const [file, setFile] = useState<File | null>(null)
  const [message, setMessage] = useState('')
  const queryClient = useQueryClient()
  const attachments = useQuery({
    queryKey: ['v1-contract-attachments', kind, thirdPartyId, contractId],
    queryFn: () => kind === 'cliente'
      ? contractApi.attachmentsForClient(thirdPartyId, contractId)
      : contractApi.attachmentsForProvider(thirdPartyId, contractId),
  })
  const upload = useMutation({
    mutationFn: () => {
      if (!file) return Promise.reject(new Error('Archivo requerido.'))
      return kind === 'cliente'
        ? contractApi.uploadAttachmentForClient(thirdPartyId, contractId, file)
        : contractApi.uploadAttachmentForProvider(thirdPartyId, contractId, file)
    },
    onSuccess: () => {
      setFile(null)
      setMessage('Archivo cargado correctamente.')
      queryClient.invalidateQueries({ queryKey: ['v1-contract-attachments', kind, thirdPartyId, contractId] })
    },
    onError: () => setMessage('No fue posible cargar el archivo.'),
  })

  function select(event: ChangeEvent<HTMLInputElement>) {
    setFile(event.target.files?.[0] || null)
  }

  async function download(attachmentId: number, filename: string) {
    try {
      const blob = kind === 'cliente'
        ? await contractApi.downloadAttachmentForClient(thirdPartyId, contractId, attachmentId)
        : await contractApi.downloadAttachmentForProvider(thirdPartyId, contractId, attachmentId)
      const url = URL.createObjectURL(blob)
      const anchor = document.createElement('a')
      anchor.href = url
      anchor.download = filename
      anchor.click()
      URL.revokeObjectURL(url)
    } catch {
      setMessage('No fue posible descargar el archivo.')
    }
  }

  return <div className="panel attachment-panel"><h2>Adjuntos</h2><div className="attachment-actions"><input type="file" onChange={select} /><button className="secondary" type="button" onClick={() => upload.mutate()} disabled={!file || upload.isPending}>Subir archivo</button></div>{message && <p className="muted" role="status">{message}</p>}{attachments.isLoading && <p className="muted">Cargando adjuntos...</p>}{attachments.data?.map((attachment) => <button className="attachment-link" type="button" onClick={() => download(attachment.id, attachment.nombre_archivo)} key={attachment.id}>{attachment.nombre_archivo}</button>)}</div>
}
