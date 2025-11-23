<?php
// app/config/config.php
return [
  'db' => [
    // MySQL on XAMPP (macOS) using port 3307
    // Create DB first in MySQL Workbench: hospital_scms
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=hospital_scms;charset=utf8mb4',
    'user' => 'root',     // default XAMPP user
    'pass' => '',         // default XAMPP password is empty unless you set one
    'options' => [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  ],
  'app' => [
    'base_url' => '/hospital_ISCM/public/',
    'session_name' => 'hscms_sess',
  ]
  ,
  // Billing integration settings - prefer environment variables for secrets
  'billing' => [
    // Example: https://abcd-1234.ngrok.io/api or https://billing.example.local/api
    'api_url' => getenv('BILLING_API_URL') ?: '',
    // Keep API keys out of source control. Set BILLING_API_KEY in the environment.
    'api_key' => getenv('BILLING_API_KEY') ?: '',
  ]
];
