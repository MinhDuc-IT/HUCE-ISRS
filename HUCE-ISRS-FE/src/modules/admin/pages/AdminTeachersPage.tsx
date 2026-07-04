import { useEffect, useState } from "react";
import { apiFetch } from "@/shared/utils/apiClient";

interface Teacher {
  id: string;
  department_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  created_at?: string;
  updated_at?: string;
}

interface Department {
  id: string | number;
  name?: string;
  department_name?: string;
  faculty_name?: string;
}

export function AdminTeachersPage() {
  const [teachers, setTeachers] = useState<Teacher[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [formData, setFormData] = useState({
    department_id: "",
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
  });

  const [searchTerm, setSearchTerm] = useState("");
  const [selectedDepartment, setSelectedDepartment] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage] = useState(10);

  useEffect(() => {
    fetchData();
  }, []);

  // Debug log khi departments thay đổi
  useEffect(() => {
    console.log("Departments state updated:", {
      count: departments.length,
      departments: departments,
    });
  }, [departments]);

  async function fetchData() {
    try {
      setLoading(true);
      const [teachersRes, deptRes] = await Promise.all([
        apiFetch<{ data: Teacher[] }>("/admin/teachers"),
        apiFetch<{ data: Department[] }>("/admin/departments"),
      ]);

      console.log("Raw deptRes:", deptRes);

      setTeachers(teachersRes.data || []);
      // Fallback: nếu response không có key 'data', lấy trực tiếp từ response
      const deptsData = Array.isArray(deptRes) ? deptRes : deptRes.data || [];
      console.log("Departments loaded:", deptsData);
      setDepartments(deptsData);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err);
      setError("Lỗi tải dữ liệu: " + msg);
    } finally {
      setLoading(false);
    }
  }

  function openModal(teacher?: Teacher) {
    if (teacher) {
      setEditingId(teacher.id);
      setFormData({
        department_id: teacher.department_id || "",
        first_name: teacher.first_name || "",
        last_name: teacher.last_name || "",
        email: teacher.email || "",
        phone: teacher.phone || "",
      });
    } else {
      setEditingId(null);
      setFormData({
        department_id: "",
        first_name: "",
        last_name: "",
        email: "",
        phone: "",
      });
    }
    setIsModalOpen(true);
    setError(null);
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingId(null);
    setFormData({
      department_id: "",
      first_name: "",
      last_name: "",
      email: "",
      phone: "",
    });
    setError(null);
  }

  async function handleSave(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (
      !formData.department_id ||
      !formData.first_name ||
      !formData.last_name ||
      !formData.email ||
      !formData.phone
    ) {
      setError("Vui lòng điền tất cả các trường");
      return;
    }

    try {
      setIsSubmitting(true);

      if (editingId) {
        await apiFetch(`/admin/teachers/${editingId}`, {
          method: "PATCH",
          data: formData,
        });
        setMessage("Cập nhật giảng viên thành công");
      } else {
        await apiFetch("/admin/teachers", {
          method: "POST",
          data: formData,
        });
        setMessage("Thêm giảng viên thành công");
      }

      setTimeout(() => setMessage(null), 3000);
      closeModal();
      fetchData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err);
      setError(msg);
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDelete(id: string) {
    if (!window.confirm("Bạn chắc chắn muốn xóa giảng viên này?")) return;

    try {
      await apiFetch(`/admin/teachers/${id}`, {
        method: "DELETE",
      });
      setMessage("Xóa giảng viên thành công");
      setTimeout(() => setMessage(null), 3000);
      fetchData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : String(err);
      setError("Lỗi xóa giảng viên: " + msg);
    }
  }

  const filteredTeachers = teachers.filter((t) => {
    const matchSearch =
      `${t.first_name} ${t.last_name}`
        .toLowerCase()
        .includes(searchTerm.toLowerCase()) ||
      t.email.toLowerCase().includes(searchTerm.toLowerCase());

    const matchDepartment =
      !selectedDepartment ||
      t.department_id.toString() === selectedDepartment.toString();

    return matchSearch && matchDepartment;
  });

  // Reset to page 1 when search term or department changes
  useEffect(() => {
    setCurrentPage(1);
  }, [searchTerm, selectedDepartment]);

  // Pagination logic
  const totalPages = Math.ceil(filteredTeachers.length / itemsPerPage);
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const paginatedTeachers = filteredTeachers.slice(startIndex, endIndex);

  const getDepartmentName = (deptId: string) => {
    const dept = departments.find((d) => d.id.toString() === deptId.toString());
    if (!dept) return "N/A";
    return dept.name || dept.department_name || dept.faculty_name || "N/A";
  };

  const getDisplayName = (dept: Department) => {
    return dept.name || dept.department_name || dept.faculty_name || "N/A";
  };

  return (
    <div className="space-y-4">
      {/* Debug Info */}
      <div className="text-xs text-gray-500 px-2">
        [Debug] Loading: {loading.toString()} | Departments:{" "}
        {departments.length} | Teachers: {teachers.length}
      </div>

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

      <div className="bg-white rounded border border-gray-200 shadow-sm">
        <div className="p-4 border-b border-gray-200 bg-gray-50 flex flex-col gap-3 rounded-t">
          <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div className="text-sm font-semibold text-gray-700">
              Quản lý Giảng Viên
            </div>
            <button
              onClick={() => {
                if (departments.length === 0) {
                  setError("Vui lòng chờ dữ liệu bộ môn tải xong");
                  return;
                }
                openModal();
              }}
              className="bg-[#1976d2] text-white px-4 py-1.5 rounded text-sm hover:bg-[#1565c0] transition-colors font-medium disabled:opacity-50"
              disabled={loading}
            >
              + Thêm mới
            </button>
          </div>

          <div className="flex flex-col md:flex-row gap-2">
            <select
              value={selectedDepartment}
              onChange={(e) => setSelectedDepartment(e.target.value)}
              className="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2] md:w-48"
            >
              <option value="">-- Tất cả bộ môn --</option>
              {departments.map((d) => (
                <option key={d.id} value={String(d.id)}>
                  {getDisplayName(d)}
                </option>
              ))}
            </select>

            <input
              type="text"
              placeholder="Tìm giảng viên..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#1976d2] flex-1"
            />
          </div>
        </div>

        <div className="overflow-x-auto relative">
          {loading && (
            <div className="absolute inset-0 bg-white/60 z-10 flex items-center justify-center backdrop-blur-[1px]">
              <span className="text-gray-500 font-medium">
                Đang tải dữ liệu...
              </span>
            </div>
          )}
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-y border-gray-200 text-center">
              <tr>
                <th className="px-3 py-3 w-12 border-r border-gray-200">TT</th>
                <th className="px-3 py-3 border-r border-gray-200 text-left">
                  Họ tên
                </th>
                <th className="px-3 py-3 border-r border-gray-200">Email</th>
                <th className="px-3 py-3 border-r border-gray-200">
                  Điện thoại
                </th>
                <th className="px-3 py-3 border-r border-gray-200">Bộ môn</th>
                <th className="px-3 py-3">Hành động</th>
              </tr>
            </thead>
            <tbody>
              {paginatedTeachers.length === 0 && !loading ? (
                <tr>
                  <td
                    colSpan={6}
                    className="px-4 py-6 text-center text-gray-500 italic"
                  >
                    {filteredTeachers.length === 0
                      ? "Không có giảng viên nào."
                      : "Không có dữ liệu trên trang này."}
                  </td>
                </tr>
              ) : (
                paginatedTeachers.map((teacher, index) => (
                  <tr
                    key={teacher.id}
                    className="border-b border-gray-100 hover:bg-gray-50"
                  >
                    <td className="px-3 py-3 text-center border-r border-gray-100">
                      {startIndex + index + 1}
                    </td>
                    <td className="px-3 py-3 border-r border-gray-100 text-left">
                      <span className="font-medium">
                        {teacher.first_name} {teacher.last_name}
                      </span>
                    </td>
                    <td className="px-3 py-3 border-r border-gray-100 text-center text-[#1976d2]">
                      {teacher.email}
                    </td>
                    <td className="px-3 py-3 border-r border-gray-100 text-center">
                      {teacher.phone}
                    </td>
                    <td className="px-3 py-3 border-r border-gray-100 text-center">
                      {getDepartmentName(teacher.department_id)}
                    </td>
                    <td className="px-3 py-3 text-center">
                      <div className="flex gap-2 justify-center">
                        <button
                          onClick={() => openModal(teacher)}
                          className="text-[#1976d2] hover:text-[#1565c0] font-medium text-xs hover:underline"
                        >
                          Sửa
                        </button>
                        <span className="text-gray-300">|</span>
                        <button
                          onClick={() => handleDelete(teacher.id)}
                          className="text-red-600 hover:text-red-800 font-medium text-xs hover:underline"
                        >
                          Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination Footer */}
        <div className="p-4 border-t border-gray-200 bg-gray-50 rounded-b flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div className="text-xs text-gray-600">
            Hiển thị{" "}
            <span className="font-semibold">
              {paginatedTeachers.length > 0 ? startIndex + 1 : 0}
            </span>{" "}
            đến{" "}
            <span className="font-semibold">
              {Math.min(endIndex, filteredTeachers.length)}
            </span>{" "}
            trong{" "}
            <span className="font-semibold">{filteredTeachers.length}</span> kết
            quả
            {selectedDepartment && (
              <span className="ml-2 text-[#1976d2]">
                (bộ môn: "{getDepartmentName(selectedDepartment)}")
              </span>
            )}
            {searchTerm && (
              <span className="ml-2 text-[#1976d2]">(tìm: "{searchTerm}")</span>
            )}
          </div>

          {totalPages > 1 && (
            <div className="flex items-center gap-1 flex-wrap justify-center md:justify-end">
              <button
                onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                disabled={currentPage === 1}
                className="px-3 py-1.5 border border-gray-300 rounded text-xs hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                ← Trước
              </button>

              <div className="flex gap-1">
                {Array.from({ length: totalPages }, (_, i) => i + 1)
                  .filter(
                    (page) =>
                      page === 1 ||
                      page === totalPages ||
                      (page >= currentPage - 1 && page <= currentPage + 1),
                  )
                  .map((page, idx, arr) => (
                    <div key={page}>
                      {idx > 0 && arr[idx - 1] !== page - 1 && (
                        <span className="px-2 py-1.5 text-xs text-gray-500">
                          ...
                        </span>
                      )}
                      <button
                        onClick={() => setCurrentPage(page)}
                        className={`px-3 py-1.5 rounded text-xs font-medium transition-colors ${
                          currentPage === page
                            ? "bg-[#1976d2] text-white"
                            : "border border-gray-300 hover:bg-gray-100"
                        }`}
                      >
                        {page}
                      </button>
                    </div>
                  ))}
              </div>

              <button
                onClick={() =>
                  setCurrentPage(Math.min(totalPages, currentPage + 1))
                }
                disabled={currentPage === totalPages}
                className="px-3 py-1.5 border border-gray-300 rounded text-xs hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Sau →
              </button>
            </div>
          )}
        </div>
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
          <div className="bg-white w-full max-w-md rounded shadow-xl flex flex-col animate-in fade-in zoom-in duration-200">
            <div className="bg-[#1976d2] text-white px-4 py-3 rounded-t flex justify-between items-center">
              <h3 className="font-medium text-sm text-center flex-1">
                {editingId ? "Cập nhật giảng viên" : "Thêm giảng viên mới"}
              </h3>
              <button
                onClick={closeModal}
                className="text-white hover:text-gray-200 rounded-full p-1 leading-none"
              >
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            <form onSubmit={handleSave} className="p-6 space-y-4">
              {error && (
                <div className="rounded border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                  {error}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Bộ môn:
                </label>
                <select
                  value={formData.department_id}
                  onChange={(e) =>
                    setFormData({ ...formData, department_id: e.target.value })
                  }
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                >
                  <option value="">
                    {loading ? "-- Đang tải bộ môn --" : "-- Chọn bộ môn --"}
                  </option>
                  {departments.length === 0 && !loading && (
                    <option value="" disabled>
                      (Không có bộ môn)
                    </option>
                  )}
                  {departments.map((d) => (
                    <option key={d.id} value={String(d.id)}>
                      {getDisplayName(d)}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Họ:
                </label>
                <input
                  type="text"
                  value={formData.first_name}
                  onChange={(e) =>
                    setFormData({ ...formData, first_name: e.target.value })
                  }
                  placeholder="Ví dụ: Nguyễn"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Tên:
                </label>
                <input
                  type="text"
                  value={formData.last_name}
                  onChange={(e) =>
                    setFormData({ ...formData, last_name: e.target.value })
                  }
                  placeholder="Ví dụ: Văn A"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Email:
                </label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) =>
                    setFormData({ ...formData, email: e.target.value })
                  }
                  placeholder="example@huce.edu.vn"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Điện thoại:
                </label>
                <input
                  type="text"
                  value={formData.phone}
                  onChange={(e) =>
                    setFormData({ ...formData, phone: e.target.value })
                  }
                  placeholder="0123456789"
                  className="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#1976d2] focus:ring-1 focus:ring-[#1976d2]"
                  required
                />
              </div>

              <div className="pt-2 flex justify-end gap-2">
                <button
                  type="button"
                  onClick={closeModal}
                  className="border border-gray-300 text-gray-700 px-4 py-1.5 rounded text-sm hover:bg-gray-50 transition-colors"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-6 py-1.5 rounded text-sm font-medium shadow-sm transition-colors disabled:opacity-70"
                >
                  {isSubmitting
                    ? "Đang lưu..."
                    : editingId
                      ? "Cập nhật"
                      : "Thêm mới"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
