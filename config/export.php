<?php

return [
    // Memory limits by dataset size
    'memory_limits' => [
        'small' => '128M',
        'medium' => '256M',
        'large' => '512M',
        'xlarge' => '1024M',
    ],

    // Time limits by dataset size  
    'time_limits' => [
        'small' => 120,
        'medium' => 300,
        'large' => 600,
        'xlarge' => 900,
    ],

    // Thresholds for format switching
    'csv_threshold' => 25000,
    'chunk_threshold' => 50000,

    // Chunk sizes by dataset size
    'chunk_sizes' => [
        'small' => 2000,
        'medium' => 1000,
        'large' => 500,
        'xlarge' => 250,
    ],

    // Features to disable for large exports
    'disable_features' => [
        'advanced_formatting' => 5000,
        'auto_sizing' => 10000,
        'borders' => 25000,
        'colors' => 50000,
    ],
];
