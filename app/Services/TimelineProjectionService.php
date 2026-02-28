<?php

namespace App\Services;

/**
 * FV = PV × (1+r)^n + PMT × [((1+r)^n - 1) / r]. All amounts in cents. bcmath.
 */
class TimelineProjectionService
{
    private const SCALE = 10;

    /**
     * Months until currentSavings + contributions reach targetAmount.
     *
     * @param int $currentSavingsCents
     * @param int $monthlyContributionCents
     * @param string $annualReturnPercent e.g. "7"
     * @param int $targetAmountCents
     * @return int|null
     */
    public function projectMonths(
        int $currentSavingsCents,
        int $monthlyContributionCents,
        string $annualReturnPercent,
        int $targetAmountCents
    ): ?int {
        if ($targetAmountCents <= 0) {
            return null;
        }
        if ($currentSavingsCents >= $targetAmountCents) {
            return 0;
        }

        $monthlyRate = bcdiv($annualReturnPercent, '1200', self::SCALE);
        $pv = (string) $currentSavingsCents;
        $months = 0;
        $max = 1200;

        while (bccomp($pv, (string) $targetAmountCents, 0) < 0 && $months < $max) {
            $pv = bcadd(
                bcmul($pv, bcadd('1', $monthlyRate, self::SCALE), self::SCALE),
                (string) $monthlyContributionCents,
                0
            );
            $months++;
        }

        return $months >= $max ? null : $months;
    }

    public function calculateSavingsRate(int $incomeCents, int $expensesCents, int $debtPaymentsCents = 0): float
    {
        if ($incomeCents <= 0) {
            return 0.0;
        }
        $savings = $incomeCents - $expensesCents - $debtPaymentsCents;

        return round($savings / $incomeCents * 100, 2);
    }
}
