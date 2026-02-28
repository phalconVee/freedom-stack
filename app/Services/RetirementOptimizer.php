<?php

namespace App\Services;

/**
 * Employer match gap, contribution order. All amounts in cents.
 */
class RetirementOptimizer
{
    /**
     * Money left on table if not contributing enough to get full match.
     *
     * @param int $currentContributionCents Monthly
     * @param string $matchPercent e.g. "50" for 50%
     * @param int|null $matchLimitCents Annual cap in cents
     * @param int $salaryCents Annual salary in cents
     */
    public function calculateEmployerMatchGap(
        int $currentContributionCents,
        string $matchPercent,
        ?int $matchLimitCents,
        int $salaryCents
    ): int {
        $annualContribution = $currentContributionCents * 12;
        $matchPct = (float) $matchPercent / 100;
        $maxMatch = $matchLimitCents ?? (int) round($salaryCents * $matchPct * 2, 0); // assume match up to X% of salary
        $currentMatch = (int) min($annualContribution * $matchPct, $maxMatch);
        $possibleMatch = $maxMatch;
        $gap = max(0, $possibleMatch - $currentMatch);

        return $gap;
    }

    /**
     * Standard order: 401k to match → HSA → Roth IRA → 401k max → taxable.
     *
     * @return list<string>
     */
    public function contributionOrder(): array
    {
        return ['401k_match', 'hsa', 'roth_ira', '401k_max', 'taxable'];
    }
}
