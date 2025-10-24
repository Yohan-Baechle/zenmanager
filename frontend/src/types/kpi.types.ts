export interface TeamOption {
  id: number
  name: string
}

export interface EmployeeOption {
  id: number
  firstName: string
  lastName: string
  fullName: string
}

export interface MyTeamsResponse {
  success: boolean
  teams: TeamOption[]
}

export interface TeamEmployeesResponse {
  success: boolean
  employees: EmployeeOption[]
}

export interface ReportsFilters {
  start_date?: string
  end_date?: string
  team_id?: number
  user_id?: number
  employee_count?: number
}

export interface PeriodInfo {
  total_days: number | null
  working_days: number | null
  weekend_days: number | null
}

export interface WorkSchedule {
  start_time: string
  end_time: string
  tolerance_late: number
  tolerance_early_departure: number
  standard_hours_per_day: number
}

export interface KPIs {
  total_working_hours: number
  late_arrivals_count: number
  late_arrivals_rate: number  // Pourcentage de retards
  early_departures_count: number
  present_days_count: number
  absent_days_count: number
  incomplete_days_count: number
  total_exits_count: number
}

export interface ReportsData {
  filters: ReportsFilters
  period: PeriodInfo
  work_schedule: WorkSchedule
  kpis: KPIs
}

export interface ReportsResponse {
  success: boolean
  message: string
  data: ReportsData
  error?: string
}