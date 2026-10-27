<?php

return [
    // Attempt anonim (belum diklaim) dihapus setelah sekian hari oleh `model:prune` terjadwal harian.
    'anon_retention_days' => (int) env('ASSESSMENT_ANON_RETENTION_DAYS', 30),

    // POST /api/assessment/score per IP per menit.
    'score_rate_limit' => (int) env('ASSESSMENT_SCORE_RATE_LIMIT', 30),
];
