Rangkuman Database
Entitas dan Atribut
Users

id (Primary Key)
name
email (unique)
password
roles (USER, MERCHANT, COURIER)
username
phone_number
created_at
updated_at
Merchants

id (Primary Key)
name
owner_id (Foreign Key ke Users)
address
phone_number
created_at
updated_at
Products

id (Primary Key)
merchant_id (Foreign Key ke Merchants)
name
description
price
created_at
updated_at
Orders

id (Primary Key)
user_id (Foreign Key ke Users)
merchant_id (Foreign Key ke Merchants)
total_amount
payment_status
order_status (PENDING, COMPLETED, CANCELED)
created_at
updated_at
Order_Items

id (Primary Key)
order_id (Foreign Key ke Orders)
product_id (Foreign Key ke Products)
quantity
created_at
updated_at
Loyalty_Points

id (Primary Key)
user_id (Foreign Key ke Users)
points
created_at
Couriers

id (Primary Key)
user_id (Foreign Key ke Users)
vehicle_type
license_plate
created_at
updated_at
Transactions

id (Primary Key)
user_id (Foreign Key ke Users)
address
total_price
shipping_price
status (PENDING, COMPLETED, CANCELED)
payment (MANUAL, ONLINE)
payment_status (PENDING, COMPLETED, FAILED)
user_location_id (Foreign Key ke User_Locations)
kurir_id (Foreign Key ke Couriers)
rating
note
created_at
updated_at
Transaction_Items

id (Primary Key)
transaction_id (Foreign Key ke Transactions)
product_id (Foreign Key ke Products)
quantity
created_at
updated_at
User _Locations

id (Primary Key)
customer_name
user_id (Foreign Key ke Users)
address
longitude
latitude
address_type
phone_number
created_at
updated_at
Delivery

id (Primary Key)
transaction_id (Foreign Key ke Transactions)
courier_id (Foreign Key ke Couriers)
delivery_status (PENDING, IN_PROGRESS, DELIVERED, CANCELED)
estimated_delivery_time
actual_delivery_time
created_at
updated_at
Relasi Antar Tabel
Users memiliki banyak Merchants (1-to-many).
Merchants memiliki banyak Products (1-to-many).
Users memiliki banyak Orders (1-to-many).
Orders memiliki banyak Order_Items (1-to-many).
Order_Items terkait dengan satu Product (many-to-1).
Users memiliki banyak Loyalty_Points (1-to-many).
Users memiliki satu Courier (1-to-1).
Transactions terkait dengan satu **User ** (many-to-1).
Transactions memiliki banyak Transaction_Items (1-to-many).
Transaction_Items terkait dengan satu Product (many-to-1).
User _Locations terkait dengan satu **User ** (many-to-1).
Delivery terkait dengan satu Transaction (many-to-1).
Delivery terkait dengan satu Courier (many-to-

lanjutkan


Tentu! Berikut adalah kelanjutan dari rangkuman relasi antar tabel:

Relasi Antar Tabel (Lanjutan)
Delivery terkait dengan satu Courier (many-to-1).
Transactions memiliki satu Delivery (1-to-1), yang menyimpan informasi tentang pengantaran untuk transaksi tersebut.
Diagram ERD (Entity-Relationship Diagram)
Berikut adalah gambaran umum dari relasi antar tabel dalam bentuk diagram ERD:


Verify

Open In Editor
Edit
Copy code
Users
  ├──< Merchants
  ├──< Orders
  ├──< Loyalty_Points
  ├──< User_Locations
  └──< Couriers
       └──< Delivery
       
Merchants
  └──< Products

Orders
  └──< Order_Items

Products
  └──< Order_Items
  └──< Transaction_Items

Transactions
  ├──< Transaction_Items
  └──< Delivery
Penjelasan Relasi
Users dan Merchants: Setiap pengguna dapat memiliki banyak merchant (pemilik usaha), tetapi setiap merchant hanya dimiliki oleh satu pengguna (owner).
Merchants dan Products: Setiap merchant dapat memiliki banyak produk, tetapi setiap produk hanya terkait dengan satu merchant.
Users dan Orders: Setiap pengguna dapat membuat banyak pesanan, tetapi setiap pesanan hanya terkait dengan satu pengguna.
Orders dan Order_Items: Setiap pesanan dapat memiliki banyak item pesanan, tetapi setiap item pesanan hanya terkait dengan satu pesanan.
Order_Items dan Products: Setiap item pesanan terkait dengan satu produk, tetapi satu produk dapat muncul di banyak item pesanan.
Users dan Loyalty_Points: Setiap pengguna dapat memiliki banyak poin loyalitas, tetapi setiap poin loyalitas hanya terkait dengan satu pengguna.
Users dan Couriers: Setiap pengguna dapat menjadi kurir, dan setiap kurir hanya terkait dengan satu pengguna.
Transactions dan Users: Setiap transaksi terkait dengan satu pengguna, tetapi satu pengguna dapat memiliki banyak transaksi.
Transactions dan Transaction_Items: Setiap transaksi dapat memiliki banyak item transaksi, tetapi setiap item transaksi hanya terkait dengan satu transaksi.
Transaction_Items dan Products: Setiap item transaksi terkait dengan satu produk, tetapi satu produk dapat muncul di banyak item transaksi.
User _Locations dan Users: Setiap lokasi pengguna terkait dengan satu pengguna, tetapi satu pengguna dapat memiliki banyak lokasi.
Delivery dan Transactions: Setiap pengantaran terkait dengan satu transaksi, tetapi satu transaksi hanya memiliki satu pengantaran.
Delivery dan Couriers: Setiap pengantaran terkait dengan satu kurir, tetapi satu kurir dapat melakukan banyak pengantaran.
Kesimpulan
Dengan struktur database dan relasi antar tabel yang telah dirangkum di atas, aplikasi Antarkanma dapat mengelola pengguna, merchant, produk, pesanan, transaksi, dan pengantaran dengan efisien. Sistem pembayaran online dan biaya layanan per transaksi juga dapat diimplementasikan dengan baik dalam struktur ini.
