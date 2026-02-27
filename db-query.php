#!/usr/bin/env php
<?php

/**
 * MySQL Query Helper for AntarkanMa Project
 * 
 * Usage:
 *   php db-query.php "SELECT * FROM users LIMIT 5"
 *   php db-query.php "users" --json
 *   php db-query.php --sql "SELECT COUNT(*) as count FROM transactions"
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$args = $argv;
array_shift($args); // Remove script name

if (empty($args)) {
    echo "Usage:\n";
    echo "  php db-query.php \"SELECT * FROM table LIMIT 5\"\n";
    echo "  php db-query.php \"table_name\" --json\n";
    echo "  php db-query.php --sql \"SELECT * FROM table\"\n";
    echo "  php db-query.php --table \"users\"\n";
    exit(1);
}

$query = '';
$isJson = false;
$isTable = false;

foreach ($args as $arg) {
    if ($arg === '--json') {
        $isJson = true;
    } elseif ($arg === '--sql') {
        $isTable = false;
    } elseif ($arg === '--table') {
        $isTable = true;
    } else {
        $query = $arg;
    }
}

try {
    $db = DB::connection();
    
    if ($isTable || !str_starts_with(strtoupper(trim($query)), 'SELECT')) {
        // Treat as table name
        $table = $query;
        $results = DB::table($table)->limit(50)->get();
    } else {
        // Execute SQL query
        $results = DB::select($query);
    }
    
    if ($isJson) {
        echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo "Results:\n";
        foreach ($results as $row) {
            $rowArray = (array) $row;
            foreach ($rowArray as $key => $value) {
                echo "  $key: $value\n";
            }
            echo str_repeat('-', 50) . "\n";
        }
        echo "Total: " . count($results) . " rows\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
