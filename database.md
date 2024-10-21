Rangkuman Database Antarkanma
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
category_id (Foreign Key ke Product_Categories)
name
description
price
created_at
updated_at
Product_Categories

id (Primary Key)
name
softDeletes
created_at
updated_at
Product_Galleries

id (Primary Key)
products_id (Foreign Key ke Products)
url
softDeletes
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
courier_id (Foreign Key ke Couriers)
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
Users

Memiliki banyak Merchants (1-to-many).
Memiliki banyak Orders (1-to-many).
Memiliki banyak Loyalty_Points (1-to-many).
Memiliki satu Courier (1-to-1).
Memiliki banyak User _Locations (1-to-many).
Merchants

Memiliki banyak Products (1-to-many).
Products

Terkait dengan satu Merchant (many-to-1).
Terkait dengan satu Product_Category (many-to-1).
Memiliki banyak Order_Items (1-to-many).
Memiliki banyak Transaction_Items (1-to-many).
Memiliki banyak Product_Galleries (1-to-many).
Product_Categories

Memiliki banyak Products (1-to-many).
Orders

Memiliki banyak Order_Items (1-to-many).
Terkait dengan satu **User ** (many-to-1).
Terkait dengan satu Merchant (many-to-1).
Order_Items

Terkait dengan satu Order (many-to-1).
Terkait dengan satu Product (many-to-1).
Loyalty_Points

Terkait dengan satu **User ** (many-to-1).
Couriers

Terkait dengan satu **User ** (1-to-1).
Memiliki banyak Deliveries (1-to-many).
Transactions

Terkait dengan satu **User ** (many-to-1).
Memiliki banyak Transaction_Items (1-to-many).
Memiliki satu Delivery (1-to-1).
Terkait dengan satu User _Location (many-to-1).
Terkait dengan satu Courier (many-to-1).
Transaction_Items

Terkait dengan satu Transaction (many-to-1).
Terkait dengan satu Product (many-to-1).
User _Locations

Terkait dengan satu **User ** (many-to-1).
Delivery

Terkait dengan satu Transaction (many-to-1).
Terkait dengan satu Courier (many-to-1).
Kesimpulan
Dengan penambahan tabel product_categories dan product_galleries, serta relasi yang telah diperbarui, struktur database Antarkanma kini lebih komprehensif. Ini memungkinkan pengelolaan kategori produk dan galeri gambar produk dengan lebih efisien.
