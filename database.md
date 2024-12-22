# Database Structure Documentation

## Core Tables

### Users and Authentication
#### users
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `email_verified_at` - Timestamp for email verification
- `password` - Encrypted password
- `roles` - Enum: 'USER', 'MERCHANT', 'COURIER'
- `username` - Unique username (nullable)
- `phone_number` - Contact number (nullable)
- `current_team_id` - For team management (nullable)
- `profile_photo_path` - Path to profile photo (nullable)
- `is_active` - User account status
- Standard timestamps

#### password_reset_tokens
- `email` - Primary key
- `token` - Reset token
- `created_at` - Timestamp

#### sessions
- `id` - Primary key (string)
- `user_id` - Foreign key to users
- `ip_address` - Client IP
- `user_agent` - Browser/client info
- `payload` - Session data
- `last_activity` - Last activity timestamp

### E-commerce Core

#### product_galleries
- `id` - Primary key
- `product_id` - Foreign key to products (with cascade delete)
- `url` - Image URL/path
- Soft deletes
- Standard timestamps

#### product_variants
- `id` - Primary key
- `product_id` - Foreign key to products
- `name` - Variant name (e.g., "Size", "Color")
- `value` - Variant value (e.g., "Large", "Red")
- `price_adjustment` - Decimal(10,2) price modification
- `status` - Enum: 'ACTIVE', 'INACTIVE', 'OUT_OF_STOCK'
- Standard timestamps


#### merchants
- `id` - Primary key
- `name` - Merchant/store name
- `owner_id` - Foreign key to users
- `address` - Physical address
- `phone_number` - Contact number
- `status` - Enum: 'active', 'inactive'
- `description` - Store description (nullable)
- `logo` - Store logo path (nullable)
- Standard timestamps

#### products
- `id` - Primary key
- `merchant_id` - Foreign key to merchants
- `category_id` - Foreign key to product_categories
- `name` - Product name
- `description` - Product description
- `status` - Enum: 'ACTIVE', 'INACTIVE', 'OUT_OF_STOCK'
- `price` - Decimal(10,2)
- Standard timestamps

#### product_categories
- `id` - Primary key
- `name` - Category name
- Soft deletes
- Standard timestamps

### Orders and Transactions

#### orders
- `id` - Primary key
- `user_id` - Foreign key to users
- `total_amount` - Decimal(10,2)
- `order_status` - Enum: 'PENDING', 'PROCESSING', 'COMPLETED', 'CANCELED'
- Standard timestamps

#### order_items
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `product_variant_id` - Foreign key to product_variants (nullable)
- `merchant_id` - Foreign key to merchants
- `quantity` - Integer
- `price` - Decimal(10,2)
- Standard timestamps

#### transactions
- `id` - Primary key
- `order_id` - Foreign key to orders
- `user_id` - Foreign key to users
- `user_location_id` - Foreign key to user_locations
- `total_price` - Decimal(10,2)
- `shipping_price` - Decimal(10,2)
- `payment_date` - DateTime (nullable)
- `status` - Enum: 'PENDING', 'COMPLETED', 'CANCELED'
- `payment_method` - Enum: 'MANUAL', 'ONLINE'
- `payment_status` - Enum: 'PENDING', 'COMPLETED', 'FAILED'
- `rating` - Integer (nullable)
- `note` - Text (nullable)
- Standard timestamps

### Delivery System

#### delivery_items
- `id` - Primary key
- `delivery_id` - Foreign key to deliveries
- `order_item_id` - Foreign key to order_items
- `pickup_status` - Enum: 'PENDING', 'PICKED_UP'
- `pickup_time` - DateTime (nullable)
- Standard timestamps

#### deliveries
- `id` - Primary key
- `transaction_id` - Foreign key to transactions
- `courier_id` - Foreign key to couriers
- `delivery_status` - Enum: 'PENDING', 'IN_PROGRESS', 'DELIVERED', 'CANCELED'
- `estimated_delivery_time` - DateTime
- `actual_delivery_time` - DateTime (nullable)
- Standard timestamps

### Loyalty System

#### loyalty_points
- `id` - Primary key
- `user_id` - Foreign key to users (with cascade delete)
- `points` - Integer point balance
- Standard timestamps

### Courier Management

#### courier_batches
- `id` - Primary key
- `courier_id` - Foreign key to couriers
- `status` - Enum: 'PREPARING', 'IN_PROGRESS', 'COMPLETED'
- `start_time` - DateTime
- `end_time` - DateTime
- Standard timestamps

#### couriers
- `id` - Primary key
- `user_id` - Unique foreign key to users (with cascade delete)
- `vehicle_type` - Type of delivery vehicle
- `license_plate` - Vehicle registration number
- Standard timestamps

### Location Management

#### user_locations
- `id` - Primary key
- `user_id` - Foreign key to users
- `address` - Street address
- `city` - City name
- `state` - State/province (nullable)
- `country` - Country name
- `postal_code` - Postal/ZIP code
- `latitude` - Decimal(10,8) (nullable)
- `longitude` - Decimal(11,8) (nullable)
- `is_default` - Boolean
- Standard timestamps

### Reviews and Ratings

#### product_reviews
- `id` - Primary key
- `user_id` - Foreign key to users (with cascade delete)
- `product_id` - Foreign key to products (with cascade delete)
- `rating` - Integer
- `comment` - Text (nullable)
- Standard timestamps

## Key Relationships

1. User Management
   - Users -> Merchants (one-to-many via owner_id)
   - Users -> Orders (one-to-many)
   - Users -> User Locations (one-to-many)
   - Users -> Product Reviews (one-to-many)
   - Users -> Loyalty Points (one-to-one)
   - Users -> Couriers (one-to-one)

2. Product Management
   - Products -> Merchants (many-to-one)
   - Products -> Categories (many-to-one)
   - Products -> Product Reviews (one-to-many)
   - Products -> Order Items (one-to-many)
   - Products -> Product Variants (one-to-many)
   - Products -> Product Galleries (one-to-many, with cascade delete)

3. Order Processing
   - Orders -> Order Items (one-to-many)
   - Orders -> Transactions (one-to-one)
   - Transactions -> Deliveries (one-to-one)

4. Delivery System
   - Deliveries -> Transactions (one-to-one)
   - Deliveries -> Couriers (many-to-one)
   - Deliveries -> Delivery Items (one-to-many)
   - Couriers -> Courier Batches (one-to-many)
   - Delivery Items -> Order Items (one-to-one)

## Database Features
- Soft Deletes: Implemented on product_categories and product_galleries
- Timestamps: All tables include created_at and updated_at
- Foreign Key Constraints: Properly defined with appropriate cascade rules
- Enum Types: Used for status fields and role management
- Decimal Precision: Monetary values use DECIMAL(10,2)
- Nullable Fields: Appropriate use of nullable fields for optional data
- Indexing: Foreign keys and frequently queried fields are indexed

## Recent Updates
1. Added is_active flag to users table
2. Enhanced user locations with additional fields
3. Updated address types to use Indonesian language
