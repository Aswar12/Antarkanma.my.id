# 🪑 Table Management & Self Checkout — AntarkanMa

> **Created:** 11 Maret 2026  
> **Status:** 🔄 In Development  
> **Priority:** 🔴 High  
> **Est. Completion:** 1 week (~40 jam)

---

## 📋 Executive Summary

**Hybrid Table Management System** untuk handle 2 tipe merchant dengan konsep berbeda:

| Tipe Merchant | Payment Flow | Table Release | Use Case |
|---------------|--------------|---------------|----------|
| **PAY_FIRST** | Bayar di awal | Auto-release (timer) | Fast Food, Cafe, Food Court |
| **PAY_LAST** | Bayar di akhir | Manual release | Restaurant, Fine Dining, Cafe |

**Self Checkout:** Customer scan QR code → bayar sendiri tanpa kasir.

---

## 🎯 Problem Statement

### **Problem 1: One-Size-Fits-All Tidak Work**

```
❌ Scenario:
- Merchant 1 (Fast Food): Butuh auto-release (customer bayar → makan → pergi)
- Merchant 2 (Restaurant): Butuh manual-release (customer order → makan → nongkrong → bayar → pergi)

Sistem sekarang: Semua merchant pakai flow yang sama → tidak fleksibel!
```

### **Problem 2: Timing Konflik**

```
❌ Tanpa Duration:
- Release terlalu cepat → 2 customer di 1 meja (konflik!)
- Release terlalu lama → Meja kosong tidak bisa dipakai (loss revenue!)

✅ Dengan Duration:
- Buffer time yang reasonable
- Auto-release safety net
- Manual override jika perlu
```

---

## 💡 Solution Overview

### **1. Merchant Configuration**

Setiap merchant bisa configure:
- **Payment Flow:** PAY_FIRST atau PAY_LAST
- **Auto-Release:** Enable/Disable
- **Duration:** 30-120 menit (default 60)
- **Extend Options:** +15, +30 menit

### **2. Hybrid Table Release**

```
┌─────────────────────────────────────────────────────┐
│  PAY_FIRST (Fast Food, Cafe)                        │
├─────────────────────────────────────────────────────┤
│  1. Customer order & bayar → Meja → OCCUPIED        │
│  2. Makanan COMPLETED → Timer mulai                 │
│  3. Customer makan & nongkrong                      │
│  4. Timer expire (60 min) → Meja → AVAILABLE ✅     │
│     (Auto-release, no manual action needed)         │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  PAY_LAST (Restaurant, Fine Dining)                 │
├─────────────────────────────────────────────────────┤
│  1. Customer order → Meja → OCCUPIED                │
│  2. Makanan COMPLETED → Timer mulai (optional)      │
│  3. Customer makan & nongkrong                      │
│  4. Customer minta bill & bayar                     │
│  5. Staff tekan "Release Table" → Meja → AVAILABLE ✅
│     (Manual release, staff confirm customer pergi)  │
│                                                     │
│  Optional: Timer notification                       │
│  "Meja #5 sudah 60 menit. Release sekarang?"        │
└─────────────────────────────────────────────────────┘
```

### **3. Self Checkout (QR Code)**

```
┌─────────────────────────────────────────────────────┐
│  Customer Self-Service Flow                         │
├─────────────────────────────────────────────────────┤
│  1. Customer duduk → Scan QR code di meja           │
│  2. Browse menu & order via HP sendiri              │
│  3. Bayar via QRIS/E-Wallet                         │
│  4. Dapur terima order → Masak                      │
│  5. Waiter antar makanan                            │
│  6. Customer selesai → Pergi                        │
│  7. Staff release table                             │
└─────────────────────────────────────────────────────┘
```

---

## 🗄️ Database Schema

### **Migration 1: Add Merchant Config**

```php
// database/migrations/2026_03_11_add_merchant_table_config.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            // Payment flow configuration
            $table->enum('payment_flow', ['PAY_FIRST', 'PAY_LAST'])
                  ->default('PAY_FIRST')
                  ->after('name')
                  ->comment('PAY_FIRST=Bayar di awal, PAY_LAST=Bayar di akhir');
            
            // Auto-release configuration
            $table->boolean('auto_release_table')
                  ->default(true)
                  ->after('payment_flow')
                  ->comment('Enable auto-release for PAY_FIRST');
            
            // Default dine duration (minutes)
            $table->integer('default_dine_duration')
                  ->default(60)
                  ->after('auto_release_table')
                  ->comment('Buffer time in minutes (30-120)');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'payment_flow',
                'auto_release_table',
                'default_dine_duration',
            ]);
        });
    }
};
```

