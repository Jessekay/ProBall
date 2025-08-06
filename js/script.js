        let slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("slide");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) {slideIndex = 1}    
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
            slides[slideIndex-1].style.display = "block";  
            dots[slideIndex-1].className += " active";
        }

        fetch('check_session.php').then(response => response.json()).then(data => {
    const sidebar = document.querySelector('.sidebar-menu');
    if (!data.logged_in) {
        sidebar.querySelector('#logout-btn').remove();
    }
});

        // document.addEventListener('DOMContentLoaded', () => {
        //     // Load cart items
        //     fetch('get_cart.php')
        //         .then(response => response.json())
        //         .then(data => {
        //             const cartItems = document.getElementById('cart-items');
        //             let total = 0;
        //             data.items.forEach(item => {
        //                 total += item.price * item.quantity;
        //                 const card = document.createElement('div');
        //                 card.className = 'card';
        //                 card.innerHTML = `
        //                     <img src="images/${item.product_name.toLowerCase().replace(' ', '-')}-ball.png" class="card-img">
        //                     <div class="description">
        //                         <h3>${item.product_name}</h3>
        //                         <p>${item.sport}</p>
        //                         <p>$${item.price} x ${item.quantity}</p>
        //                     </div>
        //                 `;
        //                 cartItems.appendChild(card);
        //             });
        //             document.getElementById('total-amount').textContent = `Total: $${total.toFixed(2)}`;
        //         });

        //     // Load order history
        //     fetch('get_orders.php')
        //         .then(response => response.json())
        //         .then(data => {
        //             const orderHistory = document.getElementById('order-history');
        //             data.orders.forEach(order => {
        //                 const card = document.createElement('div');
        //                 card.className = 'card';
        //                 card.innerHTML = `
        //                     <div class="description">
        //                         <h3>Order #${order.order_id}</h3>
        //                         <p>Total: $${order.total_amount}</p>
        //                         <p>Date: ${new Date(order.order_date).toLocaleDateString()}</p>
        //                         <p>Status: ${order.status}</p>
        //                         <button class="card-btn" onclick="cancelOrder(${order.order_id})">Cancel Order</button>
        //                     </div>
        //                 `;
        //                 orderHistory.appendChild(card);
        //             });
        //         });
        // });

        // function placeOrder() {
        //     fetch('place_order.php', { method: 'POST' })
        //         .then(response => response.json())
        //         .then(data => {
        //             showMessage('cart-message', data.message, data.success);
        //             if (data.success) {
        //                 printReceipt(data.order_id);
        //                 document.getElementById('cart-items').innerHTML = '';
        //                 document.getElementById('total-amount').textContent = 'Total: $0.00';
        //                 // Reload order history
        //                 location.reload();
        //             }
        //         })
        //         .catch(() => showMessage('cart-message', 'Error placing order.', false));
        // }

        // function cancelOrder(orderId) {
        //     if (confirm('Are you sure you want to cancel this order?')) {
        //         fetch('cancel_order.php', {
        //             method: 'POST',
        //             headers: { 'Content-Type': 'application/json' },
        //             body: JSON.stringify({ order_id: orderId })
        //         })
        //         .then(response => response.json())
        //         .then(data => {
        //             showMessage('order-message', data.message, data.success);
        //             if (data.success) {
        //                 location.reload(); // Reload to update order history
        //             }
        //         })
        //         .catch(() => showMessage('order-message', 'Error canceling order.', false));
        //     }
        // }

        // function printReceipt(orderId) {
        //     const receiptWindow = window.open('', '_blank');
        //     receiptWindow.document.write(`
        //         <html>
        //         <head><title>Order Receipt</title><style>
        //             body { font-family: 'Montserrat', sans-serif; color: #fff; background: #0c0c0c; }
        //             .receipt { padding: 20px; text-align: center; }
        //             .receipt h1 { font-size: 2rem; }
        //             .receipt p { font-size: 1rem; }
        //             .btn { padding: 10px 20px; background: #69a4c168; border-radius: 10px; color: #fff; text-decoration: none; }
        //         </style></head>
        //         <body>
        //             <div class="receipt">
        //                 <h1>Order Receipt</h1>
        //                 <p>Order ID: ${orderId}</p>
        //                 <p>Thank you for your order! Your items will be shipped soon.</p>
        //                 <a href="#" class="btn" onclick="window.print()">Print Receipt</a>
        //             </div>
        //         </body>
        //         </html>
        //     `);
        // }

        // function showMessage(elementId, text, isSuccess) {
        //     const messageDiv = document.getElementById(elementId);
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // login

        // function login() {
        //     const email = document.getElementById('email').value;
        //     const password = document.getElementById('password').value;

        //     if (!email || !password) {
        //         showMessage('All fields are required.', false);
        //         return;
        //     }

        //     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        //         showMessage('Please enter a valid email.', false);
        //         return;
        //     }

        //     fetch('login.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({ email, password })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         showMessage(data.message, data.success);
        //         if (data.success) {
        //             setTimeout(() => window.location.href = 'products.html', 2000);
        //         }
        //     })
        //     .catch(() => showMessage('Error logging in.', false));
        // }

        // function showMessage(text, isSuccess) {
        //     const messageDiv = document.getElementById('message');
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // register

        // function register() {
        //     const username = document.getElementById('username').value;
        //     const email = document.getElementById('email').value;
        //     const password = document.getElementById('password').value;
        //     const first_name = document.getElementById('first_name').value;
        //     const last_name = document.getElementById('last_name').value;

        //     if (!username || !email || !password || !first_name || !last_name) {
        //         showMessage('All fields are required.', false);
        //         return;
        //     }

        //     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        //         showMessage('Please enter a valid email.', false);
        //         return;
        //     }

        //     if (password.length < 6) {
        //         showMessage('Password must be at least 6 characters.', false);
        //         return;
        //     }

        //     fetch('save_user.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({ username, email, password, first_name, last_name })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         showMessage(data.message, data.success);
        //         if (data.success) {
        //             setTimeout(() => window.location.href = 'products.html', 2000);
        //         }
        //     })
        //     .catch(() => showMessage('Error registering.', false));
        // }

        // function showMessage(text, isSuccess) {
        //     const messageDiv = document.getElementById('message');
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // user profile

        // Div = document.getElementById('message');
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // profile

        //   document.addEventListener('DOMContentLoaded', () => {
        //     fetch('get_profile.php')
        //         .then(response => response.json())
        //         .then(data => {
        //             document.getElementById('username').value = data.username;
        //             document.getElementById('email').value = data.email;
        //             document.getElementById('first_name').value = data.first_name || '';
        //             document.getElementById('last_name').value = data.last_name || '';
        //             document.getElementById('address').value = data.address || '';
        //             document.getElementById('phone').value = data.phone || '';
        //         });
        // });

        // function updateProfile() {
        //     const email = document.getElementById('email').value;
        //     const first_name = document.getElementById('first_name').value;
        //     const last_name = document.getElementById('last_name').value;
        //     const address = document.getElementById('address').value;
        //     const phone = document.getElementById('phone').value;

        //     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        //         showMessage('Please enter a valid email.', false);
        //         return;
        //     }

        //     fetch('update_profile.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.// document.addEventListener('DOMContentLoaded', () => {
        //     fetch('get_profile.php')
        //         .then(response => response.json())
        //         .then(data => {
        //             document.getElementById('username').value = data.username;
        //             document.getElementById('email').value = data.email;
        //             document.getElementById('first_name').value = data.first_name || '';
        //             document.getElementById('last_name').value = data.last_name || '';
        //             document.getElementById('address').value = data.address || '';
        //             document.getElementById('phone').value = data.phone || '';
        //         });
        // });

        // function updateProfile() {
        //     const email = document.getElementById('email').value;
        //     const first_name = document.getElementById('first_name').value;
        //     const last_name = document.getElementById('last_name').value;
        //     const address = document.getElementById('address').value;
        //     const phone = document.getElementById('phone').value;

        //     if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        //         showMessage('Please enter a valid email.', false);
        //         return;
        //     }

        //     fetch('update_profile.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({ email, first_name, last_name, address, phone })
        //     })
        //     .then(response => response.json())
        //     .then(data => showMessage(data.message, data.success))
        //     .catch(() => showMessage('Error updating profile.', false));
        // }

        // function showMessage(text, isSuccess) {
        //     const messagestringify({ email, first_name, last_name, address, phone })
        //     })
        //     .then(response => response.json())
        //     .then(data => showMessage(data.message, data.success))
        //     .catch(() => showMessage('Error updating profile.', false));
        // }

        // function showMessage(text, isSuccess) {
        //     const messageDiv = document.getElementById('message');
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // product details

        //  document.addEventListener('DOMContentLoaded', () => {
        //     const params = new URLSearchParams(window.location.search);
        //     document.getElementById('product-name').textContent = params.get('name');
        //     document.getElementById('product-sport').textContent = params.get('sport');
        //     document.getElementById('product-description').textContent = params.get('name') === 'Pitch Ball' ? 'Bright, durable, and match-ready.' : 
        //                                                              params.get('name') === 'Strike Ball' ? 'Durable, precise, and built for power.' : 
        //                                                              'Champions League-inspired.';
        //     document.getElementById('product-price').textContent = `$${params.get('price')}`;
        //     document.getElementById('product-image').src = `images/${params.get('name').toLowerCase().replace(' ', '-')}-ball.png`;
        // });

        // function addToCart() {
        //     const params = new URLSearchParams(window.location.search);
        //     const name = params.get('name');
        //     const sport = params.get('sport');
        //     const price = parseFloat(params.get('price'));
        //     const quantity = parseInt(document.getElementById('quantity').value);

        //     if (quantity < 1) {
        //         showMessage('Please enter a valid quantity.', false);
        //         return;
        //     }

        //     fetch('add_to_cart.php', {
        //         method: 'POST',
        //         headers: { 'Content-Type': 'application/json' },
        //         body: JSON.stringify({ name, sport, price, quantity })
        //     })
        //     .then(response => response.json())
        //     .then(data => showMessage(data.message, data.success))
        //     .catch(() => showMessage('Error adding to cart.', false));
        // }

        // function showMessage(text, isSuccess) {
        //     const messageDiv = document.getElementById('message');
        //     messageDiv.textContent = text;
        //     messageDiv.style.color = isSuccess ? 'var(--main-color)' : '#ff0000';
        //     messageDiv.style.display = 'block';
        //     setTimeout(() => messageDiv.style.display = 'none', 3000);
        // }

        // // product 

        //        function searchBalls() {
        //     let input = document.getElementById('searchBar').value.toLowerCase();
        //     let cards = document.querySelectorAll('.card');
        //     cards.forEach(card => {
        //         let name = card.querySelector('h3').textContent.toLowerCase();
        //         card.style.display = name.includes(input) ? 'block' : 'none';
        //     });
        // }