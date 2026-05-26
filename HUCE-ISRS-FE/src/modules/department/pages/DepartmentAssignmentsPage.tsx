import { useEffect, useState } from "react";
import { useAuth } from "@/shared/context/AuthContext";
import { apiFetch } from "@/shared/utils/apiClient";
import type { ApiDepartmentSubjectAssignment } from "@/shared/types/api";

export function DepartmentAssignmentsPage() {
  const { user } = useAuth();
  const [rows, setRows] = useState<ApiDepartmentSubjectAssignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  const [editingSubjectId, setEditingSubjectId] = useState<number | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState({
    lecture_name: "",
    lecturer_phone_number: "",
    lecturer_email: "",
  });

  useEffect(() => {
    if (!user?.departmentId) return;
    fetchRows();
  }, [user?.departmentId]);

  async function fetchRows() {
    try {
      setLoading(true);
      const res = await apiFetch<{ data: ApiDepartmentSubjectAssignment[] }>(
        "/department/subject-assignments",
      );
      setRows(res.data || []);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setError("Lỗi tải danh sách: " + msg);
    } finally {
      setLoading(false);
    }
  }

  function openAssignModal(row: ApiDepartmentSubjectAssignment) {
    if (!row.can_assign_lecturer) return;
    setEditingSubjectId(row.subject_id);
    setForm({
      lecture_name: row.lecture_name ?? "",
      lecturer_phone_number: row.lecturer_phone ?? "",
      lecturer_email: row.lecturer_email ?? "",
    });
    setIsModalOpen(true);
  }

  async function saveEdit() {
    if (editingSubjectId === null) return;
    setError(null);
    setMessage(null);
    try {
      const res = await apiFetch<{
        data: { updated_count: number };
        message: string;
      }>(`/department/subjects/${editingSubjectId}/lecturer`, {
        method: "PATCH",
        data: form,
      });
      setMessage(
        res.message ??
          `Đã gán giảng viên cho ${res.data?.updated_count ?? 0} đăng ký.`,
      );
      setEditingSubjectId(null);
      setIsModalOpen(false);
      await fetchRows();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setError("Lưu thất bại: " + msg);
    }
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingSubjectId(null);
  }

  if (!user?.departmentId) {
    return (
      <p className="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Tài khoản chưa gắn bộ môn. Đăng xuất và đăng nhập lại bằng tài khoản bộ
        môn.
      </p>
    );
  }

  return (
    <div className="space-y-4">
      <h1 className="text-lg font-semibold text-gray-800">
        Phân công giảng viên phụ đạo theo môn học
      </h1>
      <p className="text-sm text-gray-600">
        Hiệu lực sau thời gian sinh viên đăng ký
      </p>

      {error && (
        <div className="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}
      {message && (
        <div className="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {message}
        </div>
      )}

      <div className="overflow-x-auto rounded border border-gray-200 bg-white shadow-sm">
        <table className="w-full text-sm text-left">
          <thead className="bg-gray-100 text-xs uppercase text-gray-600">
            <tr>
              <th className="px-3 py-2 w-12 text-center">STT</th>
              <th className="px-3 py-2">Mã môn</th>
              <th className="px-3 py-2">Tên môn</th>
              <th className="px-3 py-2">Bộ môn</th>
              <th className="px-3 py-2 text-center">STC</th>
              <th className="px-3 py-2 text-center">Số ĐK</th>
              <th className="px-3 py-2">Giảng viên</th>
              <th className="px-3 py-2">SĐT / Email</th>
              <th className="px-3 py-2 w-24"></th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={9} className="px-3 py-6 text-center text-gray-500">
                  Đang tải...
                </td>
              </tr>
            ) : rows.length === 0 ? (
              <tr>
                <td
                  colSpan={9}
                  className="px-3 py-6 text-center text-gray-500 italic"
                >
                  Chưa có môn nào có đăng ký phụ đạo.
                </td>
              </tr>
            ) : (
              rows.map((row, index) => (
                <tr
                  key={row.subject_id}
                  className="border-t border-gray-100 hover:bg-gray-50"
                >
                  <td className="px-3 py-2 text-center">{index + 1}</td>
                  <td className="px-3 py-2 font-mono text-xs">
                    {row.subject_code}
                  </td>
                  <td className="px-3 py-2">{row.subject_name}</td>
                  <td className="px-3 py-2 text-xs">{row.department_name}</td>
                  <td className="px-3 py-2 text-center">{row.credits}</td>
                  <td className="px-3 py-2 text-center">
                    {row.registration_count}
                  </td>
                  <td className="px-3 py-2 text-xs">
                    {row.lecture_name || "—"}
                  </td>
                  <td className="px-3 py-2 text-xs">
                    <>
                      {row.lecturer_phone && (
                        <span className="block">{row.lecturer_phone}</span>
                      )}
                      {row.lecturer_email && (
                        <span className="block text-gray-500">
                          {row.lecturer_email}
                        </span>
                      )}
                      {!row.lecturer_phone && !row.lecturer_email && "—"}
                    </>
                  </td>
                  <td className="px-3 py-2">
                    {row.can_assign_lecturer ? (
                      <button
                        type="button"
                        className="text-xs text-brand-600 hover:underline"
                        onClick={() => openAssignModal(row)}
                      >
                        Gán GV
                      </button>
                    ) : (
                      <span
                        className="text-xs text-gray-400"
                        title="Chờ hết thời gian đăng ký phụ đạo"
                      >
                        Chưa mở
                      </span>
                    )}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {isModalOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
          onMouseDown={(e) => {
            if (e.target === e.currentTarget) closeModal();
          }}
          role="dialog"
          aria-modal="true"
        >
          <div className="w-full max-w-2xl rounded border border-blue-300 bg-white shadow-lg">
            <div className="rounded-t bg-blue-600 px-4 py-2 text-center text-white font-semibold">
              Nhập thông tin giảng viên phụ đạo
            </div>

            <div className="space-y-4 p-4">
              <div className="grid grid-cols-12 items-center gap-3">
                <label className="col-span-12 sm:col-span-3 text-sm font-semibold">
                  Giảng viên:
                </label>
                <div className="col-span-12 sm:col-span-9">
                  <input
                    className="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Nhập tên giảng viên"
                    value={form.lecture_name}
                    onChange={(e) =>
                      setForm((f) => ({ ...f, lecture_name: e.target.value }))
                    }
                  />
                </div>
              </div>

              <div className="grid grid-cols-12 items-center gap-3">
                <label className="col-span-12 sm:col-span-3 text-sm font-semibold">
                  Điện thoại:
                </label>
                <div className="col-span-12 sm:col-span-9">
                  <input
                    className="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Điện thoại"
                    value={form.lecturer_phone_number}
                    onChange={(e) =>
                      setForm((f) => ({
                        ...f,
                        lecturer_phone_number: e.target.value,
                      }))
                    }
                  />
                </div>
              </div>

              <div className="grid grid-cols-12 items-center gap-3">
                <label className="col-span-12 sm:col-span-3 text-sm font-semibold">
                  Email:
                </label>
                <div className="col-span-12 sm:col-span-9">
                  <input
                    className="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Email"
                    value={form.lecturer_email}
                    onChange={(e) =>
                      setForm((f) => ({
                        ...f,
                        lecturer_email: e.target.value,
                      }))
                    }
                  />
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  className="rounded border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  onClick={closeModal}
                >
                  Hủy
                </button>
                <button
                  type="button"
                  className="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                  onClick={saveEdit}
                >
                  Lưu
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
