<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi endpoint OpenAI (atau endpoint kompatibel OpenAI) yang
    | digunakan oleh fitur "AI Generator" untuk merancang draf artikel.
    |
    */

    'endpoint' => rtrim((string) env('OPENAI_ENDPOINT', 'https://api.openai.com/v1'), '/'),

    'key' => (string) env('OPENAI_KEY', ''),

    'model' => (string) env('OPENAI_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('OPENAI_TIMEOUT', 60),

];
