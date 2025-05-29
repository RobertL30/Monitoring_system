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
            height: 300px;
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

        // Debug function to test API endpoints
        async function debugAPIEndpoints() {
            console.log('=== DEBUGGING API ENDPOINTS ===');

            // Test 1: Check if get_dashboard_data works
            try {
                console.log('Testing get_dashboard_data...');
                const response = await fetch('?action=get_dashboard_data');
                console.log('Dashboard response status:', response.status);
                const data = await response.json();
                console.log('Dashboard data:', data);

                if (data.error) {
                    console.error('Dashboard data error:', data.error);
                } else {
                    console.log('✓ Dashboard data loaded successfully');
                    console.log('Device count:', data.stats.total);
                    console.log('Device groups:', Object.keys(data.device_groups));
                }
            } catch (error) {
                console.error('✗ Dashboard data failed:', error);
            }

            // Test 2: Check if get_system_overview works
            try {
                console.log('Testing get_system_overview...');
                const response = await fetch('?action=get_system_overview');
                console.log('System overview response status:', response.status);
                const data = await response.json();
                console.log('System overview data:', data);

                if (data.error) {
                    console.error('System overview error:', data.error);
                } else {
                    console.log('✓ System overview loaded successfully');
                }
            } catch (error) {
                console.error('✗ System overview failed:', error);
            }

            // Test 3: Check if Chart.js is loaded
            console.log('Chart.js available:', typeof Chart !== 'undefined');
            if (typeof Chart === 'undefined') {
                console.error('✗ Chart.js is not loaded!');
            } else {
                console.log('✓ Chart.js version:', Chart.version);
            }
        }

        // Load overview data with debugging
        async function loadOverviewData() {
            console.log('loadOverviewData() called');

            try {
                console.log('Fetching dashboard data...');
                const response = await fetch('?action=get_dashboard_data');
                console.log('Response received:', response.status, response.statusText);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const responseText = await response.text();
                console.log('Raw response length:', responseText.length);

                // Try to parse JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', responseText.substring(0, 500) + '...');
                    throw new Error('Invalid JSON response from server');
                }

                console.log('Parsed dashboard data:', data);

                if (data.error) {
                    throw new Error(data.error);
                }

                // Store globally
                dashboardData = data;

                console.log('Rendering overview components...');
                renderOverviewStats();
                renderCategoryGrid();
                renderSystemCharts();

                console.log('✓ Overview data loaded successfully');

            } catch (error) {
                console.error('✗ Error loading overview data:', error);
                showError('Failed to load dashboard data: ' + error.message);

                // Show detailed error in the UI
                document.getElementById('overviewStats').innerHTML = `
                    <div style="color: #e74c3c; padding: 20px; background: #2a1a1a; border-radius: 8px;">
                        <h4>Error Loading Data</h4>
                        <p><strong>Error:</strong> ${error.message}</p>
                        <p><strong>Troubleshooting:</strong></p>
                        <ul>
                            <li>Open browser console (F12) for detailed logs</li>
                            <li>Click the Debug API button to test endpoints</li>
                            <li>Check if you're logged in</li>
                            <li>Verify database connection</li>
                        </ul>
                        <button onclick="debugAPIEndpoints()" class="btn btn-secondary" style="margin-top: 10px;">🐛 Run Debug Tests</button>
                        <button onclick="loadOverviewData()" class="btn" style="margin-top: 10px; margin-left: 10px;">🔄 Retry</button>
                    </div>
                `;

                document.getElementById('categoryGrid').innerHTML = `
                    <div style="color: #e74c3c; text-align: center; padding: 20px;">
                        <p>Unable to load categories due to data loading error.</p>
                    </div>
                `;
            }
        }

        // Render overview statistics
        function renderOverviewStats() {
            const container = document.getElementById('overviewStats');
            const stats = dashboardData.stats;

            const uptime = stats.total > 0 ? ((stats.operational / stats.total) * 100).toFixed(1) : 0;
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
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded, skipping charts');
                return;
            }
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
                        data: [stats.operational, stats.degraded, stats.down,
                            Math.max(0, stats.total - stats.operational - stats.degraded - stats.down)],
                        backgroundColor: ['#00d4aa', '#f39c12', '#e74c3c', '#888'],
                        borderWidth: 2,
                        borderColor: '#1a1a1a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
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
                responseTimes.push(Math.floor(Math.random() * 50) + 20);
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
                            ticks: { color: '#ccc' },
                            grid: { color: '#333' }
                        }
                    }
                }
            });
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

        // Add debug button to the page
        function addDebugButton() {
            const debugButton = document.createElement('button');
            debugButton.textContent = '🐛 Debug API';
            debugButton.className = 'btn btn-secondary';
            debugButton.style.position = 'fixed';
            debugButton.style.top = '80px';
            debugButton.style.right = '20px';
            debugButton.style.zIndex = '9999';
            debugButton.onclick = debugAPIEndpoints;
            document.body.appendChild(debugButton);
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Reports page loaded');
            console.log('Current URL:', window.location.href);
            console.log('Chart.js available:', typeof Chart !== 'undefined');

            addDebugButton();
            loadOverviewData();
        });

        // Placeholder functions for advanced features (to prevent errors if called)
        function showOverview() { location.reload(); }
        function showCategory() { alert('Category drill-down feature coming soon!'); }
        function showDevice() { alert('Device analytics feature coming soon!'); }
        function exportCategoryReport() { alert('Export feature coming soon!'); }
        function exportDeviceReport() { alert('Export feature coming soon!'); }
        function testDeviceNow() { alert('Test device feature coming soon!'); }
        function refreshCategoryData() { location.reload(); }
        function refreshDeviceData() { location.reload(); }
        function updateDeviceCharts() { console.log('Update charts called'); }
    </script>

<?php require_once 'views/layouts/footer.php'; ?>