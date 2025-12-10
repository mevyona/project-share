class NotificationSystem {
    constructor() {
        this.bell = document.getElementById('notificationBell');
        this.badge = document.getElementById('notificationBadge');
        this.dropdown = document.getElementById('notificationDropdown');
        this.list = document.getElementById('notificationList');
        this.dropdownCount = document.getElementById('dropdownCount');
        this.markAllReadBtn = document.getElementById('markAllReadBtn');
        this.isOpen = false;
        this.refreshInterval = null;

        this.init();
    }

    init() {
        if (!this.bell) return;

        this.bell.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.dropdown.contains(e.target)) {
                this.closeDropdown();
            }
        });

        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        this.loadNotifications();
        this.startAutoRefresh();
    }

    toggleDropdown() {
        if (this.isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
        this.loadNotifications();
    }

    closeDropdown() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
    }

    async loadNotifications() {
        try {
            const response = await fetch('/project-share/notification/list');
            const data = await response.json();
            
            this.updateBadge(data.unreadCount);
            this.renderNotifications(data.notifications, data.unreadCount);
        } catch (error) {
            console.error('Erreur lors du chargement des notifications:', error);
        }
    }

    async updateCount() {
        try {
            const response = await fetch('/project-share/notification/count');
            const data = await response.json();
            this.updateBadge(data.count);
        } catch (error) {
            console.error('Erreur lors de la mise à jour du compteur:', error);
        }
    }

    updateBadge(count) {
        if (count > 0) {
            this.badge.textContent = count > 99 ? '99+' : count;
            this.badge.style.display = 'block';
            this.dropdownCount.textContent = count;
            if (this.markAllReadBtn) {
                this.markAllReadBtn.style.display = 'inline-block';
            }
        } else {
            this.badge.style.display = 'none';
            this.dropdownCount.textContent = '0';
            if (this.markAllReadBtn) {
                this.markAllReadBtn.style.display = 'none';
            }
        }
    }

    renderNotifications(notifications, unreadCount) {
        if (notifications.length === 0) {
            this.list.innerHTML = '<div class="notifications-empty"><p>Aucune notification</p></div>';
            return;
        }

        const html = notifications.map(notification => `
            <div class="notification-item ${notification.typeClass} ${!notification.isRead ? 'notification-unread' : ''}" 
                 data-notification-id="${notification.id}">
                <div class="notification-icon">
                    <span class="icon">${notification.icon}</span>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">${this.escapeHtml(notification.title)}</h4>
                        <span class="notification-time">${this.formatDate(notification.createdAt)}</span>
                    </div>
                    <p class="notification-message">${this.escapeHtml(notification.message)}</p>
                    ${notification.link ? `<a href="${notification.link}" class="notification-link">Voir plus</a>` : ''}
                </div>
                <div class="notification-actions">
                    ${!notification.isRead ? `
                        <button type="button" class="btn-mark-read" data-action="mark-read" title="Marquer comme lu">✓</button>
                    ` : ''}
                    <button type="button" class="btn-delete" data-action="delete" title="Supprimer">✕</button>
                </div>
            </div>
        `).join('');

        this.list.innerHTML = html;
        this.attachEventHandlers();
    }

    attachEventHandlers() {
        this.list.querySelectorAll('[data-action="mark-read"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const item = e.target.closest('.notification-item');
                const id = item.dataset.notificationId;
                this.markAsRead(id);
            });
        });

        this.list.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const item = e.target.closest('.notification-item');
                const id = item.dataset.notificationId;
                this.deleteNotification(id);
            });
        });
    }

    async markAsRead(id) {
        try {
            const response = await fetch(`/project-share/notification/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erreur lors du marquage de la notification:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('/project-share/notification/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erreur lors du marquage de toutes les notifications:', error);
        }
    }

    async deleteNotification(id) {
        try {
            const response = await fetch(`/project-share/notification/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (response.ok) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression de la notification:', error);
        }
    }

    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.updateCount();
        }, 30000);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        if (diff < 60) return 'À l\'instant';
        if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
        if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;

        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const notificationSystem = new NotificationSystem();
    
    const testBtn = document.getElementById('testNotificationBtn');
    if (testBtn) {
        testBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch('/project-share/notification/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                if (response.ok) {
                    setTimeout(() => {
                        notificationSystem.loadNotifications();
                    }, 300);
                }
            } catch (error) {
                console.error('Erreur lors de l\'envoi de la notification de test:', error);
            }
        });
    }
});
