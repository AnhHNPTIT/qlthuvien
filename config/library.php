<?php

return [
    'fine_per_day' => (int) env('LIBRARY_FINE_PER_DAY', 2000),
    'loan_days' => 14,
    'max_active_loans' => 3,
];
