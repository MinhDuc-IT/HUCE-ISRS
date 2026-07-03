/** Payload API — chỉ snake_case (Phase 10). */

export type ApiRemedialTerm = {
  id: number;
  name: string;
  year: number;
  semester: number;
  start_date: string | null;
  end_date: string | null;
  registration_start: string | null;
  registration_end: string | null;
  remedial_coefficient?: number;
  price_per_period?: number;
  price_coefficient?: number;
  is_current_term?: boolean;
  status?: number;
  status_name?: string;
  status_logic?: number;
  status_logic_name?: string;
};

export type ApiDepartmentSubjectAssignment = {
  subject_id: number;
  subject_code: string;
  subject_name: string;
  department_name: string;
  credits: number;
  registration_count: number;
  lecture_name?: string | null;
  lecturer_phone?: string | null;
  lecturer_email?: string | null;
  can_assign_lecturer: boolean;
};

export type ApiTermRegisteredSubject = {
  course_code: string;
  subject_name: string;
  credits: number;
  class_section_code?: string;
  lop_du_kien?: string;
  registration_date?: string | null;
  registration_status?: string;
  academic_year_label?: string;
  academic_year?: number;
  semester?: number;
  term_name?: string;
  exam_date?: string | null;
};

export type ApiEligibleSubject = {
  course_code: string;
  subject_name: string;
  credits: number;
  final_score?: number | null;
  letter_grade?: string | null;
};

export type ApiStudentRegistration = {
  id: number;
  student_code?: string;
  course_code: string;
  course_name?: string;
  remedial_term_id?: number;
  remedial_periods?: number;
  registration_date: string;
  lecture_name?: string | null;
  lecturer_phone?: string | null;
  lecturer_email?: string | null;
};

export type ApiAdminRegistrationSummary = {
  remedial_term_id: number;
  remedial_term_name: string;
  subject_id: number;
  subject_code: string;
  subject_name: string;
  student_count: number;
  lecture_name?: string | null;
};

export type ApiAdminRegistrationStudent = {
  id: number;
  student_code?: string;
  student_name?: string;
  class_name?: string | null;
  registration_date?: string;
};

export type ApiDepartmentLoginUser = {
  id: number;
  name: string;
  email: string;
};

export type ApiDepartment = {
  id: number;
  department_code: string;
  department_name: string;
  faculty_code?: string | null;
  faculty_name?: string | null;
  email?: string | null;
  phone?: string | null;
  phone_number?: string | null;
  login_user?: ApiDepartmentLoginUser | null;
};

export type ApiSubject = {
  id: number;
  subject_code: string;
  name: string;
  credits: number | null;
  department_id: number;
};

export type ApiTermStatisticsSummary = {
  remedial_term_id: number;
  remedial_term_name: string;
  distinct_student_count: number;
  courses_with_registration_count: number;
  total_revenue: number;
};

export type ApiTermStatistics = {
  remedial_term_id: number;
  distinct_student_count: number;
  catalog_course_count: number;
  courses_with_registration_count: number;
  assigned_class_count: number;
  total_registrations: number;
  total_revenue: number;
};

export type ApiSystemSetting = {
  id: number | string;
  key: string;
  value: string;
  description?: string;
};