### **Migration 2: Add Table Tracking**

```php
// database/migrations/2026_03_11_add_table_tracking_to_pos_transactions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            // Track when food is completed/served
            $table->timestamp('food_completed_at')
                  ->nullable()
                  ->after('status')
                  ->comment('When food is served to customer');
            
            // Track table release time
            $table->timestamp('table_released_at')
                  ->nullable()
                  ->after('food_completed_at')
                  ->comment('When table is released back to AVAILABLE');
            
            // Track auto-release timer
            $table->timestamp('auto_release_at')
                  ->nullable()
                  ->after('table_released_at')
                  ->comment('Scheduled auto-release time');
            
            // Manual release by user
            $table->foreignId('released_by')
                  ->nullable()
                  ->constrained('users')
                  ->after('auto_release_at')
                  ->comment('Staff who released the table');
        });
        
        // Index for performance
        $table->index(['status', 'food_completed_at']);
        $table->index(['status', 'table_released_at']);
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropIndex(['status', 'food_completed_at']);
            $table->dropIndex(['status', 'table_released_at']);
            
            $table->dropColumn([
                'food_completed_at',
                'table_released_at',
                'auto_release_at',
                'released_by',
            ]);
        });
    }
};
```

---

## 🔧 Backend Implementation

### **1. Model Updates**

#### **Merchant.php**

```php
// app/Models/Merchant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    const PAYMENT_FLOW_PAY_FIRST = 'PAY_FIRST';
    const PAYMENT_FLOW_PAY_LAST = 'PAY_LAST';

    protected $fillable = [
        'name',
        'payment_flow',
        'auto_release_table',
        'default_dine_duration',
        // ... other fields
    ];

    protected $casts = [
        'auto_release_table' => 'boolean',
        'default_dine_duration' => 'integer',
    ];

    /**
     * Check if merchant uses auto-release
     */
    public function shouldAutoReleaseTable(): bool
    {
        return $this->payment_flow === self::PAYMENT_FLOW_PAY_FIRST 
            && $this->auto_release_table;
    }

    /**
     * Get dine duration in minutes
     */
    public function getDineDurationAttribute(): int
    {
        return $this->default_dine_duration ?? 60;
    }

    /**
     * Get payment flow label
     */
    public function getPaymentFlowLabelAttribute(): string
    {
        return match($this->payment_flow) {
            self::PAYMENT_FLOW_PAY_FIRST => 'Bayar di Awal',
            self::PAYMENT_FLOW_PAY_LAST => 'Bayar di Akhir',
            default => 'Unknown',
        };
    }

    /**
     * Related tables
     */
    public function tables()
    {
        return $this->hasMany(MerchantTable::class);
    }
}
```

#### **PosTransaction.php**

