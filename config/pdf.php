<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default PDF Options
    |--------------------------------------------------------------------------
    |
    | Default options for PDF generation
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Page Size Configuration
    |--------------------------------------------------------------------------
    |
    | Default page size and orientation. Available sizes:
    | 'letter', 'legal', 'tabloid', 'ledger', 'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6'
    |
    */
    'page' => [
        'size' => 'a4',
        'orientation' => 'portrait', // portrait or landscape
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser and Layout Configuration
    |--------------------------------------------------------------------------
    */
    'windowSize' => [1024, 768],
    
    'margins' => [
        'top' => 10,
        'right' => 10,
        'bottom' => 10,
        'left' => 10
    ],
    
    'scale' => 1.0,
    'printBackground' => true,
    'preferCSSPageSize' => true,
    
    
    /*
    |--------------------------------------------------------------------------
    | Chrome Binary Path
    |--------------------------------------------------------------------------
    |
    | Optional path to Chrome binary. Leave null to auto-detect.
    |
    */
    'chromePath' => null,
];
