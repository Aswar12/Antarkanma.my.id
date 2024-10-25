[Users]
id (PK)
name
email (unique)
password
roles (ENUM: USER, MERCHANT, COURIER)
username
phone_number
created_at
updated_at
  |
  |--1----* [Merchants]
  |         id (PK)
  |         owner_id (FK -> Users)
  |         name
  |         address
  |         phone_number
  |         created_at
  |         updated_at
  |
  |--1----* [Orders]
  |         id (PK)
  |         user_id (FK -> Users)
  |         total_amount
  |         order_status (ENUM: PENDING, PROCESSING, COMPLETED, CANCELED)
  |         created_at
  |         updated_at
  |
  |--1----* [Loyalty_Points]
  |         id (PK)
  |         user_id (FK -> Users)
  |         points
  |         created_at
  |
  |--1----1 [Couriers]
  |         id (PK)
  |         user_id (FK -> Users)
  |         vehicle_type
  |         license_plate
  |         created_at
  |         updated_at
  |
  |--1----* [User_Locations]
  |         id (PK)
  |         user_id (FK -> Users)
  |         customer_name
  |         address
  |         longitude
  |         latitude
  |         address_type
  |         phone_number
  |         created_at
  |         updated_at
  |
  |--1----* [Product_Reviews]
  |         id (PK)
  |         user_id (FK -> Users)
  |         product_id (FK -> Products)
  |         rating
  |         comment
  |         created_at
  |         updated_at
  |
  |--1----* [Transactions]
            id (PK)
            order_id (FK -> Orders)
            user_id (FK -> Users)
            user_location_id (FK -> User_Locations)
            total_price
            shipping_price
            payment_date
            status (ENUM: PENDING, COMPLETED, CANCELED)
            payment_method (ENUM: MANUAL, ONLINE)
            payment_status (ENUM: PENDING, COMPLETED, FAILED)
            rating
            note
            created_at
            updated_at

[Merchants]
  |
  |--1----* [Products]
            id (PK)
            merchant_id (FK -> Merchants)
            category_id (FK -> Product_Categories)
            name
            description
            price
            created_at
            updated_at
              |
              |--1----* [Product_Galleries]
                        id (PK)
                        products_id (FK -> Products)
                        url
                        softDeletes
                        created_at
                        updated_at

[Product_Categories]
id (PK)
name
softDeletes
created_at
updated_at
  |
  |--1----* [Products]

[Orders]
  |
  |--1----* [Order_Items]
            id (PK)
            order_id (FK -> Orders)
            product_id (FK -> Products)
            merchant_id (FK -> Merchants)
            quantity
            price
            created_at
            updated_at

[Transactions]
  |
  |--1----1 [Delivery]
            id (PK)
            transaction_id (FK -> Transactions)
            courier_id (FK -> Couriers)
            delivery_status (ENUM: PENDING, IN_PROGRESS, DELIVERED, CANCELED)
            estimated_delivery_time
            actual_delivery_time
            created_at
            updated_at
              |
              |--1----* [Delivery_Items]
                        id (PK)
                        delivery_id (FK -> Deliveries)
                        order_item_id (FK -> Order_Items)
                        pickup_status (ENUM: PENDING, PICKED_UP)
                        pickup_time (DATETIME, nullable)
                        created_at
                        updated_at

[Courier_Batches]
id (PK)
courier_id (FK -> Couriers)
status (ENUM: PREPARING, IN_PROGRESS, COMPLETED)
start_time (DATETIME)
end_time (DATETIME)
created_at
updated_at
  |
  |--1----* [Delivery]

[Order_Items]
id (PK)
order_id (FK -> Orders)
product_id (FK -> Products)
merchant_id (FK -> Merchants)
quantity
price
created_at
updated_at
  |
  |--1----1 [Delivery_Items]

[Product_Reviews]
id (PK)
user_id (FK -> Users)
product_id (FK -> Products)
rating
comment
created_at
updated_at

[Products]
  |
  |--1----* [Product_Reviews]

[Users]
  |
  |--1----* [Product_Reviews]




   Penjelasan ini akan mencakup setiap entitas, atributnya, dan hubungan antar entitasnya.

Users