```php
// app/Models/PosTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'PENDING';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_VOIDED = 'VOIDED';

    protected $fillable = [
        'merchant_id',
        'table_id',
        'order_type',
        'status',
        'payment_status',
        'food_completed_at',
        'table_released_at',
        'auto_release_at',
        'released_by',
        // ... other fields
    ];

    protected $casts = [
        'food_completed_at' => 'datetime',
        'table_released_at' => 'datetime',
        'auto_release_at' => 'datetime',
    ];

    /**
     * Get related merchant
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get related table
     */
    public function table()
    {
        return $this->belongsTo(MerchantTable::class, 'table_id');
    }

    /**
     * Get staff who released table
     */
    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Check if transaction is ready for table release
     */
    public function isReadyForTableRelease(): bool
    {
        $merchant = $this->merchant;
        
        if ($merchant->payment_flow === Merchant::PAYMENT_FLOW_PAY_FIRST) {
            // Auto-release after timer
            if (!$this->food_completed_at) {
                return false;
            }
            
            $elapsedMinutes = now()->diffInMinutes($this->food_completed_at);
            return $elapsedMinutes >= $merchant->dine_duration;
        } else {
            // PAY_LAST: Only release when paid + manual release
            return $this->payment_status === 'PAID' 
                && $this->table_released_at !== null;
        }
    }

    /**
     * Schedule auto-release
     */
    public function scheduleAutoRelease(): void
    {
        $merchant = $this->merchant;
        
        if ($merchant->shouldAutoReleaseTable() && $this->food_completed_at) {
            $this->auto_release_at = $this->food_completed_at->addMinutes($merchant->dine_duration);
            $this->save();
        }
    }

    /**
     * Release table manually
     */
    public function releaseTable(?User $releasedBy = null): void
    {
        $this->table_released_at = now();
        $this->released_by = $releasedBy?->id;
        $this->save();

        // Update table status
        if ($this->table) {
            $this->table->status = 'AVAILABLE';
            $this->table->save();
        }

        // Log activity
        ActivityLog::create([
            'merchant_id' => $this->merchant_id,
            'user_id' => $releasedBy?->id,
            'action' => 'table_manually_released',
            'description' => "Table #{$this->table->table_number} manually released by {$releasedBy?->name ?? 'System'}",
            'metadata' => [
                'transaction_id' => $this->id,
                'table_id' => $this->table_id,
            ],
        ]);
    }
}
```

---

### **2. TableManagementService**

```php
// app/Services/TableManagementService.php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\MerchantTable;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class TableManagementService
{
    /**
     * Release table (auto or manual)
     */
    public function releaseTable(PosTransaction $transaction, bool $manual = false, ?User $releasedBy = null): bool
    {
        $merchant = $transaction->merchant;
        $table = $transaction->table;

        if (!$table || $table->status === 'AVAILABLE') {
            return false;
        }

        if ($merchant->shouldAutoReleaseTable() && !$manual) {
            // Auto-release logic for PAY_FIRST
            if ($transaction->isReadyForTableRelease()) {
                $this->performRelease($transaction, $table, 'auto');
                
                Log::info("Table #{$table->table_number} auto-released", [
                    'merchant_id' => $merchant->id,
                    'transaction_id' => $transaction->id,
                ]);

                return true;
            }
        } elseif ($manual) {
            // Manual release for PAY_LAST or override
            $this->performRelease($transaction, $table, 'manual', $releasedBy);
            
            Log::info("Table #{$table->table_number} manually released", [
                'merchant_id' => $merchant->id,
                'transaction_id' => $transaction->id,
                'released_by' => $releasedBy?->id,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Perform table release
     */
    private function performRelease(PosTransaction $transaction, MerchantTable $table, string $type, ?User $releasedBy = null): void
    {
        // Update table status
        $table->status = 'AVAILABLE';
        $table->save();

        // Update transaction
        $transaction->table_released_at = now();
        
        if ($type === 'auto') {
            $transaction->auto_release_at = now();
        } else {
            $transaction->released_by = $releasedBy?->id;
        }
        
        $transaction->save();

        // Log activity
        ActivityLog::create([
            'merchant_id' => $transaction->merchant_id,
            'user_id' => $releasedBy?->id,
            'action' => "table_{$type}_released",
            'description' => "Table #{$table->table_number} {$type}-released by {$releasedBy?->name ?? 'System'}",
            'metadata' => [
                'transaction_id' => $transaction->id,
                'table_id' => $table->id,
                'release_type' => $type,
            ],
        ]);
    }

    /**
     * Check and auto-release expired tables (scheduler)
     */
    public function checkAutoRelease(): int
    {
        $released = 0;

        $transactions = PosTransaction::where('status', 'COMPLETED')
            ->whereNotNull('food_completed_at')
            ->whereNull('table_released_at')
            ->whereHas('merchant', function ($query) {
                $query->where('payment_flow', 'PAY_FIRST')
                      ->where('auto_release_table', true);
            })
            ->get();

        foreach ($transactions as $transaction) {
            if ($this->releaseTable($transaction, manual: false)) {
                $released++;
            }
        }

        Log::info("Auto-release check completed", [
            'checked' => $transactions->count(),
            'released' => $released,
        ]);

        return $released;
    }

    /**
     * Extend table duration
     */
    public function extendDuration(PosTransaction $transaction, int $minutes): void
    {
        $merchant = $transaction->merchant;
        
        if ($merchant->shouldAutoReleaseTable() && $transaction->auto_release_at) {
            $transaction->auto_release_at = $transaction->auto_release_at->addMinutes($minutes);
            $transaction->save();

            ActivityLog::create([
                'merchant_id' => $merchant->id,
                'action' => 'table_duration_extended',
                'description' => "Table #{$transaction->table->table_number} duration extended by {$minutes} minutes",
                'metadata' => [
                    'transaction_id' => $transaction->id,
                    'extended_minutes' => $minutes,
                    'new_release_time' => $transaction->auto_release_at->toDateTimeString(),
                ],
            ]);
        }
    }

    /**
     * Get table occupancy duration
     */
    public function getOccupancyDuration(PosTransaction $transaction): int
    {
        if (!$transaction->food_completed_at) {
            return 0;
        }

        return now()->diffInMinutes($transaction->food_completed_at);
    }

    /**
     * Get notification for tables ready to release
     */
    public function getTablesReadyToRelease(int $merchantId): array
    {
        $transactions = PosTransaction::where('merchant_id', $merchantId)
            ->where('status', 'COMPLETED')
            ->whereNotNull('food_completed_at')
            ->whereNull('table_released_at')
            ->whereHas('merchant', function ($query) {
                $query->where('payment_flow', 'PAY_LAST');
            })
            ->get()
            ->filter(function ($transaction) {
                return $this->getOccupancyDuration($transaction) >= 60; // 60 minutes threshold
            });

        return $transactions->map(function ($transaction) {
            return [
                'transaction_id' => $transaction->id,
                'table_number' => $transaction->table->table_number,
                'duration_minutes' => $this->getOccupancyDuration($transaction),
                'order_type' => $transaction->order_type,
                'total_amount' => $transaction->total_amount,
            ];
        })->toArray();
    }
}
```

