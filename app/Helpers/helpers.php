<?php

if (!function_exists('money')) {
    /**
     * Format a monetary amount in FCFA.
     * e.g. money(1234567) → "1 234 567 FCFA"
     */
    function money(float|int|null $amount, bool $showSymbol = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0, ',', ' ');
        return $showSymbol ? $formatted . ' FCFA' : $formatted;
    }
}