Entitas utama yang menyimpan informasi semua pengguna sistem.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap pengguna.
name: Nama lengkap pengguna.
email (unique): Alamat email pengguna, harus unik.
password: Password terenkripsi untuk keamanan akun.
roles (ENUM: USER, MERCHANT, COURIER): Peran pengguna dalam sistem.
username: Nama pengguna untuk login.
phone_number: Nomor telepon pengguna.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
One-to-Many dengan Merchants: Satu user dapat memiliki banyak merchant.
One-to-Many dengan Orders: Satu user dapat membuat banyak pesanan.
One-to-Many dengan Loyalty_Points: Satu user dapat memiliki banyak entri poin loyalitas.
One-to-One dengan Couriers: Satu user dapat menjadi satu kurir.
One-to-Many dengan User_Locations: Satu user dapat memiliki banyak alamat.
One-to-Many dengan Product_Reviews: Satu user dapat memberikan banyak ulasan produk.
One-to-Many dengan Transactions: Satu user dapat memiliki banyak transaksi.
Merchants

Menyimpan informasi tentang penjual atau toko dalam sistem.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap merchant.
owner_id (Foreign Key ke Users): Menghubungkan merchant dengan pemiliknya.
name: Nama merchant atau toko.
address: Alamat fisik merchant.
phone_number: Nomor telepon merchant.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Users: Banyak merchant dapat dimiliki oleh satu user.
One-to-Many dengan Products: Satu merchant dapat memiliki banyak produk.
One-to-Many dengan Order_Items: Satu merchant dapat memiliki banyak item pesanan.
Products

Menyimpan informasi tentang produk yang dijual dalam sistem.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap produk.
merchant_id (Foreign Key ke Merchants): Menghubungkan produk dengan merchantnya.
category_id (Foreign Key ke Product_Categories): Menghubungkan produk dengan kategorinya.
name: Nama produk.
description: Deskripsi produk.
price: Harga produk.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Merchants: Banyak produk dapat dimiliki oleh satu merchant.
Many-to-One dengan Product_Categories: Banyak produk dapat termasuk dalam satu kategori.
One-to-Many dengan Order_Items: Satu produk dapat muncul di banyak item pesanan.
One-to-Many dengan Product_Galleries: Satu produk dapat memiliki banyak gambar.
One-to-Many dengan Product_Reviews: Satu produk dapat memiliki banyak ulasan.
Product_Categories

Menyimpan kategori-kategori produk.
Atribut:
id (Primary Key): Identifikasi


Product_Categories (lanjutan)

Atribut:
id (Primary Key): Identifikasi unik untuk setiap kategori.
name: Nama kategori.
description: Deskripsi kategori (opsional).
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
One-to-Many dengan Products: Satu kategori dapat memiliki banyak produk.
Orders

Menyimpan informasi tentang pesanan yang dibuat oleh pengguna.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap pesanan.
user_id (Foreign Key ke Users): Menghubungkan pesanan dengan pembelinya.
status (ENUM: PENDING, PROCESSING, SHIPPED, DELIVERED, CANCELLED): Status pesanan.
total_price: Total harga pesanan.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Users: Banyak pesanan dapat dibuat oleh satu pengguna.
One-to-Many dengan Order_Items: Satu pesanan dapat memiliki banyak item pesanan.
One-to-One dengan Deliveries: Satu pesanan terhubung dengan satu pengiriman.
One-to-One dengan Transactions: Satu pesanan terhubung dengan satu transaksi.
Order_Items

Menyimpan detail item dalam setiap pesanan.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap item pesanan.
order_id (Foreign Key ke Orders): Menghubungkan item dengan pesanannya.
product_id (Foreign Key ke Products): Menghubungkan item dengan produknya.
merchant_id (Foreign Key ke Merchants): Menghubungkan item dengan merchantnya.
quantity: Jumlah item yang dipesan.
price: Harga satuan item saat dipesan.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Orders: Banyak item dapat ada dalam satu pesanan.
Many-to-One dengan Products: Banyak item pesanan dapat merujuk pada satu produk.
Many-to-One dengan Merchants: Banyak item pesanan dapat berasal dari satu merchant.
One-to-One dengan Delivery_Items: Satu item pesanan terhubung dengan satu item pengiriman.
Deliveries

Menyimpan informasi tentang pengiriman pesanan.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap pengiriman.
order_id (Foreign Key ke Orders): Menghubungkan pengiriman dengan pesanannya.
courier_id (Foreign Key ke Couriers): Menghubungkan pengiriman dengan kurirnya.
status (ENUM: PENDING, IN_PROGRESS, DELIVERED): Status pengiriman.
estimated_arrival: Perkiraan waktu tiba.
actual_arrival: Waktu tiba sebenarnya.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
One-to-One dengan Orders: Satu pengiriman terhubung dengan satu pesanan.
Many-to-One dengan Couriers: Banyak pengiriman dapat ditangani oleh satu kurir.
One-to-Many dengan Delivery_Items: Satu pengiriman dapat memiliki banyak item pengiriman.
Many-to-One dengan Courier_Batches: Banyak pengiriman dapat dikelompokkan dalam satu batch kurir.
Delivery_Items