---

### **3. Controller Updates**

#### **PosTransactionController.php**

```php
// app/Http/Controllers/API/PosTransactionController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\MerchantTable;
use App\Services\TableManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PosTransactionController extends Controller
{
    protected TableManagementService $tableService;

    public function __construct(TableManagementService $tableService)
    {
        $this->tableService = $tableService;
    }

    /**
     * Create DINE_IN transaction
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'table_id' => 'required|exists:merchant_tables,id',
            'order_type' => 'required|in:DINE_IN,TAKEAWAY,DELIVERY',
            'items' => 'required|array|min:1',
            'payment_method' => 'required|in:CASH,QRIS,TRANSFER',
        ]);

        // Check table availability for DINE_IN
        if ($validated['order_type'] === 'DINE_IN') {
            $table = MerchantTable::find($validated['table_id']);
            
            if ($table->status !== 'AVAILABLE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Table is not available',
                    'data' => [
                        'table_status' => $table->status,
                    ],
                ], 422);
            }
        }

        // Create transaction
        $transaction = PosTransaction::create([
            'merchant_id' => $validated['merchant_id'],
            'table_id' => $validated['order_type'] === 'DINE_IN' ? $validated['table_id'] : null,
            'order_type' => $validated['order_type'],
            'status' => 'PENDING',
            'payment_status' => 'PENDING',
            // ... other fields
        ]);

        // Update table status for DINE_IN
        if ($validated['order_type'] === 'DINE_IN') {
            $table->status = 'OCCUPIED';
            $table->save();
        }

        // Mark food as completed (for auto-release timer)
        // This should be called when food is served
        $this->markFoodCompleted($transaction);

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaction created successfully',
        ]);
    }

    /**
     * Mark food as completed (served to customer)
     */
    public function markFoodCompleted(PosTransaction $transaction): JsonResponse
    {
        $transaction->food_completed_at = now();
        $transaction->save();

        // Schedule auto-release for PAY_FIRST merchants
        $transaction->scheduleAutoRelease();

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Food marked as completed',
        ]);
    }

    /**
     * Release table manually (for PAY_LAST)
     */
    public function releaseTable(Request $request, PosTransaction $transaction): JsonResponse
    {
        $releasedBy = $request->user();

        $success = $this->tableService->releaseTable($transaction, manual: true, releasedBy: $releasedBy);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Table released successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to release table',
        ], 422);
    }

    /**
     * Extend table duration
     */
    public function extendDuration(Request $request, PosTransaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|in:15,30,45,60',
        ]);

        $this->tableService->extendDuration($transaction, $validated['minutes']);

        return response()->json([
            'success' => true,
            'data' => [
                'new_release_time' => $transaction->auto_release_at->toDateTimeString(),
            ],
            'message' => 'Duration extended successfully',
        ]);
    }

    /**
     * Get tables ready to release (notification for PAY_LAST)
     */
    public function getTablesReadyToRelease(Request $request): JsonResponse
    {
        $merchantId = $request->user()->merchant_id;

        $tables = $this->tableService->getTablesReadyToRelease($merchantId);

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }
}
```

