# Database Folder

This folder contains all SQL scripts and database-related files for the Smart Udhar System.

## 📁 File Organization

### Core Database Files

- `smart_udhar_system.sql` - Main database schema and initial data dump

### Migration/Update Scripts

- `alter_users.sql` - User table alterations
- `alter_users_corrected.sql` - Corrected user table alterations
- `create_user_logs.sql` - User activity logging table creation

### Data Import Scripts

- `agro_grocery_items.sql` - Agro-grocery items dummy data insertion
- `complete_items_data.sql` - Complete master data for all 35 items (existing + new)

## 🚀 How to Use SQL Files

### Option 1: phpMyAdmin

1. Open phpMyAdmin in your browser
2. Select the `smart_udhar_db` database
3. Go to the "Import" tab
4. Upload the desired SQL file from this folder
5. Click "Go" to execute

### Option 2: MySQL Command Line

```bash
# Navigate to the database folder
cd database/

# Run specific SQL file
mysql -u root smart_udhar_db < agro_grocery_items.sql
```

### Option 3: Via XAMPP Shell

1. Open XAMPP Control Panel
2. Click "Shell" button
3. Navigate to project directory: `cd htdocs/smart-udhar-system-2/database`
4. Run: `mysql -u root smart_udhar_db < agro_grocery_items.sql`

## � Troubleshooting Foreign Key Issues

If you encounter foreign key constraint errors when importing `agro_grocery_items.sql`:

### **Error: #1452 - Cannot add or update a child row: a foreign key constraint fails**

**Cause:** The `items` table references `users` table via `user_id`, but the required user doesn't exist.

**Solutions:**

1. **Automatic Fix (Recommended):** The `agro_grocery_items.sql` file now includes automatic user creation:

   ```sql
   INSERT IGNORE INTO users (id, username, password, ...) VALUES (1, 'admin', ...);
   ```

   This creates user ID 1 if it doesn't exist.

2. **Manual User Creation:** If the automatic fix doesn't work, create the admin user manually:

   ```sql
   INSERT INTO users (id, username, password, full_name, email, shop_name, mobile, role, status)
   VALUES (1, 'admin', '$2y$10$8K1p/5w6QyTJ9LZrVzKdOeJc8QK8qQcXcXcXcXcXcXcXcXcXcXcXc',
   'Admin User', 'admin@myshop.com', 'My Shop', '9876543210', 'admin', 'active');
   ```

3. **Check Existing Users:** Verify what users exist:
   ```sql
   SELECT id, username, full_name FROM users;
   ```

## 📦 Complete Items Data (`complete_items_data.sql`)

This is the comprehensive master data file containing **all 35 items** for your Smart Udhar System.

### What's Included:

- **11 Existing Items**: Items that were already in your database
- **24 New Items**: Additional agro-grocery products including fertilizers, seeds, insecticides, and more
- **Automatic User Creation**: Creates admin user (ID: 1) if not exists
- **Proper Categorization**: Items organized by category (Fertilizers, Seeds, Insecticides, etc.)
- **GST Compliance**: All items include proper GST rates and HSN codes

### Import Instructions:

1. Open phpMyAdmin
2. Select `smart_udhar_db` database
3. Go to "Import" tab
4. Upload `complete_items_data.sql`
5. Click "Go"

### Verification Query:

```sql
SELECT
    COUNT(*) as total_items,
    SUM(CASE WHEN category = 'Fertilizers' THEN 1 ELSE 0 END) as fertilizers,
    SUM(CASE WHEN category = 'Seeds' THEN 1 ELSE 0 END) as seeds,
    SUM(CASE WHEN category = 'Insecticides' THEN 1 ELSE 0 END) as insecticides
FROM items WHERE user_id = 1;
```

**Expected Result:** `total_items = 35`

## �📋 File Descriptions

| File                        | Purpose                                   | Status              |
| --------------------------- | ----------------------------------------- | ------------------- |
| `smart_udhar_system.sql`    | Complete database schema with sample data | ✅ Production Ready |
| `alter_users.sql`           | User table modifications                  | ⚠️ Migration Script |
| `alter_users_corrected.sql` | Corrected user table modifications        | ⚠️ Migration Script |
| `create_user_logs.sql`      | User activity logging setup               | ⚠️ Migration Script |
| `agro_grocery_items.sql`    | Agricultural product data insertion       | ✅ Ready to Import  |
| `complete_items_data.sql`   | Complete master data (35 items total)     | ✅ Ready to Import  |

## ⚠️ Important Notes

- **Backup First**: Always backup your database before running migration scripts
- **Order Matters**: Run core schema first, then migrations, then data imports
- **User ID**: Data import scripts assume user_id = 1 (admin user)
- **Dependencies**: Ensure the main schema is created before running any scripts

## 🔍 Verification

After running any SQL script, verify the results:

```sql
-- Check total items (after importing complete_items_data.sql)
SELECT COUNT(*) as total_items FROM items;

-- Check items by category
SELECT category, COUNT(*) as item_count
FROM items
WHERE user_id = 1
GROUP BY category
ORDER BY item_count DESC;

-- Check agro items specifically
SELECT COUNT(*) as agro_items FROM items
WHERE category IN ('Fertilizers', 'Seeds', 'Insecticides', 'Others');
```

---

**Last Updated:** December 25, 2025
**Location:** `database/` folder
