<?php

namespace App\Data;

use App\Models\FinancialProfile;
use App\Models\User;

/**
 * Strip all PII before sending to OpenRouter. Return only amounts, rates, categories.
 */
class FinancialDataSanitizer
{
    public function sanitizeForAI(User $user): array
    {
        $profile = $user->financialProfile;
        $debts = $user->debts;
        $accounts = $user->investmentAccounts;
        $expenses = $user->expenses;

        $out = [
            'monthly_net_income' => $profile?->monthly_net_income ?? 0,
            'monthly_expenses_total' => $profile?->monthly_expenses_total ?? 0,
            'filing_status' => $profile?->filing_status ?? 'single',
            'risk_tolerance' => $profile?->risk_tolerance ?? 'moderate',
            'debts' => [],
            'accounts' => [],
            'expense_breakdown' => [],
        ];

        foreach ($debts as $i => $d) {
            $out['debts'][] = [
                'label' => 'Debt ' . ($i + 1),
                'balance' => $d->balance,
                'interest_rate' => $d->interest_rate,
                'minimum_payment' => $d->minimum_payment,
                'type' => $d->type,
            ];
        }

        foreach ($accounts as $i => $a) {
            $out['accounts'][] = [
                'label' => 'Account ' . ($i + 1),
                'type' => $a->type,
                'balance' => $a->balance,
                'monthly_contribution' => $a->monthly_contribution,
                'employer_match_pct' => $a->employer_match_pct,
                'employer_match_limit' => $a->employer_match_limit,
            ];
        }

        $byCategory = [];
        foreach ($expenses as $e) {
            $byCategory[$e->category] = ($byCategory[$e->category] ?? 0) + $e->monthly_amount;
        }
        $out['expense_breakdown'] = $byCategory;

        return $out;
    }
}
