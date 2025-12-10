class SessionActivityChecker {
    constructor() {
        this.warningTime = 5 * 1000; // 5 secondes
        this.logoutTime = 10 * 1000; // 10 secondes
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.checkInterval = null;
        this.warningTimeout = null;
        this.logoutTimeout = null;
        
        this.init();
    }

    init() {
        this.setupActivityListeners();
        this.startChecking();
    }

    setupActivityListeners() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.resetTimer();
            }, true);
        });
    }

    resetTimer() {
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.hideWarning();
        
        if (this.warningTimeout) {
            clearTimeout(this.warningTimeout);
        }
        if (this.logoutTimeout) {
            clearTimeout(this.logoutTimeout);
        }
        
        this.warningTimeout = setTimeout(() => {
            this.showWarning();
        }, this.warningTime);
        
        this.logoutTimeout = setTimeout(() => {
            this.logout();
        }, this.logoutTime);
    }

    startChecking() {
        this.resetTimer();
        
        this.checkInterval = setInterval(() => {
            const inactiveTime = Date.now() - this.lastActivity;
            
            if (inactiveTime >= this.logoutTime) {
                this.logout();
            } else if (inactiveTime >= this.warningTime && !this.warningShown) {
                this.showWarning();
            }
        }, 10000);
    }

    showWarning() {
        if (this.warningShown) return;
        
        this.warningShown = true;
        
        const existingModal = document.getElementById('inactivityWarningModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const remainingTime = Math.ceil((this.logoutTime - (Date.now() - this.lastActivity)) / 1000);
        
        const modal = document.createElement('div');
        modal.id = 'inactivityWarningModal';
        modal.className = 'inactivity-modal';
        modal.innerHTML = `
            <div class="inactivity-modal-content">
                <div class="inactivity-modal-header">
                    <h3>⚠️ Inactivité détectée</h3>
                </div>
                <div class="inactivity-modal-body">
                    <p>Vous serez déconnecté dans <strong id="remainingTime">${remainingTime}</strong> secondes en raison d'inactivité.</p>
                    <p>Déplacez votre curseur pour annuler la déconnexion automatique.</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        document.getElementById('stayConnectedBtn').addEventListener('click', () => {
            this.resetTimer();
        });
        
        document.getElementById('logoutNowBtn').addEventListener('click', () => {
            this.logout();
        });
        
        const countdownInterval = setInterval(() => {
            const remaining = Math.ceil((this.logoutTime - (Date.now() - this.lastActivity)) / 1000);
            const timeElement = document.getElementById('remainingTime');
            if (timeElement) {
                timeElement.textContent = remaining > 0 ? remaining : 0;
            }
            
            if (remaining <= 0 || !this.warningShown) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    hideWarning() {
        const modal = document.getElementById('inactivityWarningModal');
        if (modal) {
            modal.remove();
        }
    }

    logout() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }
        
        this.hideWarning();
        
        window.location.href = '/project-share/logout';
    }

    destroy() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }
        if (this.warningTimeout) {
            clearTimeout(this.warningTimeout);
        }
        if (this.logoutTimeout) {
            clearTimeout(this.logoutTimeout);
        }
        this.hideWarning();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const isAuthenticated = document.querySelector('body').dataset.authenticated === 'true';
    
    if (isAuthenticated) {
        new SessionActivityChecker();
    }
});
