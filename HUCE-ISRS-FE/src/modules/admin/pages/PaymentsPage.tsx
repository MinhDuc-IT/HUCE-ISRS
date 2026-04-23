import { useMemo } from 'react'
import { Link } from 'react-router-dom'
import { useDemoData } from '@/shared/context/DemoDataContext'
import { useToast } from '@/shared/context/ToastContext'
import { downloadCsv } from '@/modules/admin/utils/downloadCsv'

function formatVnd(n: number) {
  return new Intl.NumberFormat('vi-VN').format(n) + ' đ'
}

export function PaymentsPage() {
  const { state, getPaymentLines } = useDemoData()
  const { success } = useToast()
  const lines = useMemo(() => getPaymentLines(), [getPaymentLines])
  const totals = useMemo(() => {
    return lines.reduce(
      (acc, r) => {
        acc.sub += r.subtotal
        acc.vat += r.vatAmount
        acc.all += r.total
        return acc
      },
      { sub: 0, vat: 0, all: 0 },
    )
  }, [lines])

  function handleExportExcel() {
    const header = [
      'Đợt',
      'Mã môn',
      'Tên môn',
      'Giảng viên',
      'Số SV',
      'Đơn giá',
      'Tiền hàng',
      'VAT',
      'Thành tiền',
    ]
    const body = lines.map((r) => [
      r.cohortName,
      r.courseCode,
      r.courseName,
      r.lecturerName,
      String(r.studentCount),
      String(r.unitFee),
      String(r.subtotal),
      String(r.vatAmount),
      String(r.total),
    ])
    downloadCsv(`thanh-toan-phu-dao-${new Date().toISOString().slice(0, 10)}.csv`, [
      header,
      ...body,
      [],
      ['', '', '', '', '', 'Cộng', String(totals.sub), String(totals.vat), String(totals.all)],
    ])
    success('Đã tải file CSV thanh toán.')
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-lg font-semibold text-gray-800">
            Thanh toán tiền phụ đạo
          </h1>
          <p className="text-sm text-gray-500">
            Tổng hợp theo giảng viên đã phân công và môn có đăng ký. Đơn giá & VAT
            lấy từ{' '}
            <Link to="/admin/settings" className="text-brand-500 hover:text-brand-600 hover:underline">
              Cài đặt hệ thống
            </Link>
            .
          </p>
          <p className="mt-1 text-xs text-gray-400">
            Đơn giá hiện tại: {formatVnd(state.settings.feePerRegistration)} / lượt
            đăng ký · VAT {state.settings.vatPercent}%
          </p>
        </div>
        <button
          type="button"
          className="inline-flex items-center justify-center rounded-lg bg-success-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-success-600"
          onClick={handleExportExcel}
        >
          Xuất CSV (Excel)
        </button>
      </div>

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm">
        <div className="overflow-x-auto">
          <table className="min-w-full border-collapse text-left text-sm">
            <thead className="border-b border-gray-200 bg-gray-50 text-gray-600">
              <tr>
                <th className="px-3 py-2 font-semibold">Đợt</th>
                <th className="px-3 py-2 font-semibold">Môn</th>
                <th className="px-3 py-2 font-semibold">Giảng viên</th>
                <th className="px-3 py-2 text-right font-semibold">Số SV</th>
                <th className="px-3 py-2 text-right font-semibold">Tiền hàng</th>
                <th className="px-3 py-2 text-right font-semibold">VAT</th>
                <th className="px-3 py-2 text-right font-semibold">Thành tiền</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {lines.map((r) => (
                <tr key={`${r.cohortId}-${r.courseId}`} className="hover:bg-gray-50/80">
                  <td className="px-3 py-2 text-gray-700">{r.cohortName}</td>
                  <td className="px-3 py-2 text-gray-800">
                    <span className="font-mono text-xs text-gray-500">
                      {r.courseCode}
                    </span>{' '}
                    {r.courseName}
                  </td>
                  <td className="px-3 py-2 text-gray-800">{r.lecturerName}</td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-700">
                    {r.studentCount}
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-700">
                    {formatVnd(r.subtotal)}
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-700">
                    {formatVnd(r.vatAmount)}
                  </td>
                  <td className="px-3 py-2 text-right font-medium tabular-nums text-gray-900">
                    {formatVnd(r.total)}
                  </td>
                </tr>
              ))}
            </tbody>
            {lines.length > 0 ? (
              <tfoot className="border-t-2 border-gray-200 bg-gray-50 font-semibold">
                <tr>
                  <td colSpan={4} className="px-3 py-2 text-right text-gray-700">
                    Cộng
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-900">
                    {formatVnd(totals.sub)}
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-900">
                    {formatVnd(totals.vat)}
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-gray-900">
                    {formatVnd(totals.all)}
                  </td>
                </tr>
              </tfoot>
            ) : null}
          </table>
        </div>
        {lines.length === 0 ? (
          <p className="px-4 py-8 text-center text-sm text-gray-500">
            Chưa có dòng thanh toán: cần phân công giảng viên (tên non-empty) cho
            các môn có đăng ký.
          </p>
        ) : null}
      </div>
    </div>
  )
}
