# Class Diagram — Antarkanma Backend

> **Versi**: v2.0 — 24 Februari 2026  
> Mencerminkan Laravel Models, Controllers, dan Services yang **aktual berjalan**.

---

## Backend Class Diagram

```mermaid
classDiagram
    direction TB

    %% ─── MODELS ───────────────────────────────────────────────────
    class Transaction {
        +int id
        +int user_id
        +int user_location_id
        +int courier_id
        +int base_merchant_id
        +decimal total_price
        +decimal shipping_price
        +string status
        +string payment_method
        +string payment_status
        +string courier_approval
        +string courier_status
        +timestamp timeout_at
        +decimal rating
        +string note
        --
        STATUS_PENDING = "PENDING"
        STATUS_COMPLETED = "COMPLETED"
        STATUS_CANCELED = "CANCELED"
        COURIER_PENDING = "PENDING"
        COURIER_APPROVED = "APPROVED"
        COURIER_REJECTED = "REJECTED"
        COURIER_STATUS_IDLE = "IDLE"
        COURIER_STATUS_HEADING_TO_MERCHANT = "HEADING_TO_MERCHANT"
        COURIER_STATUS_AT_MERCHANT = "AT_MERCHANT"
        COURIER_STATUS_HEADING_TO_CUSTOMER = "HEADING_TO_CUSTOMER"
        COURIER_STATUS_AT_CUSTOMER = "AT_CUSTOMER"
        COURIER_STATUS_DELIVERED = "DELIVERED"
        --
        +isPending() bool
        +isCompleted() bool
        +isCanceled() bool
        +isCOD() bool
        +isTimedOut() bool
        +needsCourierApproval() bool
        +allOrdersCompleted() bool
        +canBeProcessed() bool
        +orders() HasMany
        +courier() BelongsTo
        +user() BelongsTo
        +userLocation() BelongsTo
        +baseMerchant() BelongsTo
    }

    class Order {
        +int id
        +int transaction_id
        +int user_id
        +int merchant_id
        +decimal total_amount
        +string order_status
        +string merchant_approval
        +string rejection_reason
        +string customer_note
        --
        STATUS_PENDING = "PENDING"
        STATUS_WAITING_APPROVAL = "WAITING_APPROVAL"
        STATUS_PROCESSING = "PROCESSING"
        STATUS_READY = "READY_FOR_PICKUP"
        STATUS_PICKED_UP = "PICKED_UP"
        STATUS_COMPLETED = "COMPLETED"
        STATUS_CANCELED = "CANCELED"
        MERCHANT_PENDING = "PENDING"
        MERCHANT_APPROVED = "APPROVED"
        MERCHANT_REJECTED = "REJECTED"
        --
        +transaction() BelongsTo
        +merchant() BelongsTo
        +user() BelongsTo
        +orderItems() HasMany
    }

    class OrderItem {
        +int id
        +int order_id
        +int product_id
        +int merchant_id
        +int quantity
        +decimal price
        +string customer_note
        --
        +order() BelongsTo
        +product() BelongsTo
        +merchant() BelongsTo
    }

    class User {
        +int id
        +string name
        +string email
        +string password
        +string roles
        +string phone_number
        +string profile_photo_path
        --
        +merchant() HasOne
        +courier() HasOne
        +transactions() HasMany
        +userLocations() HasMany
        +fcmTokens() HasMany
    }

    class Merchant {
        +int id
        +int owner_id
        +string name
        +string address
        +decimal latitude
        +decimal longitude
        +string phone_number
        +json operating_hours
        +string status
        --
        +owner() BelongsTo
        +products() HasMany
        +orders() HasMany
    }

    class Courier {
        +int id
        +int user_id
        +string vehicle_type
        +string license_plate
        +bool is_available
        +decimal wallet_balance
        --
        +user() BelongsTo
        +transactions() HasMany
        +hasSufficientBalance() bool
    }

    class Product {
        +int id
        +int merchant_id
        +int category_id
        +string name
        +decimal price
        +string status
        --
        +merchant() BelongsTo
        +category() BelongsTo
        +galleries() HasMany
    }

    %% ─── CONTROLLERS ──────────────────────────────────────────────
    class CourierController {
        -FirebaseService firebaseService
        -OsrmService osrmService
        --
        +getNewTransactions(Request) JsonResponse
        +getCourierTransactions(Request) JsonResponse
        +approveTransaction(Request, id) JsonResponse
        +arriveAtMerchant(Request, id) JsonResponse
        +pickupOrder(Request, orderId) JsonResponse
        +arriveAtCustomer(Request, id) JsonResponse
        +completeOrder(Request, orderId) JsonResponse
        +getDailyStatistics(Request) JsonResponse
        +getStatusCounts(Request) JsonResponse
        -getCourier(Request) Courier
    }

    class TransactionController {
        -FirebaseService firebaseService
        --
        +create(Request) JsonResponse
        +index(Request) JsonResponse
        +show(id) JsonResponse
        +cancel(Request, id) JsonResponse
    }

    class MerchantOrderController {
        -FirebaseService firebaseService
        --
        +index(Request) JsonResponse
        +approve(Request, id) JsonResponse
        +reject(Request, id) JsonResponse
        +markReady(Request, id) JsonResponse
    }

    %% ─── SERVICES ─────────────────────────────────────────────────
    class FirebaseService {
        +sendToUser(tokens, data, title, body) void
        +sendToMultiple(userIds, data, title, body) void
    }

    class OsrmService {
        +getRouteDistance(lat1, lon1, lat2, lon2) array
        +calculateCompleteShipping(merchants, destination) array
    }

    %% ─── RELASI ───────────────────────────────────────────────────
    Transaction "1" --> "*" Order : has many
    Transaction "*" --> "1" User : belongs to customer
    Transaction "*" --> "0..1" Courier : belongs to
    Transaction "*" --> "1" Merchant : base merchant

    Order "1" --> "*" OrderItem : has many
    Order "*" --> "1" Merchant : belongs to
    Order "*" --> "1" Transaction : belongs to

    OrderItem "*" --> "1" Product : refers to

    User "1" --> "0..1" Courier : is a
    User "1" --> "0..1" Merchant : owns
    User "1" --> "*" Transaction : creates

    Merchant "1" --> "*" Product : sells

    CourierController ..> Transaction : uses
    CourierController ..> Order : uses
    CourierController ..> Courier : uses
    CourierController ..> FirebaseService : uses
    CourierController ..> OsrmService : uses

    TransactionController ..> Transaction : uses
    TransactionController ..> Order : uses
    TransactionController ..> FirebaseService : uses

    MerchantOrderController ..> Order : uses
    MerchantOrderController ..> FirebaseService : uses
```

