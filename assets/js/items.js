// File: smart-udhar-system/assets/js/items.js

// Sidebar toggle
document.getElementById("sidebarToggle").addEventListener("click", function() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("active");
    } else {
        sidebar.classList.toggle("closed");
        if (sidebar.classList.contains("closed")) {
            mainContent.style.marginLeft = "0";
        } else {
            mainContent.style.marginLeft = "250px";
        }
    }
});

// Auto-hide sidebar on mobile when clicking outside
document.addEventListener("click", function(event) {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("sidebarToggle");

    if (
        window.innerWidth <= 768 &&
        !sidebar.contains(event.target) &&
        !toggleBtn.contains(event.target) &&
        sidebar.classList.contains("active")
    ) {
        sidebar.classList.remove("active");
    }
});

function confirmDelete(itemId, itemName) {
    if (confirm('Are you sure you want to delete "' + itemName + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const input1 = document.createElement('input');
        input1.type = 'hidden';
        input1.name = 'item_id';
        input1.value = itemId;

        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'delete_item';
        input2.value = '1';

        form.appendChild(input1);
        form.appendChild(input2);
        document.body.appendChild(form);
        form.submit();
    }
}

// GST rate validation
document.getElementById('igst_rate')?.addEventListener('input', function() {
    const igst = parseFloat(this.value) || 0;
    const cgst = document.getElementById('cgst_rate');
    const sgst = document.getElementById('sgst_rate');

    if (igst > 0) {
        cgst.value = 0;
        sgst.value = 0;
        cgst.disabled = true;
        sgst.disabled = true;
    } else {
        cgst.disabled = false;
        sgst.disabled = false;
    }
});

// Form validation
document.getElementById('itemForm')?.addEventListener('submit', function(e) {
    const price = parseFloat(document.getElementById('price').value);
    if (isNaN(price) || price <= 0) {
        e.preventDefault();
        alert('Price must be greater than 0');
        document.getElementById('price').focus();
        return false;
    }

    const cgst = parseFloat(document.getElementById('cgst_rate').value) || 0;
    const sgst = parseFloat(document.getElementById('sgst_rate').value) || 0;
    const igst = parseFloat(document.getElementById('igst_rate').value) || 0;

    if (cgst < 0 || cgst > 100 || sgst < 0 || sgst > 100 || igst < 0 || igst > 100) {
        e.preventDefault();
        alert('GST rates must be between 0 and 100');
        return false;
    }

    if (igst > 0 && (cgst > 0 || sgst > 0)) {
        e.preventDefault();
        alert('Please use either IGST (for inter-state) OR CGST+SGST (for intra-state), not both');
        return false;
    }

    return true;
});