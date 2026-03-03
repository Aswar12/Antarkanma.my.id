#!/usr/bin/env php
<?php

/**
 * Laravel MCP Server
 * 
 * This script provides MCP (Model Context Protocol) interface for Laravel applications.
 * It allows AI assistants to interact with your Laravel application safely.
 * 
 * Usage:
 *   php mcp-server.php
 * 
 * Or add to Claude Desktop configuration:
 *   "command": "php",
 *   "args": ["C:\\laragon\\www\\Antarkanma\\mcp-server.php"]
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

class LaravelMCPServer
{
    private array $tools = [
        'database_query' => [
            'description' => 'Execute read-only database queries',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'SQL SELECT query'],
                ],
                'required' => ['query'],
            ],
        ],
        'artisan_command' => [
            'description' => 'Run Laravel Artisan commands (read-only)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'command' => ['type' => 'string', 'description' => 'Artisan command name'],
                    'parameters' => ['type' => 'array', 'description' => 'Command parameters'],
                ],
                'required' => ['command'],
            ],
        ],
        'read_file' => [
            'description' => 'Read application files',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'path' => ['type' => 'string', 'description' => 'File path relative to project root'],
                ],
                'required' => ['path'],
            ],
        ],
        'list_routes' => [
            'description' => 'List application routes',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'search' => ['type' => 'string', 'description' => 'Search term for route filtering'],
                ],
            ],
        ],
        'check_model' => [
            'description' => 'Get model information',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'model' => ['type' => 'string', 'description' => 'Model class name'],
                ],
                'required' => ['model'],
            ],
        ],
    ];

    public function handle(): void
    {
        $input = file_get_contents('php://stdin');
        $request = json_decode($input, true);

        if (!$request) {
            $this->sendError('Invalid JSON request');
            return;
        }

        $method = $request['method'] ?? '';

        match ($method) {
            'initialize' => $this->handleInitialize($request),
            'tools/list' => $this->handleToolsList($request),
            'tools/call' => $this->handleToolsCall($request),
            'ping' => $this->sendResult(null),
            default => $this->sendError('Unknown method: ' . $method),
        };
    }

    private function handleInitialize(array $request): void
    {
        $this->sendResult([
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => [
                    'listChanged' => true,
                ],
            ],
            'serverInfo' => [
                'name' => 'laravel-mcp-server',
                'version' => '1.0.0',
            ],
        ]);
    }

    private function handleToolsList(array $request): void
    {
        $tools = [];
        foreach ($this->tools as $name => $tool) {
            $tools[] = [
                'name' => $name,
                'description' => $tool['description'],
                'inputSchema' => $tool['inputSchema'],
            ];
        }

        $this->sendResult(['tools' => $tools]);
    }

    private function handleToolsCall(array $request): void
    {
        $name = $request['params']['name'] ?? '';
        $args = $request['params']['arguments'] ?? [];

        $result = match ($name) {
            'database_query' => $this->executeQuery($args),
            'artisan_command' => $this->runArtisan($args),
            'read_file' => $this->readFile($args),
            'list_routes' => $this->listRoutes($args),
            'check_model' => $this->checkModel($args),
            default => ['error' => 'Unknown tool: ' . $name],
        };

        $this->sendResult([
            'content' => [
                [
                    'type' => 'text',
                    'text' => is_array($result) ? json_encode($result, JSON_PRETTY_PRINT) : $result,
                ],
            ],
        ]);
    }

    private function executeQuery(array $args): array
    {
        $query = $args['query'] ?? '';

        // Security: Only allow SELECT queries
        if (!preg_match('/^\s*SELECT/i', $query)) {
            return ['error' => 'Only SELECT queries are allowed'];
        }

        try {
            $results = DB::select($query);
            return ['success' => true, 'data' => $results, 'count' => count($results)];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function runArtisan(array $args): array
    {
        $command = $args['command'] ?? '';
        $parameters = $args['parameters'] ?? [];

        // Security: Block dangerous commands
        $blockedCommands = ['migrate:fresh', 'db:seed', 'db:wipe', 'tinker', 'down'];
        if (in_array(explode(':', $command)[0], $blockedCommands)) {
            return ['error' => 'Command not allowed: ' . $command];
        }

        try {
            Artisan::call($command, $parameters);
            return [
                'success' => true,
                'output' => Artisan::output(),
                'exitCode' => Artisan::exitCode(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function readFile(array $args): array
    {
        $path = $args['path'] ?? '';
        $basePath = realpath(__DIR__);

        // Security: Prevent directory traversal
        $fullPath = realpath($basePath . '/' . $path);
        if (!$fullPath || !str_starts_with($fullPath, $basePath)) {
            return ['error' => 'Invalid path'];
        }

        if (!is_file($fullPath)) {
            return ['error' => 'File not found: ' . $path];
        }

        try {
            return ['success' => true, 'content' => file_get_contents($fullPath)];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function listRoutes(array $args): array
    {
        $search = $args['search'] ?? null;

        try {
            $routes = Route::getRoutes();
            $result = [];

            foreach ($routes as $route) {
                $routeInfo = [
                    'method' => implode('|', $route->methods()),
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                ];

                if (!$search || str_contains($route->uri(), $search) || str_contains($route->getName() ?? '', $search)) {
                    $result[] = $routeInfo;
                }
            }

            return ['success' => true, 'routes' => $result, 'count' => count($result)];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function checkModel(array $args): array
    {
        $model = $args['model'] ?? '';

        if (!$model) {
            return ['error' => 'Model name required'];
        }

        // Try to resolve model class
        $classNames = [
            $model,
            'App\\Models\\' . $model,
            'App\\' . $model,
        ];

        foreach ($classNames as $className) {
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                return [
                    'success' => true,
                    'class' => $className,
                    'file' => $reflection->getFileName(),
                    'traits' => $reflection->getTraitNames(),
                    'methods' => array_map(fn($m) => $m->getName(), $reflection->getMethods()),
                ];
            }
        }

        return ['error' => 'Model not found: ' . $model];
    }

    private function sendResult(mixed $result): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $_SERVER['REQUEST_ID'] ?? null,
            'result' => $result,
        ];

        echo json_encode($response) . PHP_EOL;
    }

    private function sendError(string $message): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $_SERVER['REQUEST_ID'] ?? null,
            'error' => [
                'code' => -32000,
                'message' => $message,
            ],
        ];

        echo json_encode($response) . PHP_EOL;
    }
}

// Run server
$server = new LaravelMCPServer();
$server->handle();
