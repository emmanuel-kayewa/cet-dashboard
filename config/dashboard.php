<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Data Source
    |--------------------------------------------------------------------------
    |
    | Controls where dashboard data comes from.
    | Options: 'simulation', 'manual', 'oracle'
    |
    */
    'data_source' => env('DASHBOARD_DATA_SOURCE', 'simulation'),

    /*
    |--------------------------------------------------------------------------
    | Simulation Configuration
    |--------------------------------------------------------------------------
    */
    'simulation' => [
        'enabled' => env('DASHBOARD_SIMULATION_ENABLED', true),
        'interval_seconds' => env('DASHBOARD_SIMULATION_INTERVAL', 30),
        'realistic_trends' => true,
        'seed_value' => 42,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Email Domain
    |--------------------------------------------------------------------------
    */
    'allowed_email_domain' => env('DASHBOARD_ALLOWED_EMAIL_DOMAIN', 'zesco.co.zm'),

    /*
    |--------------------------------------------------------------------------
    | Session Timeout (minutes)
    |--------------------------------------------------------------------------
    */
    'session_timeout' => env('SESSION_LIFETIME', 15),

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => true,
        'kpi_drop_threshold' => 10, // percentage drop to trigger alert
        'risk_threshold' => 7, // risk score (1-10) to trigger alert
        'email_notifications' => true,
        'whatsapp_notifications' => env('DASHBOARD_WHATSAPP_NOTIFICATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */
    'export' => [
        'pdf_orientation' => 'landscape',
        'company_name' => 'ZESCO Limited',
        'company_logo' => 'images/zesco-logo.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Oracle Database Configuration (Future)
    |--------------------------------------------------------------------------
    */
    'oracle' => [
        'connection' => 'oracle',
        'schema' => env('DB_ORACLE_SCHEMA', 'ZESCO'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI / LLM Configuration
    |--------------------------------------------------------------------------
    |
    | Provider-agnostic AI layer. Switch between Ollama (local) and
    | OpenAI (cloud) by changing the AI_PROVIDER env variable.
    |
    */
    'ai' => [
        'enabled' => env('AI_ENABLED', true),
        'provider' => env('AI_PROVIDER', 'ollama'),  // 'ollama' | 'openai'

        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'qwen2.5:72b'),
            'fast_model' => env('OLLAMA_FAST_MODEL', 'qwen2.5:14b'), // lighter model for quick tasks
            'timeout' => (int) env('OLLAMA_TIMEOUT', 600), // seconds — large models need time
        ],

        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 60),
        ],

        // Cache durations for AI responses (minutes)
        'cache' => [
            'executive_insights' => 1440,   // 24 hours
            'anomaly_explanation' => 720,    // 12 hours
            'recommendations' => 720,        // 12 hours
            'kpi_categorization' => 10080,   // 7 days
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KPI Import Settings
    |--------------------------------------------------------------------------
    */
    'import' => [
        'max_file_size' => 10240, // KB
        'allowed_extensions' => ['xlsx', 'csv', 'xls'],
        'ai_categorization' => true, // Use AI to auto-categorize imported KPIs
    ],

    /*
    |--------------------------------------------------------------------------
    | KPI Deadline / Alert Settings
    |--------------------------------------------------------------------------
    */
    'deadlines' => [
        'warning_days' => [14, 7, 3, 1], // days before deadline to send warnings
        'stale_data_days' => 15,           // alert if no data submitted in N days
    ],

    /*
    |--------------------------------------------------------------------------
    | KPI Categories
    |--------------------------------------------------------------------------
    */
    'kpi_categories' => [
        'financial' => 'Financial',
        'operational' => 'Operational',
        'strategic' => 'Strategic',
        'risk' => 'Risk & Compliance',
        'hr' => 'Human Resources',
        'customer' => 'Customer',
        'project' => 'Projects',
        'technical' => 'Technical',
    ],

];
