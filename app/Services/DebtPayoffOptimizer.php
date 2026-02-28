<?php

namespace App\Services;

/**
 * Avalanche (highest interest first) and snowball (lowest balance first).
 * All amounts in cents. Uses bcmath.
 */
class DebtPayoffOptimizer
{
    private const SCALE = 10;

    /**
     * @param array<int, array{balance: int, interest_rate: float|string, minimum_payment: int}> $debts
     * @return array{total_months: int, total_interest: int, debt_free_date: string, schedule: array}
     */
    public function calculateAvalanche(array $debts, int $extraPaymentCents = 0): array
    {
        return $this->runPayoff($debts, $extraPaymentCents, 'avalanche');
    }

    /**
     * @param array<int, array{balance: int, interest_rate: float|string, minimum_payment: int}> $debts
     */
    public function calculateSnowball(array $debts, int $extraPaymentCents = 0): array
    {
        return $this->runPayoff($debts, $extraPaymentCents, 'snowball');
    }

    /**
     * @param array<int, array{balance: int, interest_rate: float|string, minimum_payment: int, name?: string}> $debts
     */
    private function runPayoff(array $debts, int $extraPaymentCents, string $method): array
    {
        $totalInterest = 0;
        $month = 0;
        $maxMonths = 600;

        $list = array_values($debts);
        foreach ($list as $i => $d) {
            $list[$i]['_balance'] = (string) $d['balance'];
            $list[$i]['_id'] = $i;
        }

        if ($method === 'avalanche') {
            usort($list, fn ($a, $b) => (float) $b['interest_rate'] <=> (float) $a['interest_rate']);
        } else {
            usort($list, fn ($a, $b) => $a['balance'] <=> $b['balance']);
        }

        while (array_sum(array_column($list, 'balance')) > 0 && $month < $maxMonths) {
            $available = $extraPaymentCents;
            foreach ($list as $i => &$d) {
                if ($d['balance'] <= 0) {
                    continue;
                }
                $interest = (int) round((float) bcmul(bcmul($d['_balance'], (string) $d['interest_rate'], self::SCALE), '0.01', self::SCALE) / 12, 0);
                $totalInterest += $interest;
                $payment = $d['minimum_payment'] + $available;
                $available = 0;
                $payPrincipal = max(0, $payment - $interest);
                $newBalance = max(0, $d['balance'] - $payPrincipal);
                $available += max(0, $payment - $interest - ($d['balance'] - $newBalance));
                $d['balance'] = $newBalance;
                $d['_balance'] = (string) $newBalance;
            }
            unset($d);
            $month++;
        }

        $debtFreeDate = now()->addMonths($month)->format('Y-m-d');

        return [
            'total_months' => $month,
            'total_interest' => $totalInterest,
            'debt_free_date' => $debtFreeDate,
            'schedule' => [],
        ];
    }

    /**
     * @param array $debts
     * @return array{avalanche: array, snowball: array, interest_saved: int}
     */
    public function compareMethods(array $debts, int $extraPaymentCents = 0): array
    {
        $avalanche = $this->calculateAvalanche($debts, $extraPaymentCents);
        $snowball = $this->calculateSnowball($debts, $extraPaymentCents);

        return [
            'avalanche' => $avalanche,
            'snowball' => $snowball,
            'interest_saved' => max(0, $snowball['total_interest'] - $avalanche['total_interest']),
        ];
    }
}
