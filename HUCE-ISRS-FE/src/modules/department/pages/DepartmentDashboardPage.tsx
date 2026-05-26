import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { apiFetch } from "@/shared/utils/apiClient";
import { useAuth } from "@/shared/context/AuthContext";

export function DepartmentDashboardPage() {
  const { user } = useAuth();
  const [regCount, setRegCount] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user?.departmentId) return;
    fetchData();
  }, [user?.departmentId]);

  async function fetchData() {
    try {
      setLoading(true);
      const res = await apiFetch<{ data: unknown[] }>(
        "/department/remedial-registrations",
      );
      setRegCount(res.data?.length ?? 0);
    } catch {
      setRegCount(0);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="space-y-4">
      <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm">
        <h1 className="text-lg font-semibold text-gray-800">
          Xin chào, {user?.displayName}
        </h1>
        <p className="mt-1 text-sm text-gray-500">
          Khu vực quản lý bộ môn — chỉ xem đăng ký thuộc bộ môn của bạn.
        </p>
        <div className="mt-4 flex flex-wrap gap-3">
          <Link
            to="/department/profile"
            className="inline-flex rounded bg-brand-500 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-brand-600"
          >
            Thông tin bộ môn
          </Link>
          <Link
            to="/department/assignments"
            className="inline-flex rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 no-underline hover:bg-gray-50"
          >
            Đăng ký & phân công GV
          </Link>
        </div>
      </div>

      <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm">
        <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
          Đăng ký phụ đạo thuộc bộ môn
        </p>
        <p className="mt-1 text-2xl font-semibold text-gray-800">
          {loading ? "—" : regCount}
        </p>
      </div>
    </div>
  );
}
