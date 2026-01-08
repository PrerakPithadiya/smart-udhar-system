# Tax Invoice Bill Format - Setup Guide

## Overview
A new tax invoice print format has been created that exactly matches your provided bill image. This format includes all the necessary details for GST compliance and professional invoicing.

## Features of the Tax Invoice Format

### Header Section
- **License Number** with date (e.g., FFR230000228 Dt. 10/02/2023)
- **TAX INVOICE** title prominently displayed
- **GST Number** (e.g., 24AAFCJ3152N1ZG)

### Company Details
- Company name in bold
- Complete address with village, taluka, district details
- Registration number with date

### Bill Information
- Bill number
- **CREDIT MEMO** designation
- Transaction date

### Customer Information
- Buyer's name
- Buyer's address

### Items Table
Columns include:
- Sr No.
- Item Name
- HSN Code
- GST% (combined CGST + SGST + IGST)
- Unit Sold
- Qty Sold
- Rate Rate
- Total Amount

### Summary Section
- Total
- Discount
- Taxable Amount
- CGST
- SGST
- Discount/Round off
- **Total Payable Amount** (in numbers and words)

### Footer
- Phone numbers
- Amount in words (e.g., "Two Hundred Seventy Rupees Only.")
- Buyer's Signature section
- Company signature section

## Setup Instructions

### Step 1: Add Database Fields

Run the setup script to add the necessary fields to your database:

1. Open your browser and navigate to:
   ```
   http://localhost/smart-udhar-system-2/setup_shop_details.php
   ```

2. This will add the following fields to the `users` table:
   - `license_no` - Your business license number
   - `license_date` - License issue date
   - `gst_no` - Your GST identification number
   - `registration_no` - Registration number
   - `registration_date` - Registration date

### Step 2: Update Your Profile

1. Go to **Profile** page from the dashboard
2. Scroll down to the **Tax Invoice Details** section
3. Fill in the following information:
   - **License Number**: e.g., FFR230000228
   - **License Date**: Select the date
   - **GST Number**: e.g., 24AAFCJ3152N1ZG (15 characters)
   - **Registration Number**: e.g., 125338
   - **Registration Date**: Select the date

4. Click **Update Profile** to save

### Step 3: Print Bills

You now have two print format options:

#### Option 1: From Bill List Page
1. Go to **Udhar** page
2. Find the bill you want to print
3. Click the **Print** dropdown button (🖨️)
4. Choose:
   - **Standard Bill** - Original A5 format
   - **Tax Invoice** - New GST-compliant format

#### Option 2: From Bill View Page
1. Open any bill details
2. Click the **Print** dropdown button
3. Select your preferred format

## File Structure

### New Files Created
1. **print_bill_tax_invoice.php** - Main tax invoice template
2. **setup_shop_details.php** - Database setup script
3. **database/add_shop_details.sql** - SQL migration file

### Modified Files
1. **profile.php** - Added tax invoice details fields
2. **udhar_view.php** - Added print format dropdown menus

## Customization

### Modifying the Tax Invoice Layout

If you need to adjust the layout, edit `print_bill_tax_invoice.php`:

- **Page Size**: Currently set to A4. Change in `@page { size: A4; }`
- **Fonts**: Modify the `font-family` in the `body` style
- **Colors**: Adjust border colors and backgrounds in the CSS section
- **Column Widths**: Modify the `.col-*` classes for table columns

### Number to Words Function

The bill includes an automatic number-to-words converter that displays the total amount in words (e.g., "Two Hundred Seventy Rupees Only."). This function supports:
- Crores
- Lakhs
- Thousands
- Hundreds
- Tens and Units

## Printing Tips

1. **Browser Print Settings**:
   - Use Chrome or Edge for best results
   - Set margins to "Default" or "Minimum"
   - Enable "Background graphics" for better appearance
   - Paper size: A4

2. **PDF Generation**:
   - Use "Save as PDF" option in print dialog
   - This creates a digital copy for email or storage

3. **Physical Printing**:
   - Use good quality paper (80 GSM or higher)
   - Ensure printer is set to A4 size
   - Check print preview before printing

## Troubleshooting

### Issue: Fields not showing in profile
**Solution**: Make sure you ran `setup_shop_details.php` first

### Issue: Tax invoice shows empty values
**Solution**: Update your profile with License No., GST No., etc.

### Issue: Print layout is broken
**Solution**: 
- Clear browser cache
- Try a different browser (Chrome recommended)
- Check if all CSS is loading properly

### Issue: Amount in words not displaying correctly
**Solution**: This is automatically calculated. If incorrect, check the `numberToWords()` function in `print_bill_tax_invoice.php`

## Important Notes

1. **Data Preservation**: All your existing bill data remains unchanged
2. **Backward Compatibility**: The standard bill format still works as before
3. **GST Compliance**: Ensure your GST number is valid and active
4. **Legal Requirements**: Consult with your accountant to ensure all required fields are present

## Support

If you encounter any issues:
1. Check the browser console for errors (F12)
2. Verify database fields were added correctly
3. Ensure all profile fields are filled in
4. Check file permissions on the server

## Future Enhancements

Possible additions you might want:
- QR code for digital payments
- Barcode for bill tracking
- Company logo upload
- Multiple tax rate support
- E-invoice integration

---

**Created**: January 2026
**Version**: 1.0
**Compatibility**: Smart Udhar System v2.0
