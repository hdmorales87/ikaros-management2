import { ArrowLeft } from 'lucide-react'
import { Link } from 'react-router-dom'

export default function AdministrationBackLink() {
  return <div className="administration-back"><Link to="/administracion"><ArrowLeft size={17} aria-hidden="true" />Volver a Administración</Link></div>
}