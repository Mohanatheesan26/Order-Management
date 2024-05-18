document.addEventListener('DOMContentLoaded', () => {
    const dbRequest = indexedDB.open('ordersDB', 1);

    dbRequest.onupgradeneeded = function(event) {
        const db = event.target.result;
        const objectStore = db.createObjectStore('orders', { autoIncrement: true });
        objectStore.createIndex('customerName', 'customerName', { unique: false });
        objectStore.createIndex('orderValue', 'orderValue', { unique: false });
        objectStore.createIndex('orderDate', 'orderDate', { unique: false });
    };

    dbRequest.onsuccess = function(event) {
        const db = event.target.result;

        document.getElementById('orderForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const customerName = document.getElementById('customerName').value;
            const orderValue = document.getElementById('orderValue').value;
            const orderDate = document.getElementById('orderDate').value;

            const transaction = db.transaction(['orders'], 'readwrite');
            const objectStore = transaction.objectStore('orders');
            objectStore.add({ customerName, orderValue, orderDate });

            transaction.oncomplete = function() {
                console.log('Order added successfully');
                loadOrders();
                document.getElementById('orderForm').reset();
            };
        });

        function loadOrders() {
            const transaction = db.transaction(['orders'], 'readonly');
            const objectStore = transaction.objectStore('orders');

            const ordersTable = document.getElementById('ordersTable').getElementsByTagName('tbody')[0];
            ordersTable.innerHTML = '';

            objectStore.openCursor().onsuccess = function(event) {
                const cursor = event.target.result;
                if (cursor) {
                    const row = ordersTable.insertRow();
                    row.insertCell(0).textContent = cursor.value.customerName;
                    row.insertCell(1).textContent = cursor.value.orderValue;
                    row.insertCell(2).textContent = cursor.value.orderDate;
                    cursor.continue();
                }
            };
        }

        loadOrders();
    };

    dbRequest.onerror = function(event) {
        console.error('Database error:', event.target.errorCode);
    };
});
