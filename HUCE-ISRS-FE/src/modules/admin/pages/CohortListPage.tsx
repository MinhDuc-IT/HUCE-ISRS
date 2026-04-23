import { Link } from 'react-router-dom'
import { CohortStatusBadge } from '@/modules/admin/components/CohortStatusBadge'
import { useConfirm } from '@/shared/context/ConfirmContext'
import { useDemoData } from '@/shared/context/DemoDataContext'
import { useToast } from '@/shared/context/ToastContext'

export function CohortListPage() {
  const { state, registrationCountForCohort, removeCohort } = useDemoData()
  const { confirm } = useConfirm()
  const { success, error: showError } = useToast()

  async function handleDelete(id: string, name: string) {
    const ok = await confirm({
      title: 'Xóa đợt phụ đạo',
      message: `Xóa đợt "${name}"? Thao tác không hoàn tác (mock).`,
      confirmLabel: 'Xóa',
      variant: 'danger',
    })
    if (!ok) return
    const res = removeCohort(id)
    if (!res.ok) {
      showError(
        'Không thể xóa: đợt này đã có sinh viên đăng ký phụ đạo (theo UC).',
      )
      return
    }
    success('Đã xóa đợt phụ đạo.')
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-semibold text-gray-800">Đợt phụ đạo</h1>
        </div>
        <Link
          to="/admin/cohorts/new"
          className="inline-flex items-center rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
        >
          + Thêm đợt
        </Link>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-4 py-3 font-semibold">Tên đợt</th>
                <th className="px-4 py-3 font-semibold">Thời gian</th>
                <th className="px-4 py-3 font-semibold">Trạng thái</th>
                <th className="px-4 py-3 font-semibold">SV đăng ký</th>
                <th className="px-4 py-3 font-semibold text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {state.cohorts.map((c) => (
                <tr key={c.id} className="hover:bg-gray-50/80">
                  <td className="px-4 py-3 font-medium text-gray-800">{c.name}</td>
                  <td className="px-4 py-3 text-gray-600">
                    {c.startDate} → {c.endDate}
                  </td>
                  <td className="px-4 py-3">
                    <CohortStatusBadge status={c.status} />
                  </td>
                  <td className="px-4 py-3 text-gray-600">
                    {registrationCountForCohort(c.id)}
                  </td>
                  <td className="px-4 py-3 text-right">
                    <Link
                      to={`/admin/cohorts/${c.id}/edit`}
                      className="mr-2 text-brand-500 hover:text-brand-600 no-underline hover:underline"
                    >
                      Sửa
                    </Link>
                    <button
                      type="button"
                      className="text-red-600 hover:underline"
                      onClick={() => void handleDelete(c.id, c.name)}
                    >
                      Xóa
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        {state.cohorts.length === 0 ? (
          <p className="px-4 py-8 text-center text-sm text-gray-500">
            Chưa có đợt phụ đạo.
          </p>
        ) : null}
      </div>
    </div>
  )
}
