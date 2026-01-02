class RealTimeManager {
    constructor() {
        this.websocket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.isConnected = false;
        this.subscribers = new Map();
        this.lastUpdate = Date.now();
        
        this.init();
    }
    
    init() {
        this.connect();
        
        // Fallback to polling if WebSocket fails
        if (!this.websocket) {
            this.startPolling();
        }
    }
    
    connect() {
        try {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsUrl = `${protocol}//${window.location.host}/ws`;
            
            this.websocket = new WebSocket(wsUrl);
            
            this.websocket.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.sendHeartbeat();
            };
            
            this.websocket.onmessage = (event) => {
                this.handleMessage(JSON.parse(event.data));
            };
            
            this.websocket.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.attemptReconnect();
            };
            
            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.isConnected = false;
            };
            
        } catch (error) {
            console.error('Failed to connect WebSocket:', error);
            this.startPolling();
        }
    }
    
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.log('Max reconnection attempts reached, falling back to polling');
            this.startPolling();
        }
    }
    
    startPolling() {
        console.log('Starting polling fallback');
        setInterval(() => {
            this.pollUpdates();
        }, 5000);
    }
    
    pollUpdates() {
        fetch('/api/realtime.php?action=poll&last_update=' + this.lastUpdate)
            .then(response => response.json())
            .then(data => {
                if (data.updates && data.updates.length > 0) {
                    data.updates.forEach(update => {
                        this.handleMessage(update);
                    });
                    this.lastUpdate = data.timestamp;
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
            });
    }
    
    handleMessage(message) {
        const { type, data, channel } = message;
        
        // Notify subscribers
        if (this.subscribers.has(channel)) {
            this.subscribers.get(channel).forEach(callback => {
                callback(data);
            });
        }
        
        // Handle specific message types
        switch (type) {
            case 'presence_update':
                this.handlePresenceUpdate(data);
                break;
            case 'points_update':
                this.handlePointsUpdate(data);
                break;
            case 'notification':
                this.handleNotification(data);
                break;
            case 'system_update':
                this.handleSystemUpdate(data);
                break;
        }
    }
    
    handlePresenceUpdate(data) {
        // Update presence indicators
        const presenceElements = document.querySelectorAll(`[data-user-id="${data.user_id}"] .presence-indicator`);
        presenceElements.forEach(element => {
            element.className = `presence-indicator ${data.present ? 'online' : 'offline'}`;
        });
        
        // Update presence counts
        this.updatePresenceCount(data.unit_id, data.present);
    }
    
    handlePointsUpdate(data) {
        // Update points display
        const pointsElements = document.querySelectorAll(`[data-user-id="${data.user_id}"] .points-display`);
        pointsElements.forEach(element => {
            element.textContent = data.points;
            
            // Add animation
            element.classList.add('points-updated');
            setTimeout(() => {
                element.classList.remove('points-updated');
            }, 1000);
        });
        
        // Update rankings
        this.updateRankings(data.unit_id);
    }
    
    handleNotification(data) {
        // Show notification toast
        this.showToast(data.title, data.message, data.type);
        
        // Update notification count
        this.updateNotificationCount();
    }
    
    handleSystemUpdate(data) {
        // Handle system-wide updates
        if (data.action === 'refresh') {
            location.reload();
        } else if (data.action === 'clear_cache') {
            this.clearCache();
        }
    }
    
    subscribe(channel, callback) {
        if (!this.subscribers.has(channel)) {
            this.subscribers.set(channel, new Set());
        }
        this.subscribers.get(channel).add(callback);
        
        // Send subscription message to server
        if (this.isConnected) {
            this.websocket.send(JSON.stringify({
                type: 'subscribe',
                channel: channel
            }));
        }
    }
    
    unsubscribe(channel, callback) {
        if (this.subscribers.has(channel)) {
            this.subscribers.get(channel).delete(callback);
            
            if (this.subscribers.get(channel).size === 0) {
                this.subscribers.delete(channel);
                
                // Send unsubscribe message to server
                if (this.isConnected) {
                    this.websocket.send(JSON.stringify({
                        type: 'unsubscribe',
                        channel: channel
                    }));
                }
            }
        }
    }
    
    sendHeartbeat() {
        if (this.isConnected) {
            this.websocket.send(JSON.stringify({ type: 'heartbeat' }));
            setTimeout(() => this.sendHeartbeat(), 30000);
        }
    }
    
    showToast(title, message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${title}</strong>
                <button class="toast-close">&times;</button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
        
        // Manual close
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });
    }
    
    updatePresenceCount(unitId, isPresent) {
        const countElement = document.querySelector(`[data-unit-id="${unitId}"] .presence-count`);
        if (countElement) {
            const currentCount = parseInt(countElement.textContent);
            countElement.textContent = isPresent ? currentCount + 1 : currentCount - 1;
        }
    }
    
    updateRankings(unitId) {
        // Refresh rankings table
        const rankingsTable = document.querySelector('.rankings-table');
        if (rankingsTable) {
            fetch(`/api/pontos.php?action=rankings&unit_id=${unitId}`)
                .then(response => response.json())
                .then(data => {
                    this.updateRankingsTable(data);
                });
        }
    }
    
    updateRankingsTable(data) {
        const tbody = document.querySelector('.rankings-table tbody');
        if (tbody && data.rankings) {
            tbody.innerHTML = '';
            data.rankings.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.nome}</td>
                    <td>${item.pontos}</td>
                    <td>${item.unidade}</td>
                `;
                tbody.appendChild(row);
            });
        }
    }
    
    updateNotificationCount() {
        fetch('/api/realtime.php?action=notification_count')
            .then(response => response.json())
            .then(data => {
                const countElement = document.querySelector('.notification-count');
                if (countElement) {
                    countElement.textContent = data.count;
                    countElement.style.display = data.count > 0 ? 'inline-block' : 'none';
                }
            });
    }
    
    clearCache() {
        // Clear application cache
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                cacheNames.forEach(cacheName => {
                    caches.delete(cacheName);
                });
            });
        }
    }
}

// Initialize real-time manager
const realTimeManager = new RealTimeManager();

// Export for use in other scripts
window.RealTimeManager = realTimeManager;
