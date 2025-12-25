# Bill Editing Feature - Documentation

## Overview
The Smart Udhar System now includes a comprehensive bill editing feature that allows you to modify any aspect of a bill while maintaining complete accountability and transparency through revision tracking.

## Features

### 1. **Comprehensive Editing**
You can edit all aspects of a bill:
- ✅ Bill date and due date
- ✅ Bill items (add, remove, modify)
- ✅ Item quantities and prices
- ✅ GST rates (CGST, SGST, IGST)
- ✅ Discount (amount and type)
- ✅ Round off adjustments
- ✅ Bill status (Pending, Partially Paid, Paid)
- ✅ Category
- ✅ Description and notes

### 2. **Revision Tracking**
Every edit creates a complete revision snapshot:
- **Automatic versioning**: Each edit increments the revision number
- **Complete history**: All previous versions are preserved
- **Change reason**: Mandatory field to document why changes were made
- **Audit trail**: Tracks who made changes and when
- **Data preservation**: Original bill data is never lost

### 3. **User-Friendly Interface**
- Clean, intuitive design
- Real-time calculations
- Easy item management (add/remove)
- Visual feedback for changes
- Revision history display

## How to Use

### Editing a Bill

1. **Access the Edit Page**
   - From the bill list, click the edit (pencil) icon next to any bill
   - Or from the bill view page, click "Edit Bill"

2. **Make Your Changes**
   - Modify any fields as needed
   - Add or remove items using the "+ Add Item" button
   - Update quantities, prices, or tax rates
   - Adjust discount or round-off values

3. **Provide Change Reason**
   - **IMPORTANT**: You must provide a reason for the edit
   - This helps maintain accountability
   - Examples:
     - "Correcting item quantity from 10 to 12"
     - "Customer requested price adjustment"
     - "Fixed GST rate calculation error"

4. **Review and Submit**
   - Check the grand total calculation
   - Click "Update Bill & Create Revision"
   - The system will:
     - Save the current bill as a revision
     - Update the bill with new data
     - Increment the revision number
     - Log the change in history

### Viewing Revision History

On the edit page, scroll down to see the **Revision History** section:
- Shows all previous versions
- Displays revision number, date, and time
- Shows who made the change
- Includes the change reason
- Shows the grand total for each revision

## Database Setup

### Required Migration

Run the following SQL migration to enable bill editing:

```bash
# Navigate to your project directory
cd c:\xampp\htdocs\smart-udhar-system-2

# Run the migration (using MySQL command line or phpMyAdmin)
mysql -u your_username -p your_database_name < database/add_bill_revisions.sql
```

### Tables Created

1. **bill_revisions**
   - Stores complete snapshots of bills before edits
   - Includes all bill data and items (as JSON)
   - Tracks change metadata

2. **Updated: udhar_transactions**
   - Added `revision_number` field
   - Added `last_edited_by` field
   - Added `last_edited_at` timestamp
   - Added `grand_total` computed column

## Security & Permissions

- ✅ Users can only edit their own bills
- ✅ Customer cannot be changed after bill creation (prevents fraud)
- ✅ Bill number is immutable
- ✅ All changes are logged with user ID
- ✅ Complete audit trail for compliance

## Best Practices

### When to Edit a Bill

**Good Reasons:**
- Correcting data entry errors
- Updating quantities based on customer requests
- Fixing calculation mistakes
- Adjusting prices per agreement
- Updating payment status

**Avoid:**
- Making changes without documenting the reason
- Editing bills after they've been paid (unless necessary)
- Changing historical data for reporting purposes

### Change Reason Guidelines

Be specific and clear:
- ❌ Bad: "Update"
- ✅ Good: "Corrected quantity from 5 to 7 units per customer request"

- ❌ Bad: "Fix"
- ✅ Good: "Fixed CGST rate from 9% to 6% as per product category"

## Technical Details

### Revision Creation Process

1. **Before Edit**: System creates a snapshot
   - Captures all bill fields
   - Serializes items as JSON
   - Records current user and timestamp

2. **During Edit**: Transaction-based update
   - Deletes old items
   - Inserts new items
   - Updates bill totals
   - Increments revision number

3. **After Edit**: Confirmation
   - Success message displayed
   - Redirects to bill view
   - Revision visible in history

### Data Integrity

- **Transactions**: All updates use database transactions
- **Rollback**: Failed edits are automatically rolled back
- **Validation**: Required fields are enforced
- **Calculations**: Totals are recalculated automatically

## Troubleshooting

### Common Issues

**Issue**: "Please provide a reason for editing this bill"
- **Solution**: Fill in the "Reason for Editing" field

**Issue**: "Please add at least one item"
- **Solution**: Add at least one item to the bill before saving

**Issue**: Changes not saving
- **Solution**: Check that all required fields are filled
- **Solution**: Ensure database migration has been run

**Issue**: Revision history not showing
- **Solution**: Verify `bill_revisions` table exists
- **Solution**: Check database permissions

## API Reference

### Key Functions

```php
// Create a revision before editing
createBillRevision($conn, $udhar_id, $change_reason)

// Returns: boolean (success/failure)
```

### Database Schema

```sql
-- Bill Revisions Table
CREATE TABLE bill_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    udhar_id INT NOT NULL,
    revision_number INT NOT NULL,
    -- ... (see migration file for complete schema)
);
```

## Future Enhancements

Planned features:
- [ ] Compare revisions side-by-side
- [ ] Restore previous revision
- [ ] Export revision history
- [ ] Email notifications on bill edits
- [ ] Bulk edit capabilities
- [ ] Advanced filtering in revision history

## Support

For issues or questions:
1. Check this documentation
2. Review the revision history for similar changes
3. Contact system administrator

---

**Version**: 1.0  
**Last Updated**: December 26, 2025  
**Author**: Smart Udhar System Team