---

## Flutter App — Class Diagram (Courier App)

```mermaid
classDiagram
    class TransactionModel {
        +dynamic id
        +int userId
        +String status
        +String courierStatus
        +String courierApproval
        +List~OrderModel~ orders
        +UserModel user
        +UserLocationModel userLocation
        +double distance
        --
        COURIER_STATUS_IDLE = "IDLE"
        COURIER_STATUS_HEADING_TO_MERCHANT = "HEADING_TO_MERCHANT"
        COURIER_STATUS_AT_MERCHANT = "AT_MERCHANT"
        COURIER_STATUS_HEADING_TO_CUSTOMER = "HEADING_TO_CUSTOMER"
        COURIER_STATUS_AT_CUSTOMER = "AT_CUSTOMER"
        COURIER_STATUS_DELIVERED = "DELIVERED"
        --
        +fromJson(json) TransactionModel$
        +toJson() Map
    }

    class OrderModel {
        +dynamic id
        +String orderStatus
        +double totalAmount
        +List~OrderItemModel~ orderItems
        +int merchantId
        --
        +merchantName String
        +fromJson(json) OrderModel$
    }

    class CourierOrderController {
        +RxList~TransactionModel~ activeOrders
        +RxList~TransactionModel~ completedOrders
        +RxBool isLoading
        +RxMap~String,bool~ loadingActions
        --
        +fetchActiveOrders() Future~void~
        +fetchCompletedOrders() Future~void~
        +refresh() Future~void~
        +arriveAtMerchant(id) Future~void~
        +pickupOrder(orderId) Future~void~
        +arriveAtCustomer(id) Future~void~
        +completeOrder(orderId) Future~void~
        -setupFCMListener() void
    }

    class CourierProvider {
        -AuthService authService
        --
        +getNewTransactions(lat, lon) Future~Response~
        +getMyTransactions() Future~Response~
        +approveTransaction(id) Future~Response~
        +rejectTransaction(id) Future~Response~
        +arriveAtMerchant(id) Future~Response~
        +pickupOrder(orderId) Future~Response~
        +arriveAtCustomer(id) Future~Response~
        +completeOrder(orderId) Future~Response~
    }

    class OrderPage {
        -CourierOrderController orderController
        --
        +build(context) Widget
        -buildTransactionCard(tx) Widget
        -buildOrderRow(order, tx) Widget
        -buildMainActionButton(tx) Widget
        -buildCourierStatusBadge(status) Widget
    }

    CourierOrderController --> CourierProvider : uses
    CourierOrderController --> TransactionModel : manages
    TransactionModel "1" --> "*" OrderModel : contains
    OrderPage --> CourierOrderController : observes
```

---

*Terakhir diperbarui: 24 Februari 2026*