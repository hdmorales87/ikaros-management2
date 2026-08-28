import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { userApi, User } from '../../api'
import { useAuth } from '../../auth'
import { Navigate } from 'react-router-dom'

type FormValues = { nombre: string; apellido: string; email: string; password: string; id_rol: string }
const emptyForm: FormValues = { nombre: '', apellido: '', email: '', password: '', id_rol: '0' }

export default function UsersPage() {
  const { session } = useAuth()
  const permissions = new Set(String(session?.permisos || '').split(',').filter(Boolean).map(Number))
  const queryClient = useQueryClient()
  const [form, setForm] = useState<FormValues>(emptyForm)
  const [editing, setEditing] = useState<User | null>(null)
  const [message, setMessage] = useState('')
  const users = useQuery({ queryKey: ['users'], queryFn: userApi.list })
  const refresh = () => queryClient.invalidateQueries({ queryKey: ['users'] })
  const save = useMutation({
    mutationFn: () => editing
      ? userApi.update(editing.id, { nombre: form.nombre, apellido: form.apellido, email: form.email, id_rol: Number(form.id_rol), ...(form.password ? { password: form.password } : {}) })
      : userApi.create({ ...form, id_rol: Number(form.id_rol) }),
    onSuccess: () => { refresh(); setEditing(null); setForm(emptyForm); setMessage(editing ? 'Usuario actualizado.' : 'Usuario creado correctamente.') },
    onError: () => setMessage('No fue posible guardar el usuario.'),
  })
  const update = useMutation({ mutationFn: ({ id, activo }: { id: number; activo: boolean }) => userApi.update(id, { activo: !activo }), onSuccess: () => { refresh(); setMessage('Estado de usuario actualizado.') } })
  const remove = useMutation({ mutationFn: userApi.remove, onSuccess: () => { refresh(); setMessage('Usuario eliminado.') } })
  const activation = useMutation({ mutationFn: userApi.sendActivation, onSuccess: () => setMessage('Enlace de activación enviado.'), onError: () => setMessage('No fue posible enviar el enlace de activación.') })
  if (!permissions.has(1) && !permissions.has(22)) return <Navigate to="/" replace />
  function submit(event: FormEvent) { event.preventDefault(); save.mutate() }
  function edit(user: User) { setEditing(user); setForm({ nombre: user.nombre, apellido: user.apellido, email: user.email, password: '', id_rol: String(user.id_rol) }); setMessage('') }
  function cancel() { setEditing(null); setForm(emptyForm) }
  function setField(field: keyof FormValues, value: string) { setForm((current) => ({ ...current, [field]: value })) }
  return <section><div className="topbar"><div><h1>Usuarios</h1><p className="muted">Personas con acceso a la organización.</p></div></div><div className="panel user-form"><h2>{editing ? 'Editar usuario' : 'Nuevo usuario'}</h2><form onSubmit={submit}><div className="form-grid"><label className="field">Nombre<input value={form.nombre} onChange={(event) => setField('nombre', event.target.value)} required /></label><label className="field">Apellido<input value={form.apellido} onChange={(event) => setField('apellido', event.target.value)} required /></label><label className="field">Correo<input type="email" value={form.email} onChange={(event) => setField('email', event.target.value)} required /></label><label className="field">Contraseña<input type="password" minLength={6} value={form.password} onChange={(event) => setField('password', event.target.value)} required={!editing} /></label><label className="field">Rol<input type="number" min={0} value={form.id_rol} onChange={(event) => setField('id_rol', event.target.value)} required /></label></div>{message && <p className="muted" role="status">{message}</p>}<button className="primary" disabled={save.isPending}>{editing ? 'Guardar cambios' : 'Crear usuario'}</button>{editing && <button className="secondary" type="button" onClick={cancel}>Cancelar</button>}</form></div><div className="panel"><h2>Usuarios registrados</h2>{users.isLoading && <p className="muted">Cargando usuarios...</p>}{users.isError && <div className="error">No fue posible cargar los usuarios.</div>}{users.data && <div className="table-wrap"><table><thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>{users.data.map((user) => <tr key={user.id}><td>{user.nombre} {user.apellido}</td><td>{user.email}</td><td>{user.id_rol}</td><td>{user.activo ? 'Activo' : 'Inactivo'}</td><td><button className="secondary" onClick={() => edit(user)}>Editar</button><button className="secondary" onClick={() => update.mutate({ id: user.id, activo: user.activo })} disabled={update.isPending}>{user.activo ? 'Desactivar' : 'Activar'}</button><button className="secondary" onClick={() => activation.mutate(user.id)} disabled={activation.isPending}>Activar por correo</button><button className="secondary" onClick={() => remove.mutate(user.id)} disabled={remove.isPending}>Eliminar</button></td></tr>)}</tbody></table></div>}</div></section>
}
