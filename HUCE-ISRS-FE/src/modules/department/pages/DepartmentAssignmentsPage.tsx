import { useEffect, useState } from "react";
import { useAuth } from "@/shared/context/AuthContext";
import { apiFetch } from "@/shared/utils/apiClient";
import type {
  ApiDepartmentSubjectAssignment,
  ApiRemedialTerm,
} from "@/shared/types/api";

interface Teacher {
  id: number;
  name: string;
  email: string;
  phone: string;
  display: string;
}



export function DepartmentAssignmentsPage() {
  const { user } = useAuth();
  const [currentTerm, setCurrentTerm] = useState<ApiRemedialTerm | null>(null);
  const [rows, setRows] = useState<ApiDepartmentSubjectAssignment[]>([]);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);
  const [editingSubjectId, setEditingSubjectId] = useState<number | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [loadingTeachers, setLoadingTeachers] = useState(false);
  const [form, setForm] = useState({
    teacher_id: "",
  });

  useEffect(() => {
    if (!user?.departmentId) return;
    fetchRows();
  }, [user?.departmentId]);

  async function fetchRows() {
    try {
      setLoading(true);
      setError(null);

      const termRes = await apiFetch<{ data: ApiRemedialTerm }>(
        "/department/remedial-terms/current",
      );
      const term = termRes.data;
      setCurrentTerm(term);

      const res = await apiFetch<{ data: ApiDepartmentSubjectAssignment[] }>(
        `/department/subject-assignments?remedial_term_id=${term.id}`,
      );
      setRows(res.data || []);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setCurrentTerm(null);
      setRows([]);
      setError("Lỗi tải danh sách: " + msg);
    } finally {
      setLoading(false);
    }
  }

  async function fetchTeachers(subjectId: number) {
    try {
      setLoadingTeachers(true);
      const res = await apiFetch<{ data: Teacher[] }>(
        `/department/subjects/${subjectId}/teachers`,
      );
      setTeachers(res.data || []);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      console.error("Lỗi tải danh sách giáo viên: " + msg);
    } finally {
      setLoadingTeachers(false);
    }
  }

  function openAssignModal(row: ApiDepartmentSubjectAssignment) {
    if (!row.can_assign_lecturer) return;
    setEditingSubjectId(row.subject_id);
    setForm({
      teacher_id: "",
    });
    fetchTeachers(row.subject_id);
    setIsModalOpen(true);
  }

  function handleSelectTeacher(teacherId: string) {
    setForm((f) => ({ ...f, teacher_id: teacherId }));
  }

  async function saveEdit() {
    if (editingSubjectId === null || currentTerm === null) return;
    if (!form.teacher_id) {
      setError("Vui lòng chọn giáo viên");
      return;
    }
    setError(null);
    setMessage(null);
    setIsSubmitting(true);
    try {
      const res = await apiFetch<{
        data: { updated_count: number };
        message: string;
      }>(`/department/subjects/${editingSubjectId}/lecturer`, {
        method: "PATCH",
        data: {
          teacher_id: parseInt(form.teacher_id),
          remedial_term_id: currentTerm.id,
        },
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
    } finally {
      setIsSubmitting(false);
    }
  }

  function closeModal() {
    if (isSubmitting) return;
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
        Phân công giảng viên hiệu lực sau thời gian sinh viên đăng ký
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
              <th className="px-3 py-2 text-center">Số SV</th>
              <th className="px-3 py-2">Giảng viên</th>
              <th className="px-3 py-2">SĐT</th>
              <th className="px-3 py-2">Email</th>
              <th className="px-3 py-2 w-24"></th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td
                  colSpan={10}
                  className="px-3 py-6 text-center text-gray-500"
                >
                  Đang tải...
                </td>
              </tr>
            ) : !currentTerm ? (
              <tr>
                <td
                  colSpan={10}
                  className="px-3 py-6 text-center text-gray-500 italic"
                >
                  Không có đợt phụ đạo hiện tại để phân công giảng viên.
                </td>
              </tr>
            ) : rows.length === 0 ? (
              <tr>
                <td
                  colSpan={10}
                  className="px-3 py-6 text-center text-gray-500 italic"
                >
                  Chưa có môn nào có đăng ký phụ đạo trong đợt hiện tại.
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
                    {row.lecturer_phone || "—"}
                  </td>
                  <td className="px-3 py-2 text-xs text-gray-600">
                    {row.lecturer_email || "—"}
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
              Gán giảng viên phụ đạo
            </div>

            <div className="space-y-4 p-4">
              <div className="grid grid-cols-12 items-center gap-3">
                <label className="col-span-12 sm:col-span-3 text-sm font-semibold">
                  Chọn giáo viên:
                </label>
                <div className="col-span-12 sm:col-span-9">
                  <select
                    className="w-full rounded border px-3 py-2 text-sm"
                    value={form.teacher_id}
                    onChange={(e) => handleSelectTeacher(e.target.value)}
                    disabled={loadingTeachers}
                  >
                    <option value="">
                      {loadingTeachers ? "Đang tải..." : "-- Chọn giáo viên --"}
                    </option>
                    {teachers.map((teacher) => (
                      <option key={teacher.id} value={teacher.id}>
                        {teacher.display}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  className="rounded border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
                  onClick={closeModal}
                  disabled={isSubmitting}
                >
                  Hủy
                </button>
                <button
                  type="button"
                  className="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-400"
                  onClick={saveEdit}
                  disabled={isSubmitting}
                >
                  {isSubmitting && (
                    <svg
                      className="h-4 w-4 animate-spin"
                      viewBox="0 0 24 24"
                      fill="none"
                      aria-hidden="true"
                    >
                      <circle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="4"
                        className="opacity-25"
                      />
                      <path
                        d="M22 12a10 10 0 0 1-10 10"
                        stroke="currentColor"
                        strokeWidth="4"
                        strokeLinecap="round"
                        className="opacity-75"
                      />
                    </svg>
                  )}
                  <span>{isSubmitting ? "Đang lưu..." : "Lưu"}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
