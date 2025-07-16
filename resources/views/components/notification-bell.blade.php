<div class="dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" id="notificationDropdown" data-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">
            0
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
            <h6 class="mb-0">Notifications</h6>
            <button class="btn btn-sm btn-link mark-all-read">Mark all as read</button>
        </div>
        <div class="notification-list">
            <!-- Notifications will be loaded here -->
        </div>
    </div>
</div>

@push('styles')
<style>
.notification-badge {
    font-size: 0.7rem;
    padding: 0.25em 0.6em;
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
}
.notification-item {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
.notification-item.unread {
    background-color: #f0f7ff;
}
.notification-time {
    font-size: 0.75rem;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification bell component initialized');
    const notificationBell = document.querySelector('#notificationDropdown');
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationList = document.querySelector('.notification-list');
    const markAllReadBtn = document.querySelector('.mark-all-read');

    function updateNotificationBadge(count) {
        console.log('Updating notification badge count:', count);
        if (count > 0) {
            notificationBadge.textContent = count;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }

    function loadNotifications() {
        console.log('Loading notifications...');
        fetch('/notifications')
            .then(response => {
                console.log('Notifications response:', response);
                return response.json();
            })
            .then(notifications => {
                console.log('Received notifications:', notifications);
                notificationList.innerHTML = notifications.length ? notifications.map(notification => `
                    <div class="notification-item ${notification.read_at ? '' : 'unread'}" data-id="${notification.id}">
                        <div class="d-flex justify-content-between">
                            <div>${notification.data.message}</div>
                            <small class="notification-time">${new Date(notification.created_at).toLocaleDateString()}</small>
                        </div>
                    </div>
                `).join('') : '<div class="p-3 text-center">No notifications</div>';
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = '<div class="p-3 text-center text-danger">Error loading notifications</div>';
            });
    }

    function getUnreadCount() {
        console.log('Getting unread count...');
        fetch('/notifications/unread-count')
            .then(response => {
                console.log('Unread count response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Received unread count:', data);
                updateNotificationBadge(data.count);
            })
            .catch(error => {
                console.error('Error getting unread count:', error);
            });
    }

    // Load notifications when dropdown is opened
    notificationBell.addEventListener('click', function(e) {
        console.log('Notification bell clicked');
        e.preventDefault();
        loadNotifications();
    });

    // Mark notification as read when clicked
    notificationList.addEventListener('click', function(e) {
        const notificationItem = e.target.closest('.notification-item');
        if (notificationItem) {
            const notificationId = notificationItem.dataset.id;
            console.log('Marking notification as read:', notificationId);
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Mark as read response:', response);
                return response.json();
            })
            .then(data => {
                console.log('Notification marked as read:', data);
                notificationItem.classList.remove('unread');
                getUnreadCount();
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
    });

    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', function(e) {
        console.log('Marking all notifications as read');
        e.preventDefault();
        fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Mark all as read response:', response);
            return response.json();
        })
        .then(data => {
            console.log('All notifications marked as read:', data);
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('unread');
            });
            getUnreadCount();
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    });

    // Initial load of unread count
    getUnreadCount();
});
</script>
@endpush 