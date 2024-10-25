+------------------------+        +------------------------+
|         User           |        |        Merchant        |
+------------------------+        +------------------------+
| -id: int               |        | -id: int               |
| -name: string          |        | -name: string          |
| -email: string         |        | -ownerId: int          |
| -password: string      |        | -address: string       |
| -role: enum            |        | -phoneNumber: string   |
| -username: string      |        +------------------------+
| -phoneNumber: string   |        | +manageProfile()       |
+------------------------+        | +addProduct()          |
| +register()            |        | +editProduct()         |
| +login()               |        | +deleteProduct()       |
| +viewCatalog()         |        | +manageInventory()     |
| +searchProduct()       |        | +viewOrders()          |
| +addToCart()           |        | +processOrder()        |
| +manageCart()          |        | +managePromotions()    |
| +checkout()            |        | +setOperatingHours()   |
| +trackOrder()          |        | +viewAnalytics()       |
| +leaveReview()         |        +------------------------+
| +manageProfile()       |                 |
| +viewOrderHistory()    |                 |
| +manageAddress()       |                 |
| +useLoyaltyPoints()    |                 |
| +viewPromotions()      |                 |
| +saveFavoriteProduct() |                 |
| +cancelOrder()         |                 |
| +requestRefund()       |                 |
+------------------------+                 |
           |                               |
           |                               |
           v                               v
+------------------------+        +------------------------+
|        Product         |        |         Order          |
+------------------------+        +------------------------+
| -id: int               |        | -id: int               |
| -merchantId: int       |        | -userId: int           |
| -categoryId: int       |        | -totalAmount: decimal  |
| -name: string          |        | -status: enum          |
| -description: text     |        +------------------------+
| -price: decimal        |        | +create()              |
+------------------------+        | +update()              |
| +create()              |        | +cancel()              |
| +update()              |        +------------------------+
| +delete()              |                 |
+------------------------+                 |
           |                               |
           |                               |
           v                               v
+------------------------+        +------------------------+
|     ProductGallery     |        |       OrderItem        |
+------------------------+        +------------------------+
| -id: int               |        | -id: int               |
| -productId: int        |        | -orderId: int          |
| -url: string           |        | -productId: int        |
+------------------------+        | -merchantId: int       |
                                  | -quantity: int         |
+------------------------+        | -price: decimal        |
|    ProductCategory     |        +------------------------+
+------------------------+
| -id: int               |        +------------------------+
| -name: string          |        |      Transaction       |
+------------------------+        +------------------------+
                                  | -id: int               |
+------------------------+        | -orderId: int          |
|      LoyaltyPoint      |        | -userId: int           |
+------------------------+        | -userLocationId: int   |
| -id: int               |        | -totalPrice: decimal   |
| -userId: int           |        | -shippingPrice: decimal|
| -points: int           |        | -paymentDate: datetime |
+------------------------+        | -status: enum          |
                                  | -paymentMethod: enum   |
+------------------------+        | -paymentStatus: enum   |
|        Courier         |        | -rating: int           |
+------------------------+        | -note: string          |
| -id: int               |        +------------------------+
|

+------------------------+        +------------------------+
|        Courier         |        |      UserLocation      |
+------------------------+        +------------------------+
| -id: int               |        | -id: int               |
| -userId: int           |        | -userId: int           |
| -vehicleType: string   |        | -customerName: string  |
| -licensePlate: string  |        | -address: string       |
+------------------------+        | -longitude: float      |
| +login()               |        | -latitude: float       |
| +viewDeliveryTasks()   |        | -addressType: string   |
| +acceptDeliveryTask()  |        | -phoneNumber: string   |
| +updateDeliveryStatus()|        +------------------------+
| +completeDelivery()    |        | +create()              |
| +viewDeliveryHistory() |        | +update()              |
| +manageProfile()       |        | +delete()              |
| +viewEarnings()        |        +------------------------+
| +setAvailabilityStatus()|
| +viewDeliveryRoute()   |        +------------------------+
| +contactCustomer()     |        |        Delivery        |
| +reportIssue()         |        +------------------------+
+------------------------+        | -id: int               |
           |                      | -transactionId: int    |
           |                      | -courierId: int        |
           v                      | -status: enum          |
+------------------------+        | -estimatedDeliveryTime:|
|    CourierBatch        |        |  datetime              |
+------------------------+        | -actualDeliveryTime:   |
| -id: int               |        |  datetime              |
| -courierId: int        |        +------------------------+
| -status: enum          |        | +create()              |
| -startTime: datetime   |        | +updateStatus()        |
| -endTime: datetime     |        +------------------------+
+------------------------+                 |
| +create()              |                 |
| +updateStatus()        |                 |
+------------------------+                 |
                                           v
