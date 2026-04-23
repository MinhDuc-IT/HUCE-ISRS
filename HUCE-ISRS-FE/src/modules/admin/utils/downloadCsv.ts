/** Xuất CSV UTF-8 BOM (mở tốt bằng Excel). */
export function downloadCsv(filename: string, rows: string[][]) {
  const BOM = '\uFEFF'
  const esc = (cell: string) => {
    const s = String(cell)
    if (/[",\n\r]/.test(s)) return `"${s.replace(/"/g, '""')}"`
    return s
  }
  const body = rows.map((r) => r.map(esc).join(',')).join('\r\n')
  const blob = new Blob([BOM + body], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}
