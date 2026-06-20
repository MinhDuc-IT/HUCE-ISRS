import { Navigate, Route, Routes } from 'react-router-dom'
import { LoginPage } from '@/modules/auth/pages/LoginPage'
import { AdminDashboardPage } from '@/modules/admin/pages/AdminDashboardPage'
import { AdminRegistrationsPage } from '@/modules/admin/pages/AdminRegistrationsPage'
import { AdminSendEmailPage } from '@/modules/admin/pages/AdminSendEmailPage'
import { StatisticsCohortPage } from '@/modules/admin/pages/StatisticsCohortPage'
import { TeachingPaymentsPage } from '@/modules/admin/pages/TeachingPaymentsPage'
import { SystemSettingsPage } from '@/modules/admin/pages/SystemSettingsPage'
import { CohortFormPage } from '@/modules/admin/pages/CohortFormPage'
import { CohortListPage } from '@/modules/admin/pages/CohortListPage'
import { DepartmentListPage } from '@/modules/admin/pages/DepartmentListPage'
import { UserFormPage } from '@/modules/admin/pages/UserFormPage'
import { UserListPage } from '@/modules/admin/pages/UserListPage'
import { DepartmentAssignmentsPage } from '@/modules/department/pages/DepartmentAssignmentsPage'
import { DepartmentDashboardPage } from '@/modules/department/pages/DepartmentDashboardPage'
import { DepartmentProfilePage } from '@/modules/department/pages/DepartmentProfilePage'
import { StudentDashboardPage } from '@/modules/student/pages/StudentDashboardPage'
import { StudentInstructorsPage } from '@/modules/student/pages/StudentInstructorsPage'
import { StudentRegisterPage } from '@/modules/student/pages/StudentRegisterPage'
import { StudentRegistrationsPage } from '@/modules/student/pages/StudentRegistrationsPage'
import { RequireAuth } from '@/shared/components/RequireAuth'
import { AppShellLayout } from '@/shared/layouts/AppShellLayout'
import { HomeRedirect } from '@/app/HomeRedirect'

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={<HomeRedirect />} />

      <Route element={<RequireAuth allowedRoles={['admin']} />}>
        <Route path="/admin" element={<AppShellLayout role="admin" />}>
          <Route index element={<AdminDashboardPage />} />
          <Route path="cohorts" element={<CohortListPage />} />
          <Route path="cohorts/new" element={<CohortFormPage />} />
          <Route path="cohorts/:id/edit" element={<CohortFormPage />} />
          <Route path="users" element={<UserListPage />} />
          <Route path="users/new" element={<UserFormPage />} />
          <Route path="users/:id/edit" element={<UserFormPage />} />
          <Route path="departments" element={<DepartmentListPage />} />
          <Route path="registrations" element={<AdminRegistrationsPage />} />
          <Route path="email-department" element={<AdminSendEmailPage />} />
          <Route path="settings" element={<SystemSettingsPage />} />
          <Route path="statistics" element={<StatisticsCohortPage />} />
          <Route path="teaching-payments" element={<TeachingPaymentsPage />} />
        </Route>
      </Route>

      <Route element={<RequireAuth allowedRoles={['department']} />}>
        <Route path="/department" element={<AppShellLayout role="department" />}>
          <Route index element={<DepartmentDashboardPage />} />
          <Route path="profile" element={<DepartmentProfilePage />} />
          <Route path="assignments" element={<DepartmentAssignmentsPage />} />
        </Route>
      </Route>

      <Route element={<RequireAuth allowedRoles={['student']} />}>
        <Route path="/student" element={<AppShellLayout role="student" />}>
          <Route index element={<StudentDashboardPage />} />
          <Route path="register" element={<StudentRegisterPage />} />
          <Route path="registrations" element={<StudentRegistrationsPage />} />
          <Route path="instructors" element={<StudentInstructorsPage />} />
        </Route>
      </Route>

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}
