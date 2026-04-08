/**
 * Web端埋点SDK
 * 用于采集用户行为数据
 */

(function(window) {
    'use strict';

    // 配置
    const CONFIG = {
        serverUrl: 'api/tracking.php',
        appId: 'web_store_v3',
        sessionTimeout: 30 * 60 * 1000, // 30分钟
        batchSize: 10,
        flushInterval: 5000 // 5秒批量上报
    };

    // Tracking SDK
    class TrackingSDK {
        constructor(options = {}) {
            this.config = { ...CONFIG, ...options };
            this.sessionId = this._generateSessionId();
            this.deviceId = this._getDeviceId();
            this.userId = this._getUserId();
            this.eventQueue = [];
            this.isFlushPending = false;
            
            this._init();
        }

        _init() {
            // 自动采集页面浏览
            this._trackPageView();
            
            // 自动采集点击事件
            this._initAutoTrack();
            
            // 定时批量上报
            setInterval(() => this._flush(), this.config.flushInterval);
            
            // 页面关闭前上报
            window.addEventListener('beforeunload', () => this._flush());
            
            console.log('[TrackingSDK] Initialized', {
                sessionId: this.sessionId,
                deviceId: this.deviceId,
                userId: this.userId
            });
        }

        // 生成会话ID
        _generateSessionId() {
            return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        // 获取设备ID
        _getDeviceId() {
            let deviceId = localStorage.getItem('tracking_device_id');
            if (!deviceId) {
                deviceId = 'dev_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('tracking_device_id', deviceId);
            }
            return deviceId;
        }

        // 获取用户ID
        _getUserId() {
            // 从页面全局变量或Cookie获取
            if (window.CURRENT_USER && window.CURRENT_USER.username) {
                return window.CURRENT_USER.username;
            }
            // 从cookie获取
            const match = document.cookie.match(/user_id=([^;]+)/);
            if (match) return match[1];
            // 匿名用户
            return 'anonymous_' + this.deviceId.substr(-8);
        }

        // 获取当前页面信息
        _getPageInfo() {
            return {
                url: window.location.href,
                path: window.location.pathname,
                title: document.title,
                referrer: document.referrer
            };
        }

        // 构建事件数据
        _buildEvent(eventType, extraData = {}) {
            return {
                event_type: eventType,
                user_id: this.userId,
                session_id: this.sessionId,
                device_id: this.deviceId,
                timestamp: Date.now(),
                page: this._getPageInfo(),
                extra_data: {
                    ...extraData,
                    screen: {
                        width: window.screen.width,
                        height: window.screen.height
                    },
                    viewport: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    }
                }
            };
        }

        // 追踪事件
        track(eventType, extraData = {}) {
            const event = this._buildEvent(eventType, extraData);
            this.eventQueue.push(event);
            
            // 立即上报关键事件
            if (['purchase', 'add_to_cart', 'click'].includes(eventType)) {
                this._flush();
            }
            
            console.log('[TrackingSDK] Event tracked:', eventType, extraData);
        }

        // 自动采集页面浏览
        _trackPageView() {
            this.track('page_view', {
                url: window.location.href,
                title: document.title
            });
        }

        // 初始化自动采集
        _initAutoTrack() {
            // 商品点击
            document.addEventListener('click', (e) => {
                const target = e.target.closest('[data-track]');
                if (target) {
                    const trackType = target.dataset.track;
                    const trackData = {
                        element: trackType,
                        text: target.textContent?.trim().substr(0, 50),
                        ...this._parseTrackData(target.dataset)
                    };
                    this.track('click', trackData);
                }

                // 商品卡片点击
                const productCard = e.target.closest('.product-card');
                if (productCard) {
                    const productId = productCard.dataset.productId;
                    if (productId) {
                        this.track('product_click', {
                            product_id: productId,
                            product_name: productCard.querySelector('.p-title')?.textContent?.trim()
                        });
                    }
                }
            });

            // 加入购物车
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-add-cart, [data-action="add_to_cart"]');
                if (btn) {
                    const productId = btn.dataset.productId;
                    this.track('add_to_cart', {
                        product_id: productId,
                        quantity: 1
                    });
                }
            });

            // 搜索
            const searchForm = document.querySelector('form[action*="search"]');
            if (searchForm) {
                searchForm.addEventListener('submit', (e) => {
                    const keyword = searchForm.querySelector('input[name="q"]')?.value;
                    if (keyword) {
                        this.track('search', { keyword });
                    }
                });
            }
        }

        // 解析data属性
        _parseTrackData(dataset) {
            const data = {};
            for (const key in dataset) {
                if (key.startsWith('track') && key !== 'track') {
                    const propName = key.replace('track', '').toLowerCase();
                    data[propName] = dataset[key];
                }
            }
            return data;
        }

        // 批量上报
        _flush() {
            if (this.eventQueue.length === 0 || this.isFlushPending) return;
            
            this.isFlushPending = true;
            const events = this.eventQueue.splice(0, this.config.batchSize);
            
            // 逐个上报
            const promises = events.map(event => 
                fetch(this.config.serverUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(event)
                }).catch(err => console.error('[TrackingSDK] Failed to send:', err))
            );
            
            Promise.all(promises).finally(() => {
                this.isFlushPending = false;
                // 如果还有数据，继续上报
                if (this.eventQueue.length > 0) {
                    setTimeout(() => this._flush(), 100);
                }
            });
        }

        // 设置用户ID
        setUserId(userId) {
            this.userId = userId;
            localStorage.setItem('tracking_user_id', userId);
        }

        // 手动追踪页面浏览
        trackPageView(pageName, extraData = {}) {
            this.track('page_view', {
                page_name: pageName,
                ...extraData
            });
        }

        // 追踪购买
        trackPurchase(orderId, products, totalAmount) {
            this.track('purchase', {
                order_id: orderId,
                products: products,
                total_amount: totalAmount,
                currency: 'USD'
            });
        }
    }

    // 暴露到全局
    window.TrackingSDK = TrackingSDK;
    
    // 自动初始化
    if (!window.tracker) {
        window.tracker = new TrackingSDK();
    }

})(window);
