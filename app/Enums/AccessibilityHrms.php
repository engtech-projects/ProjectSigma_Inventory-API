<?php

namespace App\Enums;

enum AccessibilityHrms: string
{
    case SUPERADMIN = "project sigma:super admin";
    case HRMS_DASHBOARD = "hrms:dashboard";
    case HRMS_ANNOUNCEMENT = "hrms:announcement";
    case HRMS_ATTENDANCE_ATTENDANCEPORTAL = "hrms:attendance_attendance portal";
    case HRMS_ATTENDANCE_DTR = "hrms:attendance_dtr";
    case HRMS_ATTENDANCE_FAILURETOLOG = "hrms:attendance_failure to log";
    case HRMS_ATTENDANCE_FACERECOGNITION = "hrms:attendance_face recognition";
    case HRMS_ATTENDANCE_ATTENDANCELOGIN = "hrms:attendance_attendance login";
    case HRMS_ATTENDANCE_QR = "hrms:attendance_generate QR";
    case HRMS_ATTENDANCE_ATTENDANCE_QR = "hrms:attendance_attendance QR";
    case HRMS_EVENTCALENDAR = "hrms:event calendar";
    case HRMS_EMPLOYEE_201_EDIT = "hrms:employee_201_edit";
    // case HRMS_ = "";
    // case HRMS_ = "";
    // case HRMS_ = "";
    case HRMS_SETUP_USERACCOUNT = "hrms:setup_user account";
    case HRMS_SETUP_DEPARTMENT = "hrms:setup_department";
    case HRMS_SETUP_APPROVALS = "hrms:setup_approvals";
    case HRMS_HMO = "hrms:hmo";
    case HRMS_SETUP_PAGIBIG = "hrms:setup_pag-ibig";
    case HRMS_SETUP_PHILHEALTH = "hrms:setup_philhealth";
    case HRMS_SETUP_SSS = "hrms:setup_sss";
    case HRMS_SETUP_WITHHOLDINGTAX = "hrms:setup_withholding tax";
    case HRMS_SETUP_LEAVES = "hrms:setup_leaves";
    case HRMS_SETUP_POSITION = "hrms:setup_position";
    case HRMS_SETUP_ALLOWANCE = "hrms:setup_allowance";
    case HRMS_SETUP_SETTINGS = "hrms:setup_settings";
    case HRMS_SETUP_SALARY_GRADE = "hrms:setup_salary grade";
    case HRMS_SETUP_PAYROLLPARTICULARS = "hrms:setup_payroll particular terms";
    // case HRMS_ = "";
    // case HRMS_ = "";
    case HRMS_PAYROLL_SALARY_GENERATE_PAYROLL_CHANGE_OF_CHARGING = "hrms:payroll_salary_generate payroll_change of charging";
    case HRMS_PAYROLL_SALARY_GENERATEPAYROLL = "hrms:payroll_salary_generate payroll_form and my requests";
    case HRMS_PAYROLL_SALARY_GENERATEPAYROLL_ALLREQUESTS = "hrms:payroll_salary_generate payroll_all requests";
    case HRMS_PAYROLL_SALARY_GENERATEPAYROLL_MYAPPROVALS = "hrms:payroll_salary_generate payroll_my approvals";
    case HRMS_PAYROLL_SALARY_PAYROLLRECORD = "hrms:payroll_salary_payroll records";
    case HRMS_PAYROLL_13THMONTH_FORMANDMYREQUEST = "hrms:payroll_13th month_form and my requests";
    case HRMS_PAYROLL_13THMONTH_ALLREQUESTS = "hrms:payroll_13th month_all requests";
    case HRMS_PAYROLL_13THMONTH_MYAPPROVALS = "hrms:payroll_13th month_my approvals";
    case HRMS_PAYROLL_13THMONTH_SUMMARY = "hrms:payroll_13th month_summary";
    case HRMS_PAYROLL_ALLOWANCE = "hrms:payroll_allowance";
    case HRMS_PAYROLL_SALARYDISBURSEMENT_FORM = "hrms:payroll_salary disbursement_form and my requests";
    case HRMS_PAYROLL_SALARYDISBURSEMENT_AllREQUESTS = "hrms:payroll_salary disbursement_all requests";
    case HRMS_PAYROLL_SALARYDISBURSEMENT_MYAPPROVALS = "hrms:payroll_salary disbursement_my approvals";
    case HRMS_PAYROLL_SALARYDISBURSEMENT_VIEWPAYSLIPS = "hrms:payroll_salary disbursement_view payslips";
    case HRMS_REPORTS_SSSEMPLOYEEREMITTANCE = "hrms:reports_sss employee remittance";
    case HRMS_REPORTS_PAGIBIGEMPLOYEEREMITTANCE = "hrms:reports_pagibig employee remittance";
    case HRMS_REPORTS_PHILHEALTHEMPLOYEEREMITTANCE = "hrms:reports_philhealth employee remittance";
    case HRMS_REPORTS_SSSGROUPREMITTANCE = "hrms:reports_sss group remittance";
    case HRMS_REPORTS_PAGIBIGGROUPREMITTANCE = "hrms:reports_pagibig group remittance";
    case HRMS_REPORTS_PHILHEALTHGROUPREMITTANCE = "hrms:reports_philhealth group remittance";
    case HRMS_REPORTS_SSSREMITTANCESUMMARY = "hrms:reports_sss remittance summary";
    case HRMS_REPORTS_PAGIBIGREMITTANCESUMMARY = "hrms:reports_pagibig remittance summary";
    case HRMS_REPORTS_PHILHEALTHREMITTANCESUMMARY = "hrms:reports_philhealth remittance summary";
    case HRMS_REPORTS_LOAN = "hrms:reports_loan reports";
    case HRMS_REPORTS_ADMINISTRATIVE = "hrms:reports_administrative reports";
    case HRMS_REPORTS_PORTALMONITORING_OVERTIMEMONITORING = "hrms:reports_portal monitoring_overtime monitoring";
    case HRMS_REPORTS_PORTALMONITORING_SALARYMONITORING = "hrms:reports_portal monitoring_salary monitoring";
    case HRMS_REPORTS_PORTALMONITORING_OVERTIMEMONITORINGSUMMARY = "hrms:reports_portal monitoring_overtime monitoring summary";
    case HRMS_REPORTS_PORTALMONITORING_FAILURETOLOGMONITORING = "hrms:reports_portal monitoring_failure to log monitoring";
    case HRMS_REPORTS_PORTALMONITORING_FAILURETOLOGMONITORINGSUMMARY = "hrms:reports_portal monitoring_failure to log monitoring summary";
    case HRMS_REPORTS_PORTALMONITORING_LEAVEMONITORING = "hrms:reports_portal monitoring_leave monitoring";
    case HRMS_REPORTS_PORTALMONITORING_LEAVEMONITORINGSUMMARY = "hrms:reports_portal monitoring_leave monitoring summary";
    case HRMS_REPORTS_PORTALMONITORING_TRAVELORDERMONITORING = "hrms:reports_portal monitoring_travel order monitoring";
    case HRMS_REPORTS_PORTALMONITORING_TRAVELORDERMONITORINGSUMMARY = "hrms:reports_portal monitoring_travel order monitoring summary";
    case HRMS_REPORTS_PORTALMONITORING_MANPOWERREQUESTMONITORING = "hrms:reports_portal monitoring_manpower request monitoring";
    case HRMS_REPORTS_PORTALMONITORING_MANPOWERREQUESTMONITORINGSUMMARY = "hrms:reports_portal monitoring_manpower request monitoring summary";
    case HRMS_REPORTS_PORTALMONITORING_PANTERMINATIONMONITORING = "hrms:reports_portal monitoring_pan termination monitoring";
    case HRMS_REPORTS_PORTALMONITORING_PANTRANSFERMONITORING = "hrms:reports_portal monitoring_pan transfer monitoring";
    case HRMS_REPORTS_PORTALMONITORING_PANPROMOTIONMONITORING = "hrms:reports_portal monitoring_pan promotion monitoring";
    case HRMS_REPORTS_PORTALMONITORING_ATTENDANCELOGMONITORING = "hrms:reports_portal monitoring_attendance log monitoring";
    case HRMS_REPORTS_PORTALMONITORING_ATTENDANCELOGMONITORINGSUMMARY = "hrms:reports_portal monitoring_attendance log monitoring summary";
    case HRMS_SCHEDULE_DEPARTMENT = "hrms:schedule_department";
    case HRMS_SCHEDULE_EMPLOYEE = "hrms:schedule_employee";
    case HRMS_SCHEDULE_PROJECT = "hrms:schedule_project";
    case HRMS_LOCATION_EMPLOYEES = "hrms:location employees";
    case HRMS_REQUEST_VOID_CREATEREQUEST = "hrms:request void_my requests and create request";
    case HRMS_REQUEST_VOID_ALLREQUEST = "hrms:request void_all requests";
    case HRMS_REQUEST_VOID_MYAPPROVALS = "hrms:request void_my approvals";
    case HRMS_EMPLOYEE_201_PIS = "hrms:employee_201_pis";
    case HRMS_EMPLOYEE_201_STAFFINFOSHEET = "hrms:employee_201_staff information sheet";
    case HRMS_EMPLOYEE_201_DOCUMENTSMEMO = "hrms:employee_201_documents and memos";
    case HRMS_EMPLOYEE_201_ID = "hrms:employee_201_id";
    case HRMS_EMPLOYEE_PAN_FORM_NEWHIRE = "hrms:employee_pan_form_new hire";
    case HRMS_EMPLOYEE_PAN_FORM_TRANSFER = "hrms:employee_pan_form_transfer";
    case HRMS_EMPLOYEE_PAN_FORM_PROMOTION = "hrms:employee_pan_form_promotion";
    case HRMS_EMPLOYEE_PAN_FORM_TERMINATION = "hrms:employee_pan_form_termination";
    case HRMS_EMPLOYEE_PAN_FORM_BULKTRANSFER = "hrms:employee_pan_form_bulk transfer";
    case HRMS_EMPLOYEE_PAN_FORM_MEMO = "hrms:employee_pan_form_memo";
    // case HRMS_EMPLOYEE_PAN_MYREQUESTS = "hrms:employee_pan_my request";
    case HRMS_EMPLOYEE_PAN_ALLREQUESTS = "hrms:employee_pan_all request";
    case HRMS_EMPLOYEE_PAN_MYAPPROVALS = "hrms:employee_pan_approval";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_FORM = "hrms:employee_manpower request_form";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_ALLREQUESTS = "hrms:employee_manpower request_all request";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_MYAPPROVALS = "hrms:employee_manpower request_approval";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_MYREQUESTS = "hrms:employee_manpower request_my request";

