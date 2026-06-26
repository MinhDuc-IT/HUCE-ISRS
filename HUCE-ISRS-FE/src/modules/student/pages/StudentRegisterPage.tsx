import { useMemo, useState, useEffect } from "react";
import { useAuth } from "@/shared/context/AuthContext";
import { apiFetch } from "@/shared/utils/apiClient";
import type {
  ApiRemedialTerm,
  ApiStudentRegistration,
  ApiTermRegisteredSubject,
} from "@/shared/types/api";

export function StudentRegisterPage() {
  const { user } = useAuth();

  const [registrations, setRegistrations] = useState<ApiStudentRegistration[]>(
    [],
  );
  const [registerableSubjects, setRegisterableSubjects] = useState<
    ApiTermRegisteredSubject[]
  >([]);
  const [currentTermId, setCurrentTermId] = useState<number | null>(null);
  const [loadingData, setLoadingData] = useState(true);

  const [selectedToRegister, setSelectedToRegister] = useState<
    Record<string, boolean>
  >({});
  const [selectedToCancel, setSelectedToCancel] = useState<
    Record<string, boolean>
  >({});
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (!user) return;
    fetchData();
  }, [user]);

  async function fetchData() {
    if (!user) return;
    try {
      setLoadingData(true);
      let termId: number | null = null;
      try {
        const termRes = await apiFetch<{ data: ApiRemedialTerm }>(
          "/student/remedial-terms/current",
        );
        termId = termRes.data?.id ?? null;
      } catch {
        termId = null;
      }
      setCurrentTermId(termId);

      const [regsResponse, subjectsResponse] = await Promise.all([
        termId != null
          ? apiFetch<{ data: ApiStudentRegistration[] }>(
              `/student/me/remedial-registrations?remedial_term_id=${termId}`,
            )
          : Promise.resolve({ data: [] as ApiStudentRegistration[] }),
        apiFetch<{ data: ApiTermRegisteredSubject[] }>(
          "/student/me/term-registered-subjects",
        ),
      ]);

      setRegistrations(regsResponse.data || []);
      setRegisterableSubjects(subjectsResponse.data || []);
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setError("Lỗi khi tải dữ liệu từ máy chủ: " + msg);
    } finally {
      setLoadingData(false);
    }
  }

  const registeredCourseCodes = useMemo(() => {
    return new Set(registrations.map((r) => r.course_code));
  }, [registrations]);

  function handleToggleRegister(courseCode: string, checked: boolean) {
    setSelectedToRegister((s) => ({ ...s, [courseCode]: checked }));
  }

  function handleToggleCancel(registrationId: number, checked: boolean) {
    setSelectedToCancel((s) => ({ ...s, [registrationId]: checked }));
  }

  async function handleSaveRegistration() {
    setMessage(null);
    setError(null);
    if (!user) return;

    const courseCodes = Object.entries(selectedToRegister)
      .filter(([, v]) => v)
      .map(([k]) => k);

    if (courseCodes.length === 0) {
      setError("Vui lòng chọn ít nhất 1 môn để đăng ký.");
      return;
    }

    try {
      setIsSubmitting(true);

      await apiFetch("/student/me/remedial-registrations", {
        method: "POST",
        data: { course_codes: courseCodes },
      });

      setMessage(`Đã đăng ký thành công ${courseCodes.length} môn học.`);
      setSelectedToRegister({});
      await fetchData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setError("Lỗi khi lưu đăng ký: " + msg);
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleDeleteRegistration() {
    setMessage(null);
    setError(null);

    const regIds = Object.entries(selectedToCancel)
      .filter(([, v]) => v)
      .map(([k]) => Number(k));

    if (regIds.length === 0) {
      setError("Vui lòng chọn ít nhất 1 môn để hủy đăng ký.");
      return;
    }

    try {
      setIsSubmitting(true);
      for (const regId of regIds) {
        const deleteUrl =
          currentTermId != null
            ? `/student/me/remedial-registrations/${regId}?remedial_term_id=${currentTermId}`
            : `/student/me/remedial-registrations/${regId}`;
        await apiFetch(deleteUrl, {
          method: "DELETE",
        });
      }

      setMessage("Đã hủy đăng ký thành công.");
      setSelectedToCancel({});
      await fetchData();
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Lỗi không xác định";
      setError("Lỗi khi hủy đăng ký: " + msg);
    } finally {
      setIsSubmitting(false);
    }
  }

  if (!user) return null;

  return (
    <div className="space-y-6">
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
        <div className="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t">
          <h2 className="text-lg font-medium text-gray-800">
            Danh sách môn học đã đăng ký
          </h2>
          <button
            type="button"
            onClick={handleDeleteRegistration}
            disabled={isSubmitting || loadingData}
            className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-4 py-1.5 rounded text-sm font-medium transition-colors disabled:opacity-70"
          >
            {isSubmitting ? "Đang xóa..." : "Xóa đăng ký"}
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200 text-center">
              <tr>
                <th className="px-4 py-3 w-12">Chọn</th>
                <th className="px-4 py-3 border-l border-gray-200">Mã MH</th>
                <th className="px-4 py-3 border-l border-gray-200">
                  Tên môn học
                </th>
                <th className="px-4 py-3 border-l border-gray-200">
                  Ngày đăng ký
                </th>
              </tr>
            </thead>
            <tbody>
              {loadingData ? (
                <tr>
                  <td
                    colSpan={5}
                    className="px-4 py-6 text-center text-gray-500"
                  >
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : registrations.length === 0 ? (
                <tr>
                  <td
                    colSpan={4}
                    className="px-4 py-6 text-center text-gray-500 italic"
                  >
                    Chưa đăng ký môn học nào.
                  </td>
                </tr>
              ) : (
                registrations.map((reg) => (
                  <tr
                    key={reg.id}
                    className="border-b border-gray-100 hover:bg-gray-50 text-center"
                  >
                    <td className="px-4 py-2">
                      <input
                        type="checkbox"
                        className="w-4 h-4 rounded border-gray-300"
                        checked={Boolean(selectedToCancel[reg.id])}
                        onChange={(e) =>
                          handleToggleCancel(reg.id, e.target.checked)
                        }
                      />
                    </td>
                    <td className="px-4 py-2">{reg.course_code}</td>
                    <td className="px-4 py-2 text-left">
                      {reg.course_name ?? "—"}
                    </td>
                    <td className="px-4 py-2">
                      {reg.registration_date
                        ? new Date(reg.registration_date).toLocaleDateString(
                            "vi-VN",
                          )
                        : "—"}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      <div className="bg-white rounded border border-gray-200 shadow-sm">
        <div className="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t">
          <h2 className="text-lg font-medium text-gray-800">
            Danh sách môn học chính quy (kỳ đợt phụ đạo hiện tại)
          </h2>
          <button
            type="button"
            onClick={handleSaveRegistration}
            disabled={isSubmitting || loadingData}
            className="bg-[#1976d2] hover:bg-[#1565c0] text-white px-4 py-1.5 rounded text-sm font-medium transition-colors disabled:opacity-70"
          >
            {isSubmitting ? "Đang lưu..." : "Lưu đăng ký"}
          </button>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-sm text-left text-gray-700">
            <thead className="text-xs text-gray-700 bg-gray-100 border-b border-gray-200 text-center">
              <tr>
                <th className="px-4 py-3 w-12">Chọn</th>
                <th className="px-4 py-3 border-l border-gray-200">Mã MH</th>
                <th className="px-4 py-3 border-l border-gray-200">
                  Tên môn học
                </th>
                <th className="px-4 py-3 border-l border-gray-200">Lớp HP</th>
                <th className="px-4 py-3 border-l border-gray-200">STC</th>
                <th className="px-4 py-3 border-l border-gray-200">Ngày thi</th>
              </tr>
            </thead>
            <tbody>
              {loadingData ? (
                <tr>
                  <td
                    colSpan={6}
                    className="px-4 py-6 text-center text-gray-500"
                  >
                    Đang tải dữ liệu...
                  </td>
                </tr>
              ) : registerableSubjects.length === 0 ? (
                <tr>
                  <td
                    colSpan={6}
                    className="px-4 py-6 text-center text-gray-500 italic"
                  >
                    Không có môn học chính quy nào trong kỳ trùng với đợt phụ
                    đạo hiện tại.
                  </td>
                </tr>
              ) : (
                registerableSubjects.map((subject) => {
                  const isRegistered = registeredCourseCodes.has(
                    subject.course_code,
                  );
                  const isNotEligible = subject.credits <= 1;
                  const disabled = isRegistered || isNotEligible;

                  return (
                    <tr
                      key={subject.course_code}
                      className={`border-b border-gray-100 hover:bg-gray-50 text-center ${isRegistered ? "bg-green-50/30" : ""} ${isNotEligible && !isRegistered ? "opacity-60 bg-gray-50" : ""}`}
                      title={
                        isNotEligible && !isRegistered
                          ? "Môn học có số tín chỉ <= 1 không được đăng ký phụ đạo"
                          : undefined
                      }
                    >
                      <td className="px-4 py-2">
                        <input
                          type="checkbox"
                          className="w-4 h-4 rounded border-gray-300 disabled:cursor-not-allowed"
                          disabled={disabled}
                          checked={
                            isRegistered ||
                            (!isNotEligible &&
                              Boolean(selectedToRegister[subject.course_code]))
                          }
                          onChange={(e) =>
                            handleToggleRegister(
                              subject.course_code,
                              e.target.checked,
                            )
                          }
                        />
                      </td>
                      <td className="px-4 py-2">{subject.course_code}</td>
                      <td className="px-4 py-2 text-left">
                        {subject.subject_name}
                      </td>
                      <td className="px-4 py-2">
                        {subject.lop_du_kien || "—"}
                      </td>
                      <td className="px-4 py-2">{subject.credits}</td>
                      <td className="px-4 py-2">
                        {subject.exam_date
                          ? new Date(subject.exam_date).toLocaleDateString(
                              "vi-VN",
                            )
                          : "—"}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
