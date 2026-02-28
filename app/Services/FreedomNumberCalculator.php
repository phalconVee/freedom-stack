<?php

namespace App\Services;

/**
 * All financial math using bcmath. Monetary values in cents.
 * Freedom Number = Annual Expenses / Withdrawal Rate
 */
class FreedomNumberCalculator
{
    private const SCALE = 10;

    /**
     * @param int $monthlyExpensesCents Monthly expenses in cents
     * @param string $withdrawalRatePercent e.g. "4" for 4%
     * @return int Freedom number in cents
     */
    public function calculate(int $monthlyExpensesCents, string $withdrawalRatePercent): int
    {
        $annualExpensesCents = bcmul((string) $monthlyExpensesCents, '12', self::SCALE);
        $rateDecimal = bcdiv($withdrawalRatePercent, '100', self::SCALE);
        $freedomNumber = bcdiv($annualExpensesCents, $rateDecimal, self::SCALE);

        return (int) round((float) $freedomNumber, 0);
    }

    /**
     * Returns freedom numbers at 3%, 4%, 5%, 6% withdrawal rates.
     *
     * @return array<string, int> [ '3' => cents, '4' => cents, ... ]
     */
    public function calculateRange(int $monthlyExpensesCents): array
    {
        $rates = ['3', '4', '5', '6'];
        $result = [];
        foreach ($rates as $rate) {
            $result[$rate] = $this->calculate($monthlyExpensesCents, $rate);
        }

        return $result;
    }

    /**
     * Years to reach freedom at given monthly savings and expected return.
     * FV = PV * (1+r)^n + PMT * (((1+r)^n - 1) / r). Solve for n.
     * Simplified: iterate months until future value >= target.
     *
     * @param int $currentSavingsCents
     * @param int $monthlySavingsCents
     * @param string $annualReturnPercent e.g. "7"
     * @param int $targetFreedomNumberCents
     * @return float|null Years to freedom, or null if unreachable/negative savings
     */
    public function yearsToFreedom(
        int $currentSavingsCents,
        int $monthlySavingsCents,
        string $annualReturnPercent,
        int $targetFreedomNumberCents
    ): ?float {
        if ($targetFreedomNumberCents <= 0) {
            return null;
        }
        if ($currentSavingsCents >= $targetFreedomNumberCents) {
            return 0.0;
        }
        $monthlyRate = bcdiv($annualReturnPercent, '1200', self::SCALE); // annual% -> monthly decimal
        $months = 0;
        $pv = (string) $currentSavingsCents;
        $maxMonths = 1200; // 100 years
        while ($months < $maxMonths && bccomp($pv, (string) $targetFreedomNumberCents, 0) < 0) {
            $pv = bcadd(bcmul($pv, bcadd('1', $monthlyRate, self::SCALE), self::SCALE), (string) $monthlySavingsCents, 0);
            $months++;
        }
        if ($months >= $maxMonths) {
            return null;
        }

        return round($months / 12, 2);
    }

    /**
     * Adjust future expenses for inflation (for display/planning).
     *
     * @param int $currentExpensesCents Monthly, in cents
     * @param string $inflationRatePercent e.g. "3"
     * @param int $years
     * @return int Future monthly expenses in cents
     */
    public function adjustForInflation(int $currentExpensesCents, string $inflationRatePercent, int $years): int
    {
        $factor = bcpow(bcadd('1', bcdiv($inflationRatePercent, '100', self::SCALE), self::SCALE), (string) $years, self::SCALE);

        return (int) round((float) bcmul((string) $currentExpensesCents, $factor, self::SCALE), 0);
    }
}