Menyimpan detail item dalam setiap pengiriman.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap item pengiriman.
delivery_id (Foreign Key ke Deliveries): Menghubungkan item dengan pengirimannya.
order_item_id (Foreign Key ke Order_Items): Menghubungkan item pengiriman dengan item pesanan.
pickup_status (ENUM: PENDING, PICKED_UP): Status pengambilan item.
pickup_time (DATETIME, nullable): Waktu pengambilan item.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Deliveries: Banyak item pengiriman dapat ada dalam satu pengiriman.
One-to-One dengan Order_Items: Satu item pengiriman terhubung dengan satu item pesanan.
Couriers

Menyimpan informasi tentang kurir yang menangani pengiriman.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap kurir.
user_id (Foreign Key ke Users): Menghubungkan kurir dengan akun penggunanya.
vehicle_type: Jenis kendaraan yang digunakan kurir.
license_plate: Nomor plat kendaraan kurir.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
One-to-One dengan Users: Satu kurir terhubung dengan satu akun pengguna.
One-to-Many dengan Deliveries: Satu kurir dapat menangani banyak pengiriman.
One-to-Many dengan Courier_Batches: Satu kurir dapat memiliki banyak batch pengiriman.
Courier_Batches

Menyimpan informasi tentang batch pengiriman yang ditangani oleh kurir.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap batch.
courier_id (Foreign Key ke Couriers): Menghubungkan batch dengan kurirnya.
status (ENUM: PREPARING, IN_PROGRESS, COMPLETED): Status batch pengiriman.
start_time (DATETIME): Waktu mulai batch pengiriman.
end_time (DATETIME): Waktu selesai batch pengiriman.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Couriers: Banyak batch dapat ditangani oleh satu kurir.
One-to-Many dengan Deliveries: Satu batch dapat mencakup banyak pengiriman.
Transactions

Menyimpan informasi tentang transaksi keuangan dalam sistem.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap transaksi.
user_id (Foreign Key ke Users): Menghubungkan transaksi dengan penggunanya.
order_id (Foreign Key ke Orders): Menghubungkan transaksi dengan pesanannya.
amount: Jumlah transaksi.
status (ENUM: PENDING, COMPLETED, FAILED): Status transaksi.
payment_method: Metode pembayaran yang digunakan.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi (lanjutan):
Many-to-One dengan Users: Banyak transaksi dapat dilakukan oleh satu pengguna.
One-to-One dengan Orders: Satu transaksi terhubung dengan satu pesanan.
Loyalty_Points

Menyimpan informasi tentang poin loyalitas pengguna.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap entri poin loyalitas.
user_id (Foreign Key ke Users): Menghubungkan poin dengan penggunanya.
points: Jumlah poin yang diperoleh atau digunakan.
transaction_type (ENUM: EARNED, REDEEMED): Jenis transaksi poin.
description: Deskripsi transaksi poin.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Users: Banyak entri poin loyalitas dapat dimiliki oleh satu pengguna.
User_Locations

Menyimpan informasi tentang alamat pengguna.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap lokasi.
user_id (Foreign Key ke Users): Menghubungkan lokasi dengan penggunanya.
address_type (ENUM: HOME, OFFICE, OTHER): Jenis alamat.
address: Alamat lengkap.
latitude: Koordinat latitude.
longitude: Koordinat longitude.
is_default (BOOLEAN): Menandai apakah ini alamat default.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Users: Banyak lokasi dapat dimiliki oleh satu pengguna.
Product_Galleries

Menyimpan gambar-gambar produk.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap gambar.
product_id (Foreign Key ke Products): Menghubungkan gambar dengan produknya.
image_url: URL gambar produk.
is_primary (BOOLEAN): Menandai apakah ini gambar utama produk.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Products: Banyak gambar dapat dimiliki oleh satu produk.
Product_Reviews

Menyimpan ulasan produk dari pengguna.
Atribut:
id (Primary Key): Identifikasi unik untuk setiap ulasan.
user_id (Foreign Key ke Users): Menghubungkan ulasan dengan penggunanya.
product_id (Foreign Key ke Products): Menghubungkan ulasan dengan produknya.
rating (INTEGER): Peringkat produk (misalnya 1-5).
comment: Komentar ulasan.
created_at dan updated_at: Timestamp untuk penciptaan dan pembaruan record.
Relasi:
Many-to-One dengan Users: Banyak ulasan dapat diberikan oleh satu pengguna.
Many-to-One dengan Products: Banyak ulasan dapat diberikan untuk satu produk.