+------------------------+        +------------------------+
|     ProductReview      |        |     DeliveryItem       |
+------------------------+        +------------------------+
| -id: int               |        | -id: int               |
| -userId: int           |        | -deliveryId: int       |
| -productId: int        |        | -orderItemId: int      |
| -rating: int           |        | -pickupStatus: enum    |
| -comment: text         |        | -pickupTime: datetime  |
+------------------------+        +------------------------+
| +create()              |        | +create()              |
| +update()              |        | +updatePickupStatus()  |
+------------------------+        +------------------------+

+------------------------+
|        Admin           |
+------------------------+
| -id: int               |
| -name: string          |
| -email: string         |
| -password: string      |
+------------------------+
| +login()               |
| +manageUsers()         |
| +manageMerchants()     |
| +manageCouriers()      |
| +manageCategories()    |
| +viewTransactionReports()|
| +managePaymentSystem() |
| +manageAppPolicies()   |
| +handleUserComplaints()|
| +managePromotions()    |
| +viewAnalytics()       |
| +manageAppContent()    |
| +setCommissionRates()  |
| +manageThirdPartyIntegrations()|
| +verifyMerchantsAndCouriers()|
| +manageLoyaltySystem() |
| +manageNotifications() |
+------------------------+


Relasi antar kelas:
User memiliki banyak Order, UserLocation, dan LoyaltyPoint.
Merchant memiliki banyak Product.
Product terkait dengan satu ProductCategory dan memiliki banyak ProductGallery.Order memiliki banyak OrderItem.
Transaction terkait dengan satu Order dan satu UserLocation.
Delivery terkait dengan satu Transaction dan satu Courier.
CourierBatch memiliki banyak Delivery.
ProductReview terkait dengan satu User dan satu Product.
DeliveryItem terkait dengan satu Delivery dan satu OrderItem.
Catatan tambahan:

Kelas User memiliki atribut 'role' yang bisa berupa USER, MERCHANT, atau COURIER. Ini memungkinkan satu tabel user untuk menangani berbagai jenis pengguna.
Kelas Merchant, Courier, dan Admin sebenarnya bisa dianggap sebagai ekstensi dari User dengan role yang sesuai. Namun, untuk kejelasan dan pemisahan concern, mereka dibuat sebagai kelas terpisah.
Kelas Transaction menghubungkan Order dengan proses pembayaran dan pengiriman.
CourierBatch memungkinkan pengelompokan beberapa Delivery untuk efisiensi pengiriman.
DeliveryItem memungkinkan pelacakan status pickup untuk setiap item dalam satu pengiriman.
Fungsionalitas utama:

User dapat melakukan berbagai aktivitas seperti melihat katalog, mengelola keranjang, checkout, melacak pesanan, dan memberikan ulasan.
Merchant dapat mengelola produk, melihat dan memproses pesanan, serta mengelola promosi dan jam operasional toko.
Courier dapat menerima dan mengelola tugas pengiriman, memperbarui status pengiriman, dan melaporkan masalah.
Admin memiliki akses luas untuk mengelola seluruh aspek sistem, termasuk pengguna, merchant, kurir, kategori produk, promosi, dan kebijakan aplikasi.
Aspek keamanan dan otorisasi:

Meskipun tidak ditampilkan secara eksplisit, setiap metode dalam kelas-kelas ini harus memiliki mekanisme otorisasi untuk memastikan bahwa hanya pengguna dengan hak akses yang sesuai yang dapat menjalankan fungsi tertentu.
Skalabilitas dan perluasan:

Struktur kelas ini memungkinkan untuk penambahan fitur baru di masa depan. Misalnya, bisa ditambahkan kelas untuk menangani program afiliasi, sistem voucher, atau integrasi dengan layanan pihak ketiga.
Penanganan pembayaran:

Kelas Transaction mencakup informasi pembayaran, tetapi untuk implementasi yang lebih kompleks, mungkin diperlukan kelas terpisah untuk menangani berbagai metode pembayaran dan gateway pembayaran.
Analitik dan pelaporan:

Meskipun tidak ditampilkan secara eksplisit, data dari berbagai kelas ini dapat digunakan untuk menghasilkan laporan dan analitik yang berguna untuk Admin, Merchant, dan mungkin juga untuk Courier.
Notifikasi dan komunikasi:

Sistem ini akan memerlukan mekanisme notifikasi yang kuat untuk memberi tahu pengguna tentang status pesanan, promosi baru, tugas pengiriman, dll. Ini bisa diimplementasikan sebagai kelas atau layanan terpisah.