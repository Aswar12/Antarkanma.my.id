# 🚀 MCP Server Setup Guide - AntarkanMa

## 📋 Apa itu MCP Server?

**MCP (Model Context Protocol)** adalah protokol yang memungkinkan AI assistant (seperti Claude) untuk berinteraksi dengan aplikasi Anda secara aman. Dengan MCP Server, AI dapat:

- ✅ Query database (read-only)
- ✅ Menjalankan Artisan commands
- ✅ Membaca file project
- ✅ List routes
- ✅ Cek model information

---

## 🛠️ Instalasi

### 1. File yang Dibuat

| File | Deskripsi |
|------|-----------|
| `mcp-server.php` | Main MCP Server script |
| `claude-desktop-mcp.json` | Claude Desktop configuration |
| `mcp-config.json` | General MCP configuration reference |

### 2. Setup untuk Claude Desktop

#### Windows

1. **Buka Claude Desktop Configuration:**
   - Tekan `Win + R`
   - Ketik: `%APPDATA%\Claude\claude_desktop_config.json`
   - Jika tidak ada, buat file baru

2. **Copy Configuration:**
   - Buka file `claude-desktop-mcp.json` di project ini
   - Copy seluruh isi ke `claude_desktop_config.json`

3. **Restart Claude Desktop**

#### macOS

1. **Buka Claude Desktop Configuration:**
   ```bash
   ~/Library/Application\ Support/Claude/claude_desktop_config.json
   ```

2. **Copy Configuration** dari `claude-desktop-mcp.json`

3. **Restart Claude Desktop**

---

## 🔧 Tools yang Tersedia

### 1. `database_query`
Execute read-only SELECT queries.

**Example:**
```json
{
  "name": "database_query",
  "arguments": {
    "query": "SELECT * FROM users LIMIT 10"
  }
}
```

### 2. `artisan_command`
Run Artisan commands (read-only, beberapa command diblokir untuk keamanan).

**Example:**
```json
{
  "name": "artisan_command",
  "arguments": {
    "command": "route:list",
    "parameters": []
  }
}
```

**Commands yang diblokir:**
- `migrate:fresh`
- `db:seed`
- `db:wipe`
- `tinker`
- `down`

### 3. `read_file`
Baca file dalam project.

**Example:**
```json
{
  "name": "read_file",
  "arguments": {
    "path": "app/Models/User.php"
  }
}
```

### 4. `list_routes`
List semua routes atau search berdasarkan nama.

**Example:**
```json
{
  "name": "list_routes",
  "arguments": {
    "search": "api"
  }
}
```

### 5. `check_model`
Dapatkan informasi model Laravel.

**Example:**
```json
{
  "name": "check_model",
  "arguments": {
    "model": "User"
  }
}
```

---

## 🧪 Testing MCP Server

### Test Manual via CLI

```bash
# Test initialize
echo {"jsonrpc":"2.0","id":1,"method":"initialize","params":{}} | php mcp-server.php

# Test tools list
echo {"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}} | php mcp-server.php

# Test database query
echo {"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"database_query","arguments":{"query":"SELECT * FROM users LIMIT 5"}}} | php mcp-server.php

# Test artisan command
echo {"jsonrpc":"2.0","id":4,"method":"tools/call","params":{"name":"artisan_command","arguments":{"command":"route:list"}}} | php mcp-server.php

# Test read file
echo {"jsonrpc":"2.0","id":5,"method":"tools/call","params":{"name":"read_file","arguments":{"path":".env.example"}}} | php mcp-server.php
```

---

## 🔒 Keamanan

MCP Server ini memiliki beberapa layer keamanan:

1. **Database Queries:** Hanya SELECT queries yang diizinkan
2. **Artisan Commands:** Commands berbahaya diblokir
3. **File Access:** Hanya file dalam project directory yang bisa diakses
4. **No Directory Traversal:** Path validation untuk mencegah akses ke luar project

---

## 📝 Troubleshooting

### Error: "PHP Fatal Error: vendor/autoload.php not found"

```bash
# Pastikan berada di directory project
cd C:\laragon\www\Antarkanma

# Install dependencies jika belum
composer install
```

### Error: "Database connection failed"

```bash
# Cek .env file
type .env

# Pastikan database configuration benar
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=antarkanma
DB_USERNAME=root
DB_PASSWORD=
```

### Claude Desktop tidak mendeteksi MCP Server

1. **Restart Claude Desktop**
2. **Cek configuration file location:**
   - Windows: `%APPDATA%\Claude\claude_desktop_config.json`
   - macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`
3. **Validasi JSON:** Pastikan tidak ada syntax error

---

## 🎯 Use Cases

### 1. Debugging Database
```
"Tolong cek berapa banyak user yang terdaftar di database"
```

### 2. Cek Routes
```
"Tampilkan semua routes yang mengandung kata 'admin'"
```

### 3. Baca File Config
```
"Buka file config/database.php dan jelaskan konfigurasinya"
```

### 4. Jalankan Artisan
```
"Jalankan php artisan cache:clear"
```

### 5. Cek Model
```
"Apa saja methods yang ada di model Order?"
```

---

## 📚 Resources

- [MCP Specification](https://modelcontextprotocol.io/)
- [Claude Desktop MCP Setup](https://claude.ai/mcp)
- [MCP Server Examples](https://github.com/modelcontextprotocol/servers)

---

## ✨ Fitur yang Bisa Ditambahkan

Jika Anda ingin menambahkan tools lain, edit `mcp-server.php` dan tambahkan ke array `$tools`:

```php
private array $tools = [
    // ... existing tools
    'new_tool' => [
        'description' => 'Description of new tool',
        'inputSchema' => [
            'type' => 'object',
            'properties' => [
                'param1' => ['type' => 'string'],
            ],
        ],
    ],
];
```

Kemudian implementasikan handler-nya di method `handleToolsCall()`.

---

**Happy Coding! 🎉**
