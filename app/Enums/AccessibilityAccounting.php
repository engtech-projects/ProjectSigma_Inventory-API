<?php

namespace App\Enums;

enum AccessibilityAccounting: string
{
    case ACCOUNTING_DASHBOARD = "accounting:dashboard";

    //Accounting Setup
    case ACCOUNTING_SETUP_APPROVALS = "accounting:setup_approvals";
    case ACCOUNTING_SETUP_ACCOUNTS = "accounting:setup_accounts";
    case ACCOUNTING_SETUP_BOOK_OF_ACCOUNTS = "accounting:setup_book of accounts";
    case ACCOUNTING_SETUP_ACCOUNT_GROUPS = "accounting:setup_account groups";
    case ACCOUNTING_SETUP_ACCOUNT_TYPES = "accounting:setup_account types";
    case ACCOUNTING_SETUP_POSTING_PERIODS = "accounting:setup_posting periods";
    case ACCOUNTING_SETUP_CHART_OF_ACCOUNTS = "accounting:setup_chart of accounts";
    case ACCOUNTING_SETUP_STAKEHOLDERS = "accounting:setup_stakeholders";
    case ACCOUNTING_SETUP_SYNCHRONIZATION = "accounting:setup_synchronization";
    case ACCOUNTING_SETUP_PARTICULAR_GROUP = "accounting:setup_particular group";
    case ACCOUNTING_SETUP_WITHHOLDING_TAX  = "accounting:setup_withholding tax";
    case ACCOUNTING_SETUP_TERMS = "accounting:setup_terms";
    //Accounting Request
    case ACCOUNTING_REQUEST_PURCHASE_ORDER = "accounting:request_purchase order";
    case ACCOUNTING_REQUEST_NON_PURCHASE_ORDER = "accounting:request_non purchase order";
    case ACCOUNTING_REQUEST_NON_PURCHASE_ORDER_ALL = "accounting:request_npo_all request";
    case ACCOUNTING_REQUEST_NON_PURCHASE_ORDER_MY_REQUEST = "accounting:request_npo_my request";
    case ACCOUNTING_REQUEST_NON_PURCHASE_ORDER_MY_APPROVAL = "accounting:request_npo_my approval";
    case ACCOUNTING_REQUEST_PRE_PAYROLL_AUDIT = "accounting:request_pre payroll audit";

    //Accounting Voucher
    case ACCOUNTING_VOUCHER_DISBURSEMENT = "accounting:voucher_disbursement";
    case ACCOUNTING_VOUCHER_DISBURSEMENT_ALL = "accounting:voucher_disbursement_all request";
    case ACCOUNTING_VOUCHER_DISBURSEMENT_MY_REQUEST = "accounting:voucher_disbursement_my request";
    case ACCOUNTING_VOUCHER_DISBURSEMENT_MY_APPROVAL = "accounting:voucher_disbursement_my approval";
    case ACCOUNTING_VOUCHER_DISBURSEMENT_FOR_DISBURSEMENT_VOUCHER = "accounting:voucher_disbursement_for disbursement voucher";
    case ACCOUNTING_VOUCHER_CASH = "accounting:voucher_cash";
    case ACCOUNTING_VOUCHER_CASH_ALL = "accounting:voucher_cash_all request";
    case ACCOUNTING_VOUCHER_CASH_MY_REQUEST = "accounting:voucher_cash_my request";
    case ACCOUNTING_VOUCHER_CASH_MY_APPROVAL = "accounting:voucher_cash_my approval";
    case ACCOUNTING_VOUCHER_CASH_FOR_CASH_VOUCHER = "accounting:voucher_cash_for cash voucher";
    case ACCOUNTING_VOUCHER_CASH_CLEARED = "accounting:voucher_cash_cleared list";
    case ACCOUNTING_VOUCHER_CASH_FOR_CLEARING = "accounting:voucher_cash_clearing list";

    //Accounting Journal
    case ACCOUNTING_JOURNAL_ENTRY = "accounting:journal_journal entry";
    case ACCOUNTING_JOURNAL_ENTRY_CASH_ENTRIES = "accounting:journal_list_journal entry cash entries";
    case ACCOUNTING_JOURNAL_ENTRY_DISBURSEMENT_ENTRIES = "accounting:journal_list_journal entry disbursement entries";
    case ACCOUNTING_JOURNAL_ENTRY_FOR_PAYMENT_ENTRIES = "accounting:journal_list_journal entry for payement entries";

    //Accounting Report
    case ACCOUNTING_REPORTS_BALANCE_SHEET = "accounting:reports_balance sheet";
    case ACCOUNTING_REPORTS_BOOK_BALANCE = "accounting:reports_book balance";
    case ACCOUNTING_REPORTS_EXPENSES_FOR_THE_MONTH = "accounting:reports_expenses for the month";
    case ACCOUNTING_REPORTS_INCOME_STATEMENT = "accounting:reports_income statement";
    case ACCOUNTING_REPORTS_MONTHLY_PROJECT_EXPENSES = "accounting:reports_monthly project expenses";
    case ACCOUNTING_REPORTS_MONTHLY_UNLIQUIDATED_CASH_ADVANCES = "accounting:reports_monthly unliquidated cash advances";
    case ACCOUNTING_REPORTS_STATEMENT_OF_CASH_FLOW = "accounting:reports_statement of cash flow";
    case ACCOUNTING_REPORTS_OFFICE_CODE = "accounting:reports_office code";
    case ACCOUNTING_REPORTS_OFFICE_HUMAN_RESOURCE = "accounting:reports_office human resource";
    case ACCOUNTING_REPORTS_LIQUIDATION_FORM = "accounting:reports_liquidation form";
    case ACCOUNTING_REPORTS_REPLENISHMENT_SUMMARY = "accounting:reports_replenishment summary";
    case ACCOUNTING_REPORTS_CASH_ADVANCE_SUMMARY = "accounting:reports_cash advance summary";
    case ACCOUNTING_REPORTS_MEMORANDUM_OF_DEPOSIT = "accounting:reports_memorandum of deposit";
    case ACCOUNTING_REPORTS_PROVISIONAL_RECEIPT = "accounting:reports_provisional receipt";
    case ACCOUNTING_REPORTS_CASH_RETURN_SLIP = "accounting:reports_cash return slip";
    case ACCOUNTING_REPORTS_PAYROLL_LIQUIDATIONS = "accounting:reports_payroll liquidations";
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
