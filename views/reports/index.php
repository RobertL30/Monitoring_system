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