---

### **4. Laravel Scheduler**

```php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\TableManagementService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check auto-release every 5 minutes
        $schedule->call(function () {
            $tableService = app(TableManagementService::class);
            $released = $tableService->checkAutoRelease();
            
            if ($released > 0) {
                Log::info("Auto-released {$released} tables");
            }
        })->everyFiveMinutes()->name('table:auto-release');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

---

## 📱 Merchant App UI Implementation

### **1. Settings Page: Table Configuration**

```dart
// lib/screens/settings/table_settings_screen.dart

import 'package:flutter/material.dart';

class TableSettingsScreen extends StatefulWidget {
  final Merchant merchant;

  const TableSettingsScreen({Key? key, required this.merchant}) : super(key: key);

  @override
  State<TableSettingsScreen> createState() => _TableSettingsScreenState();
}

class _TableSettingsScreenState extends State<TableSettingsScreen> {
  late String _paymentFlow;
  late bool _autoRelease;
  late int _dineDuration;

  @override
  void initState() {
    super.initState();
    _paymentFlow = widget.merchant.paymentFlow;
    _autoRelease = widget.merchant.autoReleaseTable;
    _dineDuration = widget.merchant.defaultDineDuration;
  }

  Future<void> _saveSettings() async {
    // API call to update merchant settings
    final response = await ApiService.updateMerchantSettings(
      merchantId: widget.merchant.id,
      paymentFlow: _paymentFlow,
      autoRelease: _autoRelease,
      dineDuration: _dineDuration,
    );

    if (response.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Settings saved successfully')),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to save settings')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Table Settings'),
        actions: [
          IconButton(
            icon: Icon(Icons.save),
            onPressed: _saveSettings,
          ),
        ],
      ),
      body: ListView(
        padding: EdgeInsets.all(16),
        children: [
          // Payment Flow
          Card(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Payment Flow',
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  value: _paymentFlow,
                  decoration: InputDecoration(
                    labelText: 'Tipe Restoran',
                    border: OutlineInputBorder(),
                  ),
                  items: [
                    DropdownMenuItem(
                      value: 'PAY_FIRST',
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Bayar di Awal', style: TextStyle(fontWeight: FontWeight.bold)),
                          Text('Fast Food, Cafe, Food Court', style: TextStyle(fontSize: 12)),
                        ],
                      ),
                    ),
                    DropdownMenuItem(
                      value: 'PAY_LAST',
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Bayar di Akhir', style: TextStyle(fontWeight: FontWeight.bold)),
                          Text('Restaurant, Fine Dining', style: TextStyle(fontSize: 12)),
                        ],
                      ),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _paymentFlow = value!;
                      // Auto-disable auto-release for PAY_LAST
                      if (_paymentFlow == 'PAY_LAST') {
                        _autoRelease = false;
                      }
                    });
                  },
                ),
                SizedBox(height: 8),
                Text(
                  _paymentFlow == 'PAY_FIRST'
                      ? 'Customer bayar saat order → Auto-release meja'
                      : 'Customer bayar setelah makan → Manual release meja',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
              ],
            ),
          ),

          SizedBox(height: 16),

          // Auto Release Toggle
          Card(
            child: SwitchListTile(
              title: Text('Auto Release Meja'),
              subtitle: Text(
                _paymentFlow == 'PAY_FIRST'
                    ? 'Meja otomatis tersedia setelah ${_dineDuration} menit'
                    : 'Nonaktif untuk PAY_LAST (manual release)',
              ),
              value: _autoRelease,
              onChanged: _paymentFlow == 'PAY_FIRST'
                  ? (value) => setState(() => _autoRelease = value)
                  : null, // Disabled for PAY_LAST
            ),
          ),

          SizedBox(height: 16),

          // Dine Duration Slider
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Durasi Makan (menit)',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(Icons.timer, color: Theme.of(context).primaryColor),
                      SizedBox(width: 8),
                      Text(
                        '$_dineDuration menit',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  Slider(
                    value: _dineDuration.toDouble(),
                    min: 30,
                    max: 120,
                    divisions: 18, // 5-minute increments
                    label: '$_dineDuration menit',
                    onChanged: (value) {
                      setState(() {
                        _dineDuration = value.toInt();
                      });
                    },
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('30 min', style: TextStyle(fontSize: 12)),
                      Text('60 min', style: TextStyle(fontSize: 12)),
                      Text('90 min', style: TextStyle(fontSize: 12)),
                      Text('120 min', style: TextStyle(fontSize: 12)),
                    ],
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Estimasi rata-rata customer selesai makan + buffer',
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  Text(
                    'Fast Food: 30 min | Cafe: 60 min | Restaurant: 90 min',
                    style: TextStyle(fontSize: 11, color: Colors.grey),
                  ),
                ],
              ),
            ),
          ),

          SizedBox(height: 24),

          // Info Card
          Container(
            padding: EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.blue[50],
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.blue[200]!),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.info_outline, color: Colors.blue),
                    SizedBox(width: 8),
                    Text(
                      'Tentang Durasi',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                SizedBox(height: 8),
                Text(
                  'Durasi adalah BUFFER TIME, bukan LIMIT. Fungsi:\n'
                  '• Estimasi customer selesai makan\n'
                  '• Auto-release safety net\n'
                  '• Mencegah konflik 2 customer di 1 meja\n'
                  '• Bukan untuk mengusir customer',
                  style: TextStyle(fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

### **2. Table Management Page**

```dart
// lib/screens/pos/table_management_screen.dart

import 'package:flutter/material.dart';

class TableManagementScreen extends StatefulWidget {
  final int merchantId;

  const TableManagementScreen({Key? key, required this.merchantId}) : super(key: key);

  @override
  State<TableManagementScreen> createState() => _TableManagementScreenState();
}

class _TableManagementScreenState extends State<TableManagementScreen> {
  List<MerchantTable> _tables = [];
  Merchant? _merchant;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadTables();
  }

  Future<void> _loadTables() async {
    setState(() => _isLoading = true);

    final response = await ApiService.getTables(widget.merchantId);
    
    setState(() {
      _tables = response.data;
      _merchant = response.merchant;
      _isLoading = false;
    });
  }

  Future<void> _releaseTable(int tableId, int transactionId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Release Table?'),
        content: Text('Pastikan customer sudah bayar dan pergi'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Release'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final response = await ApiService.releaseTable(transactionId);
      
      if (response.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Table released successfully')),
        );
        _loadTables(); // Refresh
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to release table')),
        );
      }
    }
  }

  Future<void> _extendDuration(int transactionId) async {
    final minutes = await showDialog<int>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Extend Duration'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: Text('+15 min'),
              onTap: () => Navigator.pop(context, 15),
            ),
            ListTile(
              leading: Text('+30 min'),
              onTap: () => Navigator.pop(context, 30),
            ),
            ListTile(
              leading: Text('+60 min'),
              onTap: () => Navigator.pop(context, 60),
            ),
          ],
        ),
      ),
    );

    if (minutes != null) {
      final response = await ApiService.extendDuration(transactionId, minutes);
      
      if (response.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Duration extended by $minutes minutes')),
        );
        _loadTables(); // Refresh
      }
    }
  }

  Color _getTableColor(String status) {
    switch (status) {
      case 'AVAILABLE':
        return Colors.green[100]!;
      case 'OCCUPIED':
        return Colors.red[100]!;
      case 'RESERVED':
        return Colors.orange[100]!;
      default:
        return Colors.grey[100]!;
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: Text('Table Management')),
        body: Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Table Management'),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadTables,
          ),
          IconButton(
            icon: Icon(Icons.settings),
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => TableSettingsScreen(merchant: _merchant!),
              ),
            ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadTables,
        child: GridView.builder(
          padding: EdgeInsets.all(16),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: MediaQuery.of(context).size.width > 600 ? 4 : 3,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
            childAspectRatio: 0.8,
          ),
          itemCount: _tables.length,
          itemBuilder: (context, index) {
            final table = _tables[index];
            final transaction = table.activeTransaction;
            final isOccupied = table.status == 'OCCUPIED';
            final duration = transaction?.occupancyDuration ?? 0;

            return Card(
              color: _getTableColor(table.status),
              elevation: 4,
              child: Padding(
                padding: EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Table Number
                    Text(
                      'Meja #${table.tableNumber}',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 4),
                    
                    // Capacity
                    Row(
                      children: [
                        Icon(Icons.people, size: 16),
                        SizedBox(width: 4),
                        Text('${table.capacity} orang'),
                      ],
                    ),
                    
                    Spacer(),
                    
                    // Status Badge
                    Container(
                      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: isOccupied ? Colors.red : Colors.green,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        table.status,
                        style: TextStyle(color: Colors.white, fontSize: 12),
                      ),
                    ),
                    
                    if (isOccupied && transaction != null) ...[
                      SizedBox(height: 8),
                      
                      // Duration Timer
                      Row(
                        children: [
                          Icon(Icons.timer, size: 16, color: Colors.orange),
                          SizedBox(width: 4),
                          Text(
                            '$duration min',
                            style: TextStyle(fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      
                      SizedBox(height: 8),
                      
                      // Action Buttons
                      if (_merchant?.paymentFlow == 'PAY_LAST') ...[
                        ElevatedButton(
                          onPressed: () => _releaseTable(table.id, transaction.id),
                          child: Text('Release'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            minimumSize: Size(double.infinity, 36),
                          ),
                        ),
                        SizedBox(height: 4),
                        OutlinedButton(
                          onPressed: () => _extendDuration(transaction.id),
                          child: Text('+ Extend'),
                          style: OutlinedButton.styleFrom(
                            minimumSize: Size(double.infinity, 36),
                          ),
                        ),
                      ] else ...[
                        // PAY_FIRST - Show auto-release countdown
                        if (transaction.autoReleaseAt != null)
                          Text(
                            'Auto: ${transaction.autoReleaseAt}',
                            style: TextStyle(fontSize: 11),
                          ),
                      ],
                    ],
                  ],
                ),
              ),
            );
          },
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showAddTableDialog(),
        child: Icon(Icons.add),
      ),
    );
  }
}
```

---

## 📊 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/merchant/tables` | Get all tables |
| POST | `/api/merchant/tables` | Create new table |
| PUT | `/api/merchant/tables/{id}` | Update table |
| DELETE | `/api/merchant/tables/{id}` | Delete table |
| POST | `/api/merchant/transactions` | Create DINE_IN transaction |
| POST | `/api/merchant/transactions/{id}/food-completed` | Mark food served |
| POST | `/api/merchant/transactions/{id}/release` | Release table manually |
| POST | `/api/merchant/transactions/{id}/extend` | Extend duration |
| GET | `/api/merchant/tables/ready-to-release` | Get notification list |
| PUT | `/api/merchant/settings/table` | Update table settings |

---

## 🧪 Testing Checklist

### **Backend Tests**

- [ ] Merchant config CRUD
- [ ] Table status transitions
- [ ] Auto-release timer logic
- [ ] Manual release logic
- [ ] Duration extension
- [ ] Scheduler runs every 5 min
- [ ] Activity log created

### **Merchant App Tests**

- [ ] Settings page save/load
- [ ] Table grid display
- [ ] Release button (PAY_LAST)
- [ ] Extend duration button
- [ ] Timer display
- [ ] Color coding (green/red)

---

## 📈 Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Table turnover rate | +20% | Tables used per day |
| Conflicts (2 tables) | 0 | Customer complaints |
| Manual release time | < 30 sec | Time to release |
| Merchant adoption | 80% | Merchants using feature |

---

## 🔗 Related Documents

- [`pos-dine-in-flow.md`](pos-dine-in-flow.md) - Original DINE_IN flow
- [`service-fee-model.md`](service-fee-model.md) - Payment model
- [`active-backlog.md`](active-backlog.md) - Backlog tracking

---

**Last Updated:** 11 Maret 2026  
**Status:** 🔄 In Development  
**Next Review:** After implementation complete
