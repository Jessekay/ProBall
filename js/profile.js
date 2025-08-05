document.addEventListener('DOMContentLoaded', function() {
    // Load profile data
    loadProfileData();
    
    // Tab navigation
    document.querySelectorAll('.profile-menu a').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            
            // Update active tab
            document.querySelectorAll('.profile-menu li').forEach(li => li.classList.remove('active'));
            this.parentElement.classList.add('active');
            
            // Show target section
            document.querySelectorAll('.profile-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelector(targetId).classList.add('active');
        });
    });
    
    // Avatar upload
    document.getElementById('avatar-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                showNotification('Image size should be less than 2MB', false);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('profile-avatar').src = event.target.result;
                uploadAvatar(file);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Profile form submission
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Password form submission
    document.getElementById('password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
    
    // Password strength indicator
    document.getElementById('new-password').addEventListener('input', function() {
        updatePasswordStrength(this.value);
    });
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });
});

function loadProfileData() {
    fetch('get_profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showNotification(data.error, false);
                return;
            }
            
            const user = data.user;
            const addresses = data.addresses;
            
            // Set user info
            document.getElementById('profile-username').textContent = user.username;
            document.getElementById('first-name').value = user.first_name || '';
            document.getElementById('last-name').value = user.last_name || '';
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone || '';
            
            // Format join date
            const joinDate = new Date(user.created_at);
            document.getElementById('join-date').textContent = joinDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long'
            });
            
            // Load addresses
            loadAddresses(addresses);
        })
        .catch(error => {
            console.error('Error loading profile:', error);
            showNotification('Failed to load profile data', false);
        });
}

function loadAddresses(addresses) {
    const container = document.getElementById('addresses-container');
    container.innerHTML = '';
    
    if (addresses.length === 0) {
        container.innerHTML = '<p>No saved addresses found.</p>';
        return;
    }
    
    addresses.forEach(address => {
        const addressCard = document.createElement('div');
        addressCard.className = 'address-card';
        
        let addressHtml = `
            <div class="address-header">
                <h3>${address.is_default ? 'Default Address' : 'Address'}</h3>
                ${address.is_default ? '<span class="default-badge">Default</span>' : ''}
            </div>
            <p class="address-text">${address.address_line1}</p>
            ${address.address_line2 ? `<p class="address-text">${address.address_line2}</p>` : ''}
            <p class="address-text">${address.city}, ${address.postal_code}</p>
            <p class="address-text">${address.country}</p>
            <div class="address-actions">
                <button class="btn-secondary edit-address" data-id="${address.id}">Edit</button>
                ${!address.is_default ? `<button class="btn-secondary set-default" data-id="${address.id}">Set as Default</button>` : ''}
                ${!address.is_default ? `<button class="btn-danger delete-address" data-id="${address.id}">Delete</button>` : ''}
            </div>
        `;
        
        addressCard.innerHTML = addressHtml;
        container.appendChild(addressCard);
    });
}

function updateProfile() {
    const formData = {
        first_name: document.getElementById('first-name').value.trim(),
        last_name: document.getElementById('last-name').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim()
    };
    
    // Basic validation
    if (!formData.email) {
        showNotification('Email is required', false);
        return;
    }
    
    fetch('update_profile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success);
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('Failed to update profile', false);
    });
}

function uploadAvatar(file) {
    const formData = new FormData();
    formData.append('avatar', file);
    
    fetch('upload_avatar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Avatar updated successfully', true);
            document.getElementById('profile-avatar').src = data.avatarUrl;
        } else {
            showNotification(data.message || 'Failed to upload avatar', false);
        }
    })
    .catch(error => {
        console.error('Error uploading avatar:', error);
        showNotification('Failed to upload avatar', false);
    });
}

function changePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('All password fields are required', false);
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', false);
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('Password must be at least 8 characters', false);
        return;
    }
    
    fetch('change_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message, data.success);
        if (data.success) {
            document.getElementById('password-form').reset();
        }
    })
    .catch(error => {
        console.error('Error changing password:', error);
        showNotification('Failed to change password', false);
    });
}

function updatePasswordStrength(password) {
    const strengthBars = document.querySelectorAll('.strength-bar');
    const strengthText = document.querySelector('.strength-text');
    let strength = 0;
    
    // Reset
    strengthBars.forEach(bar => bar.style.backgroundColor = '#ddd');
    
    if (password.length > 0) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Update bars
    strengthBars.forEach((bar, index) => {
        if (index < strength) {
            bar.style.backgroundColor = 
                strength > 3 ? '#27ae60' : 
                strength > 1 ? '#f39c12' : '#e74c3c';
        }
    });
    
    // Update text
    const strengthMessages = ['Very Weak', 'Weak', 'Moderate', 'Strong', 'Very Strong'];
    strengthText.textContent = strengthMessages[Math.min(strength, 4) - 1];
    strengthText.style.color = 
        strength > 3 ? '#27ae60' : 
        strength > 1 ? '#f39c12' : '#e74c3c';
}

function showNotification(message, isSuccess) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = isSuccess ? 'notification success' : 'notification error';
    notification.classList.remove('hidden');
    
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}