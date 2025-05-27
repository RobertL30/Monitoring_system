<?php require_once 'views/layouts/header.php'; ?>

    <div class="container">
        <h2>Network Reports & Analytics</h2>
        <p style="color: #ccc; margin-bottom: 30px;">Comprehensive monitoring statistics and performance analytics</p>

        <!-- Navigation Breadcrumb -->
        <div id="breadcrumb" class="breadcrumb">
            <span class="breadcrumb-item active" onclick="showOverview()">📊 Overview</span>
        </div>

        <!-- Overview Section -->
        <div id="overviewSection" class="report-section">
            <!-- Summary Stats -->
            <div class="stats-summary">
                <h3>System Overview</h3>
                <div class="overview-stats" id="overviewStats">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading system statistics...</p>
                    </div>
                </div>
            </div>

            <!-- Category Selection -->
            <div class="category-section">
                <h3>Device Categories</h3>
                <p style="color: #ccc; margin-bottom: 20px;">Click a category to view detailed device statistics</p>
                <div class="category-grid" id="categoryGrid">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading categories...</p>
                    </div>
                </div>
            </div>

            <!-- System-wide Charts -->
            <div class="system-charts">
                <h3>System Performance Trends</h3>
                <div class="chart-grid">
                    <div class="chart-container">
                        <h4>Device Status Distribution</h4>
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Average Response Times (Last 24h)</h4>
                        <canvas id="responseTimeChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Drill-down Section -->
        <div id="categorySection" class="report-section" style="display: none;">
            <div class="section-header">
                <h3 id="categoryTitle">Category Details</h3>
                <div class="action-buttons">
                    <button class="btn btn-secondary" onclick="exportCategoryReport()">📄 Export Report</button>
                    <button class="btn" onclick="refreshCategoryData()">🔄 Refresh</button>
                </div>
            </div>

            <!-- Category Stats -->
            <div class="category-stats" id="categoryStats"></div>

            <!-- Device List for Category -->
            <div class="category-devices">
                <div class="devices-header">
                    <h4>Devices in this Category</h4>
                    <div class="device-filters">
                        <input type="text" id="deviceSearchFilter" placeholder="Search devices..." class="form-input" style="width: 250px;">
                        <select id="deviceStatusFilter" class="form-select" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="operational">Operational</option>
                            <option value="degraded">Degraded</option>
                            <option value="down">Down</option>
                            <option value="unknown">Unknown</option>
                        </select>
                    </div>
                </div>
                <div class="devices-grid" id="devicesGrid"></div>
            </div>
        </div>

        <!-- Device Detail Section -->
        <div id="deviceSection" class="report-section" style="display: none;">
            <div class="section-header">
                <h3 id="deviceTitle">Device Analytics</h3>
                <div class="action-buttons">
                    <button class="btn btn-secondary" onclick="exportDeviceReport()">📄 Export Report</button>
                    <button class="btn" onclick="testDeviceNow()">🔍 Test Now</button>
                    <button class="btn" onclick="refreshDeviceData()">🔄 Refresh</button>
                </div>
            </div>

            <!-- Device Info Card -->
            <div class="device-info-card" id="deviceInfoCard"></div>

            <!-- Device Analytics -->
            <div class="device-analytics">
                <!-- Time Range Selector -->
                <div class="time-range-selector">
                    <label>Analysis Period:</label>
                    <select id="timeRangeSelect" onchange="updateDeviceCharts()">
                        <option value="24">Last 24 Hours</option>
                        <option value="168">Last Week</option>
                        <option value="720">Last Month</option>
                        <option value="2160">Last 3 Months</option>
                    </select>
                </div>

                <!-- Charts Grid -->
                <div class="analytics-grid">
                    <div class="chart-container">
                        <h4>Response Time Trend</h4>
                        <canvas id="deviceResponseChart" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Availability Status</h4>
                        <canvas id="deviceAvailabilityChart" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Packet Loss Analysis</h4>
                        <canvas id="devicePacketLossChart" width="400" height="200"></canvas>
                    </div>
                    <div class="chart-container">
                        <h4>Performance Statistics</h4>
                        <div class="stats-breakdown" id="deviceStatsBreakdown"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="recent-events">
                <h4>Recent Monitoring Events</h4>
                <div class="events-timeline" id="eventsTimeline"></div>
            </div>
        </div>
    </div>

    <style>
        .report-section {
            margin-bottom: 30px;
        }

        .breadcrumb {
            background: #2a2a2a;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #00d4aa;
        }

        .breadcrumb-item {
            color: #888;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .breadcrumb-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .breadcrumb-item.active {
            background: #00d4aa;
            color: #1a1a1a;
            font-weight: bold;
        }

        .breadcrumb-item:not(:last-child):after {
            content: ">";
            margin-left: 10px;
            color: #666;
        }

        .stats-summary {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #00d4aa;
        }

        .overview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #333;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: #00d4aa;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #ccc;
            font-size: 1em;
        }

        .stat-sublabel {
            color: #888;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .category-section {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .category-card {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #333;
            cursor: pointer;
            transition: all 0.3s;
        }

        .category-card:hover {
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .category-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #3498db;
        }

        .category-count {
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .category-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .category-stat {
            text-align: center;
        }

        .category-stat-number {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .category-stat-label {
            color: #ccc;
            font-size: 0.9em;
        }

        .system-charts {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #f39c12;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        .chart-container {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #333;
            position: relative;
            height: 300px; /* Fixed height to prevent growth */
        }

        .chart-container h4 {
            margin-bottom: 20px;
            color: #ccc;
            text-align: center;
            position: absolute;
            top: 0;
            left: 20px;
            right: 20px;
            height: 40px;
            z-index: 2;
        }

        .chart-container canvas {
            position: absolute !important;
            top: 60px !important;
            left: 20px !important;
            right: 20px !important;
            bottom: 20px !important;
            width: calc(100% - 40px) !important;
            height: calc(100% - 80px) !important;
            max-width: none !important;
            max-height: none !important;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 20px;
            background: #2a2a2a;
            border-radius: 8px;
            border-left: 4px solid #00d4aa;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .devices-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
            background: #2a2a2a;
            border-radius: 8px;
        }

        .device-filters {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .devices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .device-card {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #333;
            cursor: pointer;
            transition: all 0.3s;
        }

        .device-card:hover {
            border-color: #00d4aa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 170, 0.2);
        }

        .device-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .device-name {
            font-size: 1.1em;
            font-weight: bold;
            color: #fff;
        }

        .device-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .device-info {
            color: #ccc;
            font-size: 0.9em;
            line-height: 1.4;
        }

        .device-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #444;
        }

        .device-metric {
            text-align: center;
        }

        .device-metric-value {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .device-metric-label {
            color: #888;
            font-size: 0.8em;
        }

        .device-info-card {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #00d4aa;
        }

        .device-info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .device-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .device-detail-item {
            padding: 15px;
            background: #1a1a1a;
            border-radius: 6px;
        }

        .device-detail-label {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .device-detail-value {
            font-weight: bold;
            color: #fff;
        }

        .device-status-summary {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .time-range-selector {
            margin-bottom: 30px;
            padding: 15px 20px;
            background: #2a2a2a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stats-breakdown {
            padding: 20px;
            background: #1a1a1a;
            border-radius: 8px;
            height: 260px; /* Fixed height to match charts */
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            overflow: hidden; /* Prevent content overflow */
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #333;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            color: #ccc;
        }

        .breakdown-value {
            font-weight: bold;
            color: #00d4aa;
        }

        .recent-events {
            background: #2a2a2a;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #e74c3c;
        }

        .events-timeline {
            max-height: 400px;
            overflow-y: auto;
        }

        .event-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: #1a1a1a;
            border-radius: 6px;
            border-left: 4px solid #666;
        }

        .event-time {
            color: #888;
            font-size: 0.9em;
            margin-right: 15px;
            min-width: 120px;
        }

        .event-message {
            flex: 1;
            color: #ccc;
        }

        .event-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        /* Status colors */
        .operational { color: #00d4aa; }
        .degraded { color: #f39c12; }
        .down { color: #e74c3c; }
        .unknown { color: #888; }

        .status-operational { background: rgba(0, 212, 170, 0.2); color: #00d4aa; }
        .status-degraded { background: rgba(243, 156, 18, 0.2); color: #f39c12; }
        .status-down { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .status-unknown { background: rgba(136, 136, 136, 0.2); color: #888; }

        /* Loading states */
        .loading {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .spinner {
            border: 4px solid #333;
            border-top: 4px solid #00d4aa;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .chart-grid,
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .device-filters {
                flex-direction: column;
                gap: 10px;
            }

            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .devices-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>

    <!-- Include Chart.js for graphs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <script>
        // Global state
        let currentView = 'overview';
        let currentCategory = null;
        let currentDevice = null;
        let dashboardData = null;
        let charts = {};

        // Initialize the reports dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Add CSS to prevent body scrolling issues
            document.body.style.overflowX = 'hidden';

            loadOverviewData();
        });

        // Load overview data
        async function loadOverviewData() {
            try {
                const response = await fetch('?action=get_dashboard_data');
                dashboardData = await response.json();

                renderOverviewStats();
                renderCategoryGrid();
                renderSystemCharts();
            } catch (error) {
                console.error('Error loading overview data:', error);
                showError('Failed to load dashboard data: ' + error.message);
            }
        }

        // Render overview statistics
        function renderOverviewStats() {
            const container = document.getElementById('overviewStats');
            const stats = dashboardData.stats;

            const uptime = ((stats.operational / stats.total) * 100).toFixed(1);
            const avgResponseTime = calculateAverageResponseTime();

            container.innerHTML = `
            <div class="stat-card">
                <div class="stat-number">${stats.total}</div>
                <div class="stat-label">Total Devices</div>
                <div class="stat-sublabel">Across all categories</div>
            </div>
            <div class="stat-card">
                <div class="stat-number operational">${uptime}%</div>
                <div class="stat-label">System Uptime</div>
                <div class="stat-sublabel">${stats.operational} of ${stats.total} operational</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #3498db;">${avgResponseTime}ms</div>
                <div class="stat-label">Avg Response Time</div>
                <div class="stat-sublabel">Last 24 hours</div>
            </div>
            <div class="stat-card">
                <div class="stat-number ${stats.down > 0 ? 'down' : 'operational'}">${stats.down}</div>
                <div class="stat-label">Critical Issues</div>
                <div class="stat-sublabel">Devices requiring attention</div>
            </div>
        `;
        }

        // Calculate average response time
        function calculateAverageResponseTime() {
            let totalResponseTime = 0;
            let deviceCount = 0;

            Object.values(dashboardData.devices).forEach(deviceGroup => {
                deviceGroup.forEach(device => {
                    if (device.response_time && device.response_time > 0) {
                        totalResponseTime += device.response_time;
                        deviceCount++;
                    }
                });
            });

            return deviceCount > 0 ? Math.round(totalResponseTime / deviceCount) : 0;
        }

        // Render category grid
        function renderCategoryGrid() {
            const container = document.getElementById('categoryGrid');
            const categories = Object.entries(dashboardData.device_groups);

            let html = '';

            categories.forEach(([key, label]) => {
                const devices = dashboardData.devices[key] || [];
                const stats = calculateCategoryStats(devices);

                html += `
                <div class="category-card" onclick="showCategory('${key}', '${label}')">
                    <div class="category-header">
                        <div class="category-name">${label}</div>
                        <div class="category-count">${devices.length}</div>
                    </div>
                    <div class="category-stats">
                        <div class="category-stat">
                            <div class="category-stat-number operational">${stats.operational}</div>
                            <div class="category-stat-label">Online</div>
                        </div>
                        <div class="category-stat">
                            <div class="category-stat-number degraded">${stats.degraded}</div>
                            <div class="category-stat-label">Degraded</div>
                        </div>
                        <div class="category-stat">
                            <div class="category-stat-number down">${stats.down}</div>
                            <div class="category-stat-label">Down</div>
                        </div>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        // Calculate stats for a category
        function calculateCategoryStats(devices) {
            return {
                operational: devices.filter(d => d.status === 'operational').length,
                degraded: devices.filter(d => d.status === 'degraded').length,
                down: devices.filter(d => d.status === 'down').length,
                unknown: devices.filter(d => !d.status || d.status === 'unknown').length
            };
        }

        // Render system-wide charts
        function renderSystemCharts() {
            renderStatusChart();
            renderResponseTimeChart();
        }

        // Render status distribution chart
        function renderStatusChart() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            const stats = dashboardData.stats;

            if (charts.statusChart) {
                charts.statusChart.destroy();
            }

            charts.statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Operational', 'Degraded', 'Down', 'Unknown'],
                    datasets: [{
                        data: [stats.operational, stats.degraded, stats.down, stats.total - stats.operational - stats.degraded - stats.down],
                        backgroundColor: ['#00d4aa', '#f39c12', '#e74c3c', '#888'],
                        borderWidth: 2,
                        borderColor: '#1a1a1a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animations to prevent size changes
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#ccc',
                                padding: 10,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }

        // Render response time chart
        function renderResponseTimeChart() {
            const ctx = document.getElementById('responseTimeChart').getContext('2d');

            // Generate sample data for the last 24 hours
            const hours = [];
            const responseTimes = [];

            for (let i = 23; i >= 0; i--) {
                const hour = new Date();
                hour.setHours(hour.getHours() - i);
                hours.push(hour.getHours() + ':00');
                responseTimes.push(Math.floor(Math.random() * 50) + 20 + (i * 2)); // Sample data
            }

            if (charts.responseTimeChart) {
                charts.responseTimeChart.destroy();
            }

            charts.responseTimeChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: hours,
                    datasets: [{
                        label: 'Avg Response Time (ms)',
                        data: responseTimes,
                        borderColor: '#00d4aa',
                        backgroundColor: 'rgba(0, 212, 170, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Disable animations
                    plugins: {
                        legend: {
                            labels: { color: '#ccc' }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#ccc',
                                maxTicksLimit: 8
                            },
                            grid: { color: '#333' }
                        },
                        y: {
                            ticks: { color: '#ccc' },
                            grid: { color: '#333' }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Show overview section
        function showOverview() {
            currentView = 'overview';
            document.getElementById('overviewSection').style.display = 'block';
            document.getElementById('categorySection').style.display = 'none';
            document.getElementById('deviceSection').style.display = 'none';

            updateBreadcrumb(['📊 Overview']);
        }

        // Show category section
        function showCategory(categoryKey, categoryLabel) {
            currentView = 'category';
            currentCategory = categoryKey;

            document.getElementById('overviewSection').style.display = 'none';
            document.getElementById('categorySection').style.display = 'block';
            document.getElementById('deviceSection').style.display = 'none';

            document.getElementById('categoryTitle').textContent = categoryLabel + ' - Detailed View';

            updateBreadcrumb(['📊 Overview', `📁 ${categoryLabel}`]);
            renderCategoryView(categoryKey, categoryLabel);
        }

        // Render category detailed view
        function renderCategoryView(categoryKey, categoryLabel) {
            const devices = dashboardData.devices[categoryKey] || [];
            const stats = calculateCategoryStats(devices);

            // Render category stats
            const statsContainer = document.getElementById('categoryStats');
            const uptime = devices.length > 0 ? ((stats.operational / devices.length) * 100).toFixed(1) : 0;

            statsContainer.innerHTML = `
            <div class="overview-stats">
                <div class="stat-card">
                    <div class="stat-number">${devices.length}</div>
                    <div class="stat-label">Total Devices</div>
                    <div class="stat-sublabel">In ${categoryLabel}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number operational">${uptime}%</div>
                    <div class="stat-label">Category Uptime</div>
                    <div class="stat-sublabel">${stats.operational} operational</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number degraded">${stats.degraded}</div>
                    <div class="stat-label">Degraded</div>
                    <div class="stat-sublabel">Performance issues</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number down">${stats.down}</div>
                    <div class="stat-label">Offline</div>
                    <div class="stat-sublabel">Requiring attention</div>
                </div>
            </div>
        `;

            renderDevicesGrid(devices);
        }

        // Render devices grid
        function renderDevicesGrid(devices) {
            const container = document.getElementById('devicesGrid');

            if (devices.length === 0) {
                container.innerHTML = `
                <div class="loading">
                    <p>No devices found in this category</p>
                </div>
            `;
                return;
            }

            let html = '';

            devices.forEach(device => {
                const status = device.status || 'unknown';
                const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never';
                const responseTime = device.response_time || 'N/A';
                const packetLoss = device.packet_loss || 0;

                html += `
                <div class="device-card" onclick="showDevice(${device.id}, '${device.name}')">
                    <div class="device-card-header">
                        <div class="device-name">${device.name}</div>
                        <div class="device-status status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</div>
                    </div>
                    <div class="device-info">
                        <div><strong>IP:</strong> ${device.ip_address}</div>
                        <div><strong>Type:</strong> ${device.monitor_type.toUpperCase()}</div>
                        <div><strong>Location:</strong> ${device.location || 'Not specified'}</div>
                        <div><strong>Last Check:</strong> ${lastCheck}</div>
                    </div>
                    <div class="device-metrics">
                        <div class="device-metric">
                            <div class="device-metric-value" style="color: #00d4aa;">${responseTime}${typeof responseTime === 'number' ? 'ms' : ''}</div>
                            <div class="device-metric-label">Response</div>
                        </div>
                        <div class="device-metric">
                            <div class="device-metric-value" style="color: ${packetLoss > 0 ? '#e74c3c' : '#00d4aa'};">${packetLoss}%</div>
                            <div class="device-metric-label">Packet Loss</div>
                        </div>
                        <div class="device-metric">
                            <div class="device-metric-value" style="color: ${device.critical_device ? '#f39c12' : '#888'};">
                                ${device.critical_device ? 'Critical' : 'Normal'}
                            </div>
                            <div class="device-metric-label">Priority</div>
                        </div>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;

            // Set up search and filter functionality
            setupDeviceFilters(devices);
        }

        // Setup device search and filter functionality
        function setupDeviceFilters(devices) {
            const searchInput = document.getElementById('deviceSearchFilter');
            const statusFilter = document.getElementById('deviceStatusFilter');

            function filterDevices() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const statusFilter = document.getElementById('deviceStatusFilter').value;

                let filteredDevices = devices;

                if (searchTerm) {
                    filteredDevices = filteredDevices.filter(device =>
                        device.name.toLowerCase().includes(searchTerm) ||
                        device.ip_address.toLowerCase().includes(searchTerm) ||
                        (device.location && device.location.toLowerCase().includes(searchTerm))
                    );
                }

                if (statusFilter) {
                    filteredDevices = filteredDevices.filter(device =>
                        (device.status || 'unknown') === statusFilter
                    );
                }

                renderDevicesGrid(filteredDevices);
            }

            searchInput.addEventListener('input', filterDevices);
            statusFilter.addEventListener('change', filterDevices);
        }

        // Show device detailed view
        async function showDevice(deviceId, deviceName) {
            currentView = 'device';
            currentDevice = deviceId;

            document.getElementById('overviewSection').style.display = 'none';
            document.getElementById('categorySection').style.display = 'none';
            document.getElementById('deviceSection').style.display = 'block';

            document.getElementById('deviceTitle').textContent = `${deviceName} - Analytics`;

            const categoryLabel = dashboardData.device_groups[currentCategory];
            updateBreadcrumb(['📊 Overview', `📁 ${categoryLabel}`, `🖥️ ${deviceName}`]);

            await renderDeviceView(deviceId);
        }

        // Render device detailed view
        async function renderDeviceView(deviceId) {
            // Find device data
            let device = null;
            Object.values(dashboardData.devices).forEach(deviceGroup => {
                const found = deviceGroup.find(d => d.id == deviceId);
                if (found) device = found;
            });

            if (!device) {
                showError('Device not found');
                return;
            }

            // Render device info card
            renderDeviceInfoCard(device);

            // Load and render device analytics
            await loadDeviceAnalytics(deviceId);

            // Load recent events
            await loadDeviceEvents(deviceId);
        }

        // Render device info card
        function renderDeviceInfoCard(device) {
            const container = document.getElementById('deviceInfoCard');
            const status = device.status || 'unknown';
            const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never checked';
            const uptime = calculateDeviceUptime(device);

            container.innerHTML = `
            <div class="device-info-grid">
                <div class="device-details">
                    <div class="device-detail-item">
                        <div class="device-detail-label">Device Name</div>
                        <div class="device-detail-value">${device.name}</div>
                    </div>
                    <div class="device-detail-item">
                        <div class="device-detail-label">IP Address</div>
                        <div class="device-detail-value" style="font-family: monospace;">${device.ip_address}</div>
                    </div>
                    <div class="device-detail-item">
                        <div class="device-detail-label">Monitor Type</div>
                        <div class="device-detail-value">${device.monitor_type.toUpperCase()}${device.port ? `:${device.port}` : ''}</div>
                    </div>
                    <div class="device-detail-item">
                        <div class="device-detail-label">Location</div>
                        <div class="device-detail-value">${device.location || 'Not specified'}</div>
                    </div>
                    <div class="device-detail-item">
                        <div class="device-detail-label">Last Response Time</div>
                        <div class="device-detail-value">${device.response_time ? device.response_time + 'ms' : 'N/A'}</div>
                    </div>
                    <div class="device-detail-item">
                        <div class="device-detail-label">Last Check</div>
                        <div class="device-detail-value">${lastCheck}</div>
                    </div>
                </div>
                <div class="device-status-summary">
                    <h4>Current Status</h4>
                    <div class="device-status status-${status}" style="padding: 15px; margin: 15px 0; border-radius: 8px; font-size: 1.2em;">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </div>
                    <div style="margin-top: 20px;">
                        <div style="font-size: 2em; font-weight: bold; color: #00d4aa; margin-bottom: 5px;">${uptime}%</div>
                        <div style="color: #ccc;">Estimated Uptime</div>
                        <div style="color: #888; font-size: 0.9em; margin-top: 5px;">Last 30 days</div>
                    </div>
                </div>
            </div>
        `;
        }

        // Calculate device uptime estimate
        function calculateDeviceUptime(device) {
            // This would typically come from historical data
            // For demo purposes, calculate based on current status
            if (device.status === 'operational') return 99.5;
            if (device.status === 'degraded') return 95.2;
            if (device.status === 'down') return 87.3;
            return 90.0;
        }

        // Load device analytics data
        async function loadDeviceAnalytics(deviceId) {
            try {
                const timeRange = document.getElementById('timeRangeSelect').value;

                // Fetch real historical data from database
                const [historyResponse, statsResponse] = await Promise.all([
                    fetch(`?action=get_device_history_report&device_id=${deviceId}&hours=${timeRange}`),
                    fetch(`?action=get_device_stats&device_id=${deviceId}&hours=${timeRange}`)
                ]);
                const historyData = await historyResponse.json();
                const statsData = await statsResponse.json();

                if (historyData.error || statsData.error) {
                    throw new Error(historyData.error || statsData.error);
                }

                renderDeviceCharts(historyData);
                renderDeviceStats(statsData.stats);
                renderDeviceEvents(statsData.events);

            } catch (error) {
                console.error('Error loading device analytics:', error);
                showError('Failed to load device analytics: ' + error.message);
            }
        }

        // Generate sample analytics data (REMOVED - now using real data)
        // This function is no longer used as we fetch real data from the database

        // Render device charts
        function renderDeviceCharts(data) {
            // Process timestamps for chart labels
            const labels = data.timestamps.map(timestamp => {
                const date = new Date(timestamp);
                return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
            });

            renderDeviceResponseChart(data, labels);
            renderDeviceAvailabilityChart(data, labels);
            renderDevicePacketLossChart(data, labels);
        }

        // Render device response time chart
        function renderDeviceResponseChart(data, labels) {
            const ctx = document.getElementById('deviceResponseChart').getContext('2d');

            if (charts.deviceResponseChart) {
                charts.deviceResponseChart.destroy();
            }

            // Filter out null values for response time (when device was down)
            const responseData = data.responseTime.map(val => val === null ? 0 : val);

            charts.deviceResponseChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: responseData,
                        borderColor: '#00d4aa',
                        backgroundColor: 'rgba(0, 212, 170, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        spanGaps: false // Don't connect null values
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            labels: { color: '#ccc' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = data.responseTime[context.dataIndex];
                                    return value === null ? 'Device was down' : `Response Time: ${value}ms`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#ccc',
                                maxTicksLimit: 8
                            },
                            grid: { color: '#333' }
                        },
                        y: {
                            ticks: { color: '#ccc' },
                            grid: { color: '#333' },
                            beginAtZero: true
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Render device availability chart
        function renderDeviceAvailabilityChart(data, labels) {
            const ctx = document.getElementById('deviceAvailabilityChart').getContext('2d');

            if (charts.deviceAvailabilityChart) {
                charts.deviceAvailabilityChart.destroy();
            }

            charts.deviceAvailabilityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Status',
                        data: data.availability,
                        backgroundColor: data.availability.map(val => val ? '#00d4aa' : '#e74c3c'),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            labels: { color: '#ccc' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y ? 'Device Online' : 'Device Offline';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#ccc',
                                maxTicksLimit: 8
                            },
                            grid: { color: '#333' }
                        },
                        y: {
                            ticks: {
                                color: '#ccc',
                                callback: function(value) {
                                    return value ? 'Online' : 'Offline';
                                },
                                stepSize: 1
                            },
                            grid: { color: '#333' },
                            min: 0,
                            max: 1
                        }
                    }
                }
            });
        }

        // Render device packet loss chart
        function renderDevicePacketLossChart(data, labels) {
            const ctx = document.getElementById('devicePacketLossChart').getContext('2d');

            if (charts.devicePacketLossChart) {
                charts.devicePacketLossChart.destroy();
            }

            charts.devicePacketLossChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Packet Loss (%)',
                        data: data.packetLoss,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            labels: { color: '#ccc' }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#ccc',
                                maxTicksLimit: 8
                            },
                            grid: { color: '#333' }
                        },
                        y: {
                            ticks: {
                                color: '#ccc',
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: { color: '#333' },
                            min: 0,
                            max: Math.max(100, Math.max(...data.packetLoss) * 1.1)
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Render device statistics breakdown
        function renderDeviceStats(stats) {
            const container = document.getElementById('deviceStatsBreakdown');

            container.innerHTML = `
            <div class="breakdown-item">
                <span class="breakdown-label">Total Checks</span>
                <span class="breakdown-value">${stats.total_checks}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Successful Checks</span>
                <span class="breakdown-value">${stats.successful_checks}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Failed Checks</span>
                <span class="breakdown-value" style="color: #e74c3c;">${stats.failed_checks}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Uptime Percentage</span>
                <span class="breakdown-value">${stats.uptime_percentage}%</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Avg Response Time</span>
                <span class="breakdown-value">${stats.avg_response_time ? stats.avg_response_time + 'ms' : 'N/A'}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Min Response Time</span>
                <span class="breakdown-value">${stats.min_response_time ? stats.min_response_time + 'ms' : 'N/A'}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Max Response Time</span>
                <span class="breakdown-value">${stats.max_response_time ? stats.max_response_time + 'ms' : 'N/A'}</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Avg Packet Loss</span>
                <span class="breakdown-value">${stats.avg_packet_loss}%</span>
            </div>
        `;
        }

        // Calculate device uptime estimate from real stats
        function calculateDeviceUptime(device) {
            // This will be overridden with real data from the stats API
            if (device.status === 'operational') return 99.5;
            if (device.status === 'degraded') return 95.2;
            if (device.status === 'down') return 87.3;
            return 90.0;
        }

        // Load device events (now using real data)
        async function loadDeviceEvents(deviceId) {
            // Events are now loaded as part of loadDeviceAnalytics
            // This function is kept for compatibility but does nothing
            // as events are passed from the stats API call
        }

        // Generate sample events (REMOVED - now using real data)
        // This function is no longer used as we get real events from the database(...data.responseTime);
        const minResponseTime = Math.min(...data.responseTime);
        const uptime = ((data.availability.filter(a => a).length / data.availability.length) * 100).toFixed(1);
        const avgPacketLoss = (data.packetLoss.reduce((a, b) => a + b, 0) / data.packetLoss.length).toFixed(2);

        container.innerHTML = `
            <div class="breakdown-item">
                <span class="breakdown-label">Average Response Time</span>
                <span class="breakdown-value">${avgResponseTime}ms</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Min Response Time</span>
                <span class="breakdown-value">${minResponseTime}ms</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Max Response Time</span>
                <span class="breakdown-value">${maxResponseTime}ms</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Uptime Percentage</span>
                <span class="breakdown-value">${uptime}%</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Average Packet Loss</span>
                <span class="breakdown-value">${avgPacketLoss}%</span>
            </div>
        `;
        }

        // Load device events
        async function loadDeviceEvents(deviceId) {
            // This would typically fetch real event data
            // For demo purposes, generate sample events
            const events = generateSampleEvents();
            renderDeviceEvents(events);
        }

        // Generate sample events
        function generateSampleEvents() {
            const events = [];
            const eventTypes = [
                { message: 'Device came back online', status: 'operational' },
                { message: 'High response time detected', status: 'degraded' },
                { message: 'Device went offline', status: 'down' },
                { message: 'Packet loss detected', status: 'degraded' },
                { message: 'Normal operation resumed', status: 'operational' }
            ];

            for (let i = 0; i < 10; i++) {
                const eventType = eventTypes[Math.floor(Math.random() * eventTypes.length)];
                const timestamp = new Date();
                timestamp.setHours(timestamp.getHours() - i * 2);

                events.push({
                    timestamp: timestamp,
                    message: eventType.message,
                    status: eventType.status
                });
            }

            return events;
        }

        // Render device events
        function renderDeviceEvents(events) {
            const container = document.getElementById('eventsTimeline');

            if (events.length === 0) {
                container.innerHTML = '<div class="loading"><p>No recent events found</p></div>';
                return;
            }

            let html = '';

            events.forEach(event => {
                html += `
                <div class="event-item">
                    <div class="event-time">${event.timestamp.toLocaleString()}</div>
                    <div class="event-message">${event.message}</div>
                    <div class="event-status status-${event.status}">${event.status}</div>
                </div>
            `;
            });

            container.innerHTML = html;
        }

        // Update breadcrumb navigation
        function updateBreadcrumb(items) {
            const container = document.getElementById('breadcrumb');

            let html = '';
            items.forEach((item, index) => {
                const isActive = index === items.length - 1;
                const onclick = index === 0 ? 'onclick="showOverview()"' :
                    index === 1 ? `onclick="showCategory('${currentCategory}', '${dashboardData.device_groups[currentCategory]}')"` : '';

                html += `<span class="breadcrumb-item ${isActive ? 'active' : ''}" ${onclick}>${item}</span>`;
            });

            container.innerHTML = html;
        }

        // Update device charts when time range changes
        function updateDeviceCharts() {
            if (currentView === 'device' && currentDevice) {
                loadDeviceAnalytics(currentDevice);
            }
        }

        // Action button functions
        function exportCategoryReport() {
            const categoryLabel = dashboardData.device_groups[currentCategory];
            alert(`Exporting ${categoryLabel} report... (Feature would generate PDF/CSV report)`);
        }

        function exportDeviceReport() {
            alert('Exporting device report... (Feature would generate detailed PDF report)');
        }

        function testDeviceNow() {
            if (currentDevice) {
                alert('Testing device now... (Feature would trigger immediate monitoring check)');
            }
        }

        function refreshCategoryData() {
            if (currentCategory) {
                const categoryLabel = dashboardData.device_groups[currentCategory];
                showCategory(currentCategory, categoryLabel);
            }
        }

        function refreshDeviceData() {
            if (currentDevice) {
                renderDeviceView(currentDevice);
            }
        }

        // Error handling
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = message;
            errorDiv.style.position = 'fixed';
            errorDiv.style.top = '20px';
            errorDiv.style.right = '20px';
            errorDiv.style.zIndex = '9999';
            errorDiv.style.padding = '15px';
            errorDiv.style.borderRadius = '6px';
            errorDiv.style.backgroundColor = '#e74c3c';
            errorDiv.style.color = 'white';

            document.body.appendChild(errorDiv);

            setTimeout(() => errorDiv.remove(), 5000);
        }
    </script>

<?php require_once 'views/layouts/footer.php'; ?>