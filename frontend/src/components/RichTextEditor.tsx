import { EditorContent, useEditor } from '@tiptap/react'
import StarterKit from '@tiptap/starter-kit'
import { Bold, Italic, List, ListOrdered } from 'lucide-react'
import { useEffect } from 'react'

type Props = {
  value: string
  onChange: (value: string) => void
}

export default function RichTextEditor({ value, onChange }: Props) {
  const editor = useEditor({
    extensions: [StarterKit],
    content: value,
    editorProps: { attributes: { class: 'rich-text-content', 'aria-label': 'Descripción de la solicitud' } },
    onUpdate: ({ editor: currentEditor }) => onChange(currentEditor.getHTML()),
  })

  useEffect(() => {
    if (editor && value !== editor.getHTML()) {
      editor.commands.setContent(value, { emitUpdate: false })
    }
  }, [editor, value])

  if (!editor) return null
  const tools = [
    { label: 'Negrita', icon: Bold, action: () => editor.chain().focus().toggleBold().run(), active: editor.isActive('bold') },
    { label: 'Cursiva', icon: Italic, action: () => editor.chain().focus().toggleItalic().run(), active: editor.isActive('italic') },
    { label: 'Lista con viñetas', icon: List, action: () => editor.chain().focus().toggleBulletList().run(), active: editor.isActive('bulletList') },
    { label: 'Lista numerada', icon: ListOrdered, action: () => editor.chain().focus().toggleOrderedList().run(), active: editor.isActive('orderedList') },
  ]

  return <div className="rich-text-editor"><div className="rich-text-toolbar">{tools.map((tool) => { const Icon = tool.icon; return <button className={tool.active ? 'rich-text-tool active' : 'rich-text-tool'} type="button" aria-label={tool.label} title={tool.label} onClick={tool.action} key={tool.label}><Icon size={17} aria-hidden="true" /></button> })}</div><EditorContent editor={editor} /></div>
}