    case HRMS_EMPLOYEE_MANPOWERREQUEST_FORM_REQUESTS = "hrms:employee_manpower request_form and my requests";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_OPEN_POSITIONS = "hrms:employee_manpower request_open positions";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_APPLICATION_FORM = "hrms:employee_job applicants_application form";
    case HRMS_EMPLOYEE_MANPOWERREQUEST_APPLICANTS_JOB = "hrms:employee_job applicants_job applicants";
    // case HRMS_LOANSANDADVANCES_CASHADVANCE = "hrms:loans and advances_cash advance";
    case HRMS_LOANSANDADVANCES_CASHADVANCE_FORM = "hrms:loans and advances_cash advance_forms and my requests";
    case HRMS_LOANSANDADVANCES_CASHADVANCE_ALLREQUESTS = "hrms:loans and advances_cash advance_all requests";
    case HRMS_LOANSANDADVANCES_CASHADVANCE_MYAPPROVALS = "hrms:loans and advances_cash advance_my approvals";
    case HRMS_LOANSANDADVANCES_LOANS = "hrms:loans and advances_loans"; // TO DELETE
    case HRMS_LOANSANDADVANCES_LOANS_FORMS = "hrms:loans and advances_loans_forms";
    case HRMS_LOANSANDADVANCES_LOANS_ALLREQUESTS = "hrms:loans and advances_loans_list";
    case HRMS_LOANSANDADVANCES_LOANS_PAYMENTS = "hrms:loans and advances_loans_payments";
    case HRMS_LOANSANDADVANCES_OTHERDEDUCTIONS = "hrms:loans and advances_other deductions"; // TO DELETE
    case HRMS_LOANSANDADVANCES_OTHERDEDUCTIONS_FORMS = "hrms:loans and advances_other deductions_forms";
    case HRMS_LOANSANDADVANCES_OTHERDEDUCTIONS_ALLREQUESTS = "hrms:loans and advances_other deductions_list";
    case HRMS_ATTENDANCE_ATTENDANCELOGS = "hrms:attendance_attendance logs";
    case HRMS_ATTENDANCE_ATTENDANCELOGSDELETE = "hrms:attendance_attendance logs delete";
    case HRMS_LNOTNTO_LEAVE_FORM = "hrms:leaves and overtime_leave_form";
    case HRMS_LNOTNTO_LEAVE_ALLREQUESTS = "hrms:leaves and overtime_leave_list";
    case HRMS_LNOTNTO_LEAVE_MYAPPROVALS = "hrms:leaves and overtime_leave_my approvals";
    case HRMS_LNOTNTO_OVERTIME_FORM = "hrms:leaves and overtime_overtime_form";
    case HRMS_LNOTNTO_OVERTIME_ALLREQUESTS = "hrms:leaves and overtime_overtime_list";
    case HRMS_LNOTNTO_OVERTIME_MYREQUESTS = "hrms:leaves and overtime_overtime_my request";
    case HRMS_LNOTNTO_OVERTIME_MYAPPROVALS = "hrms:leaves and overtime_overtime_my approvals";
    case HRMS_LNOTNTO_TRAVELORDER_FORM = "hrms:leaves and overtime_travel order_form";
    case HRMS_LNOTNTO_TRAVELORDER_ALLREQUESTS = "hrms:leaves and overtime_travel order_list";
    case HRMS_LNOTNTO_TRAVELORDER_MYREQUESTS = "hrms:leaves and overtime_travel order_my request";
    case HRMS_LNOTNTO_TRAVELORDER_MYAPPROVALS = "hrms:leaves and overtime_travel order_my approvals";
    case HRMS_EMPLOYEE_JOBAPPLICANT = "hrms:employee_job applicant";
    // case HRMS_ = "hrms:attendance_biometrics";

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->name] = $case->value;
        }
        return $array;
    }

    public static function toArraySwapped(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->name;
        }
        return $array;
    }
}
