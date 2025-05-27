<?php require_once 'views/layouts/header.php'; ?>

    <div class="container">
        <div class="controls">
            <div>
                <h2>Network Status Dashboard</h2>
                <p style="color: #ccc;">Welcome back, <?= $_SESSION['username'] ?></p>
                <p id="lastUpdateTime" style="color: #888; font-size: 0.9em;"></p>
            </div>
            <div>
                <button class="btn" onclick="startManualMonitoring()" id="monitorBtn">🔄 Check All Systems</button>
                <button class="btn btn-secondary" onclick="loadDashboard()">↻ Refresh Dashboard</button>
                <label style="color: #ccc; margin-left: 20px;">
                    <input type="checkbox" id="autoMonitor" checked> Auto-monitor every 5 minutes
                </label>
            </div>
        </div>

        <!-- Progress Bar -->
        <div id="progressContainer" style="display: none; margin-bottom: 20px;">
            <div style="background: #2a2a2a; padding: 15px; border-radius: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span id="progressText">Preparing to check devices...</span>
                    <span id="progressPercent">0%</span>
                </div>
                <div style="background: #444; height: 20px; border-radius: 10px; overflow: hidden;">
                    <div id="progressBar" style="background: linear-gradient(90deg, #00d4aa, #00b894); height: 100%; width: 0%; transition: width 0.3s;"></div>
                </div>
            </div>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading system status...</p>
        </div>

        <div id="content"></div>

        <!-- Enhanced Device List with Search and Category Filtering -->
        <div id="deviceListContainer" style="display: none; margin-top: 30px;">
            <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #00d4aa;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 id="deviceListTitle">All Devices</h3>
                    <button class="btn btn-secondary" onclick="hideDeviceList()" style="padding: 8px 16px; font-size: 14px;">✕ Close</button>
                </div>

                <!-- Search and Filter Controls -->
                <div class="device-filters" style="margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 200px 150px; gap: 15px; align-items: end;">
                        <!-- Search Bar -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Search Devices</label>
                            <input type="text" id="deviceSearch" class="form-input" placeholder="Search by name, IP, or location..."
                                   oninput="applyDeviceFilters()" style="background: #1a1a1a;">
                        </div>

                        <!-- Category Filter -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Category</label>
                            <select id="categoryFilter" class="form-select" onchange="applyDeviceFilters()" style="background: #1a1a1a;">
                                <option value="">All Categories</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <div>
                            <button class="btn btn-secondary" onclick="clearDeviceFilters()" style="padding: 8px 16px; font-size: 14px;">
                                🔄 Clear
                            </button>
                        </div>
                    </div>

                    <!-- Filter Status Display -->
                    <div id="filterStatus" style="margin-top: 10px; padding: 10px; background: #1a1a1a; border-radius: 6px; font-size: 0.9em; color: #888; display: none;">
                        <span id="filterStatusText"></span>
                    </div>
                </div>

                <div id="deviceListContent"></div>
            </div>
        </div>

        <!-- Live Monitoring Results -->
        <div id="monitoringResults" style="display: none; margin-top: 30px;">
            <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; border-left: 4px solid #00d4aa;">
                <h3>Live Monitoring Results</h3>
                <div id="resultsContainer"></div>
            </div>
        </div>
    </div>

    <style>
        /* Enhanced CSS for device filtering and search */
        .device-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #444;
            transition: opacity 0.3s, background-color 0.3s;
        }

        .device-item:last-child {
            border-bottom: none;
        }

        .device-item.filtered-out {
            display: none;
        }

        .device-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
            border-radius: 6px;
            margin: 0 -10px;
            padding-left: 25px;
            padding-right: 25px;
        }

        .device-info {
            flex: 1;
        }

        .device-name {
            font-weight: bold;
            margin-bottom: 6px;
            font-size: 1.1em;
        }

        .device-details {
            font-size: 0.9em;
            color: #aaa;
            line-height: 1.4;
        }

        .device-ip {
            color: #00d4aa;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }

        .device-category {
            color: #f39c12;
            font-weight: 500;
        }

        .device-status {
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.9em;
            text-align: center;
            min-width: 80px;
        }

        .status-operational {
            color: #00d4aa;
            background: rgba(0, 212, 170, 0.15);
            border: 1px solid rgba(0, 212, 170, 0.3);
        }

        .status-degraded {
            color: #f39c12;
            background: rgba(243, 156, 18, 0.15);
            border: 1px solid rgba(243, 156, 18, 0.3);
        }

        .status-down {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .status-unknown {
            color: #888;
            background: rgba(136, 136, 136, 0.15);
            border: 1px solid rgba(136, 136, 136, 0.3);
        }

        .stats-grid .stat-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stats-grid .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .stats-grid .stat-card.active {
            border-left: 4px solid #f39c12;
            background: #333;
        }

        /* Search highlight */
        .search-highlight {
            background-color: rgba(0, 212, 170, 0.3);
            padding: 2px 4px;
            border-radius: 3px;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }

        .empty-state h4 {
            color: #ccc;
            margin-bottom: 10px;
        }

        /* Filter controls styling */
        .device-filters .form-input:focus,
        .device-filters .form-select:focus {
            border-color: #00d4aa;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.1);
        }
    </style>

    <script>
        // Global variables
        let autoMonitorInterval;
        let isMonitoring = false;
        let totalDevices = 0;
        let checkedDevices = 0;
        let currentFilter = 'all';
        let dashboardData = null;
        let allDevices = []; // Store all devices for filtering
        let filteredDevices = []; // Store currently filtered devices

        // Load dashboard data
        async function loadDashboard() {
            try {
                const response = await fetch('?action=get_dashboard_data');
                const data = await response.json();
                dashboardData = data; // Store for filtering

                // Prepare all devices array for filtering
                allDevices = [];
                Object.entries(data.devices).forEach(([groupKey, devices]) => {
                    devices.forEach(device => {
                        device.group_name = data.device_groups[device.device_group] || device.device_group;
                        device.group_key = device.device_group;
                        allDevices.push(device);
                    });
                });

                document.getElementById('loading').style.display = 'none';

                let html = `
            <div class="stats-grid">
                <div class="stat-card" onclick="filterDevices('all')" id="card-all">
                    <div class="stat-number">${data.stats.total}</div>
                    <div class="stat-label">Total Systems</div>
                </div>
                <div class="stat-card" onclick="filterDevices('operational')" id="card-operational">
                    <div class="stat-number operational">${data.stats.operational}</div>
                    <div class="stat-label">Operational</div>
                </div>
                <div class="stat-card" onclick="filterDevices('degraded')" id="card-degraded">
                    <div class="stat-number degraded">${data.stats.degraded}</div>
                    <div class="stat-label">Degraded</div>
                </div>
                <div class="stat-card" onclick="filterDevices('down')" id="card-down">
                    <div class="stat-number down">${data.stats.down}</div>
                    <div class="stat-label">Down/Issues</div>
                </div>
            </div>
        `;

                document.getElementById('content').innerHTML = html;
                document.getElementById('lastUpdateTime').textContent =
                    'Dashboard updated: ' + new Date().toLocaleString();

                // Update category filter options
                updateCategoryFilter(data.device_groups);

                // Update active filter indicator
                updateFilterIndicator();

                return data;

            } catch (error) {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('content').innerHTML =
                    '<p style="color: red; text-align: center;">Error loading dashboard: ' + error.message + '</p>';
            }
        }

        // Update category filter dropdown
        function updateCategoryFilter(deviceGroups) {
            const categoryFilter = document.getElementById('categoryFilter');
            const currentValue = categoryFilter.value;

            // Clear existing options except "All Categories"
            categoryFilter.innerHTML = '<option value="">All Categories</option>';

            // Add category options
            Object.entries(deviceGroups).forEach(([key, label]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = label;
                categoryFilter.appendChild(option);
            });

            // Restore selection if it still exists
            categoryFilter.value = currentValue;
        }

        // Filter devices by status (from stat cards)
        function filterDevices(status) {
            if (!dashboardData) {
                alert('Dashboard data not loaded yet. Please wait...');
                return;
            }

            currentFilter = status;
            updateFilterIndicator();

            // Filter devices based on status
            filteredDevices = [...allDevices];
            let title = 'All Devices';

            if (status !== 'all') {
                filteredDevices = allDevices.filter(device => {
                    const deviceStatus = device.status || 'unknown';
                    return deviceStatus === status;
                });
                title = status.charAt(0).toUpperCase() + status.slice(1) + ' Devices';
            }

            // Clear search and category filters when clicking stat cards
            document.getElementById('deviceSearch').value = '';
            document.getElementById('categoryFilter').value = '';

            // Show filtered device list
            showDeviceList(filteredDevices, title);
        }

        // Apply search and category filters
        function applyDeviceFilters() {
            if (!dashboardData || allDevices.length === 0) return;

            const searchTerm = document.getElementById('deviceSearch').value.toLowerCase().trim();
            const selectedCategory = document.getElementById('categoryFilter').value;

            // Start with all devices or current status filter
            let devices = [...allDevices];

            // Apply status filter if one is active (from stat cards)
            if (currentFilter !== 'all') {
                devices = devices.filter(device => {
                    const deviceStatus = device.status || 'unknown';
                    return deviceStatus === currentFilter;
                });
            }

            // Apply category filter
            if (selectedCategory) {
                devices = devices.filter(device => device.group_key === selectedCategory);
            }

            // Apply search filter
            if (searchTerm) {
                devices = devices.filter(device => {
                    const searchFields = [
                        device.name,
                        device.ip_address,
                        device.location || '',
                        device.group_name,
                        device.description || ''
                    ].join(' ').toLowerCase();

                    return searchFields.includes(searchTerm);
                });
            }

            filteredDevices = devices;

            // Update display
            updateDeviceListDisplay();
            updateFilterStatus(searchTerm, selectedCategory);
        }

        // Clear all filters
        function clearDeviceFilters() {
            document.getElementById('deviceSearch').value = '';
            document.getElementById('categoryFilter').value = '';

            // Reset to current status filter only
            applyDeviceFilters();
        }

        // Update filter status display
        function updateFilterStatus(searchTerm, selectedCategory) {
            const statusDiv = document.getElementById('filterStatus');
            const statusText = document.getElementById('filterStatusText');

            const filters = [];

            if (currentFilter !== 'all') {
                filters.push(`Status: ${currentFilter}`);
            }

            if (selectedCategory) {
                const categoryName = dashboardData.device_groups[selectedCategory] || selectedCategory;
                filters.push(`Category: ${categoryName}`);
            }

            if (searchTerm) {
                filters.push(`Search: "${searchTerm}"`);
            }

            if (filters.length > 0) {
                statusText.innerHTML = `
                <strong>Active filters:</strong> ${filters.join(', ')}
                <span style="margin-left: 15px; color: #00d4aa;">
                    Showing ${filteredDevices.length} of ${allDevices.length} devices
                </span>
            `;
                statusDiv.style.display = 'block';
            } else {
                statusDiv.style.display = 'none';
            }
        }

        // Update device list display with current filters
        function updateDeviceListDisplay() {
            const container = document.getElementById('deviceListContainer');
            const titleElement = document.getElementById('deviceListTitle');
            const contentElement = document.getElementById('deviceListContent');

            if (!container.style.display || container.style.display === 'none') {
                return; // Don't update if not visible
            }

            // Update title
            let title = 'All Devices';
            if (currentFilter !== 'all') {
                title = currentFilter.charAt(0).toUpperCase() + currentFilter.slice(1) + ' Devices';
            }
            titleElement.textContent = `${title} (${filteredDevices.length})`;

            // Update content
            if (filteredDevices.length === 0) {
                const searchTerm = document.getElementById('deviceSearch').value;
                const selectedCategory = document.getElementById('categoryFilter').value;

                let emptyMessage = 'No devices found';
                if (searchTerm || selectedCategory) {
                    emptyMessage = 'No devices match your current filters';
                }

                contentElement.innerHTML = `
                <div class="empty-state">
                    <h4>${emptyMessage}</h4>
                    <p>Try adjusting your search terms or filters</p>
                </div>
            `;
            } else {
                let html = '';

                filteredDevices.forEach(device => {
                    const status = device.status || 'unknown';
                    const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never checked';
                    const responseTime = device.response_time ? ` (${device.response_time}ms)` : '';
                    const location = device.location ? ` • ${device.location}` : '';
                    const port = device.port ? `:${device.port}` : '';

                    // Highlight search terms
                    const searchTerm = document.getElementById('deviceSearch').value.toLowerCase().trim();
                    let deviceName = device.name;
                    let deviceIP = device.ip_address;
                    let deviceLocation = device.location || '';

                    if (searchTerm) {
                        deviceName = highlightSearchTerm(device.name, searchTerm);
                        deviceIP = highlightSearchTerm(device.ip_address, searchTerm);
                        deviceLocation = device.location ? highlightSearchTerm(device.location, searchTerm) : '';
                    }

                    html += `
                    <div class="device-item">
                        <div class="device-info">
                            <div class="device-name">${deviceName}</div>
                            <div class="device-details">
                                <span class="device-ip">${deviceIP}${port}</span> •
                                <span class="device-category">${device.group_name}</span> •
                                ${device.monitor_type.toUpperCase()}${deviceLocation ? ` • ${deviceLocation}` : ''}<br>
                                <small>Last check: ${lastCheck}${responseTime}</small>
                            </div>
                        </div>
                        <div class="device-status status-${status}">
                            ${status.charAt(0).toUpperCase() + status.slice(1)}
                        </div>
                    </div>
                `;
                });

                contentElement.innerHTML = html;
            }
        }

        // Highlight search terms in text
        function highlightSearchTerm(text, searchTerm) {
            if (!searchTerm || !text) return text;

            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }

        // Escape special regex characters
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Show device list (updated to use new filtering system)
        function showDeviceList(devices, title) {
            const container = document.getElementById('deviceListContainer');

            // Store the devices and update display
            filteredDevices = devices;

            container.style.display = 'block';
            updateDeviceListDisplay();

            // Scroll to device list
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Hide device list
        function hideDeviceList() {
            document.getElementById('deviceListContainer').style.display = 'none';
            currentFilter = 'all';
            updateFilterIndicator();

            // Clear filters
            document.getElementById('deviceSearch').value = '';
            document.getElementById('categoryFilter').value = '';
        }

        // Update filter indicator
        function updateFilterIndicator() {
            // Remove active class from all cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('active');
            });

            // Add active class to current filter
            const activeCard = document.getElementById(`card-${currentFilter}`);
            if (activeCard) {
                activeCard.classList.add('active');
            }
        }

        // Start manual monitoring with user warning
        async function startManualMonitoring() {
            if (isMonitoring) return;

            const data = await loadDashboard();
            if (!data || data.stats.total === 0) {
                alert('No devices to monitor. Add some devices first!');
                return;
            }

            // Show user warning
            const estimatedTime = Math.ceil(data.stats.total * 2); // ~2 seconds per device
            const userConfirm = confirm(
                `⚠️ MANUAL MONITORING WARNING ⚠️\n\n` +
                `This will check ${data.stats.total} devices and may take ${estimatedTime}-${estimatedTime*2} seconds.\n` +
                `The system will be less responsive during this time.\n\n` +
                `Continue with manual monitoring?`
            );

            if (!userConfirm) return;

            isMonitoring = true;
            totalDevices = data.stats.total;
            checkedDevices = 0;

            // Hide device list during monitoring
            hideDeviceList();

            // Show progress bar and results container
            document.getElementById('progressContainer').style.display = 'block';
            document.getElementById('monitoringResults').style.display = 'block';
            document.getElementById('resultsContainer').innerHTML = '';

            // Update button and disable other controls
            const btn = document.getElementById('monitorBtn');
            btn.textContent = '⏳ Monitoring...';
            btn.disabled = true;

            // Disable auto-monitoring during manual check
            const autoCheckbox = document.getElementById('autoMonitor');
            const wasAutoEnabled = autoCheckbox.checked;
            autoCheckbox.checked = false;
            autoCheckbox.disabled = true;

            // Start staggered monitoring
            await monitorDevicesStaggered();

            // Reset controls
            btn.textContent = '🔄 Check All Systems';
            btn.disabled = false;
            autoCheckbox.disabled = false;
            autoCheckbox.checked = wasAutoEnabled;

            // Hide progress bar after 3 seconds
            setTimeout(() => {
                document.getElementById('progressContainer').style.display = 'none';
            }, 3000);

            isMonitoring = false;

            // Refresh dashboard with new data
            setTimeout(loadDashboard, 1000);
        }

        // Staggered monitoring to prevent system freeze
        async function monitorDevicesStaggered() {
            try {
                // Get device list
                const response = await fetch('?action=get_dashboard_data');
                const data = await response.json();

                const devices = [];
                Object.values(data.devices).forEach(deviceGroup => {
                    devices.push(...deviceGroup);
                });

                // Process devices in batches to prevent system freeze
                const batchSize = 3; // Monitor 3 devices simultaneously
                const batches = [];

                for (let i = 0; i < devices.length; i += batchSize) {
                    batches.push(devices.slice(i, i + batchSize));
                }

                // Process each batch
                for (let batchIndex = 0; batchIndex < batches.length; batchIndex++) {
                    const batch = batches[batchIndex];
                    const currentDeviceNumber = batchIndex * batchSize + 1;

                    updateProgress(
                        currentDeviceNumber,
                        devices.length,
                        `Processing batch ${batchIndex + 1}/${batches.length} (${batch.length} devices)...`
                    );

                    // Start all devices in this batch simultaneously
                    const batchPromises = batch.map(device => monitorSingleDevice(device));

                    // Wait for all devices in this batch to complete
                    await Promise.all(batchPromises);

                    // Update progress after batch completion
                    const completedDevices = Math.min((batchIndex + 1) * batchSize, devices.length);
                    updateProgress(
                        completedDevices,
                        devices.length,
                        `Completed ${completedDevices}/${devices.length} devices`
                    );

                    // Brief pause between batches to prevent overwhelming the system
                    if (batchIndex < batches.length - 1) {
                        await sleep(1500); // 1.5 second pause between batches
                    }
                }

                updateProgress(devices.length, devices.length, 'All monitoring complete!');

            } catch (error) {
                console.error('Monitoring error:', error);
                addResult('Error', 'Failed to complete monitoring: ' + error.message, 'error');
            }
        }

        // Monitor a single device
        async function monitorSingleDevice(device) {
            const startTime = Date.now();

            try {
                const formData = new FormData();
                formData.append('device_id', device.id);

                const response = await fetch('?action=monitor_single_device', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                const checkDuration = Date.now() - startTime;

                // Add result to display with attempt info
                const status = result.success ? 'operational' : 'down';
                const statusText = result.success ? 'Online' : 'Offline';
                const responseTime = result.response_time ? ` (${result.response_time}ms)` : '';
                const attempts = result.attempts ? ` [${result.attempts} attempts]` : '';
                const lastCheck = new Date().toLocaleString();

                addResult(
                    device.name,
                    `${statusText}${responseTime}${attempts}`,
                    status,
                    lastCheck,
                    `Check took ${Math.round(checkDuration/1000)}s`
                );

            } catch (error) {
                const checkDuration = Date.now() - startTime;
                addResult(
                    device.name,
                    'Check failed: ' + error.message,
                    'error',
                    null,
                    `Failed after ${Math.round(checkDuration/1000)}s`
                );
            }
        }

        // Update progress bar
        function updateProgress(current, total, text) {
            const percent = Math.round((current / total) * 100);
            document.getElementById('progressText').textContent = text;
            document.getElementById('progressPercent').textContent = percent + '%';
            document.getElementById('progressBar').style.width = percent + '%';
        }

        // Add monitoring result with duration info
        function addResult(deviceName, status, statusClass, lastCheck = null, duration = null) {
            const container = document.getElementById('resultsContainer');
            const resultDiv = document.createElement('div');
            resultDiv.style.cssText = `
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #444;
    `;

            const statusColor = {
                'operational': '#00d4aa',
                'down': '#e74c3c',
                'error': '#f39c12'
            }[statusClass] || '#888';

            const timeText = lastCheck ? `<small style="color: #888;">Last check: ${lastCheck}</small>` : '';
            const durationText = duration ? `<br><small style="color: #666;">${duration}</small>` : '';

            resultDiv.innerHTML = `
        <div>
            <strong>${deviceName}</strong><br>
            ${timeText}${durationText}
        </div>
        <div style="color: ${statusColor}; font-weight: bold;">
            ${status}
        </div>
    `;

            container.appendChild(resultDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Auto-monitoring functions
        function startAutoMonitoring() {
            loadDashboard();

            // Reduced frequency for background monitoring (10 minutes instead of 5)
            autoMonitorInterval = setInterval(() => {
                if (!isMonitoring) {
                    console.log('Auto-monitoring: Running quiet background check...');
                    runQuietMonitoring();
                }
            }, 600000); // 10 minutes for auto-monitoring

            console.log('Auto-monitoring started (every 10 minutes)');
        }

        // Quiet monitoring for background auto-checks
        async function runQuietMonitoring() {
            try {
                const response = await fetch('?action=monitor_all');
                const data = await response.json();

                console.log(`Auto-monitoring complete: ${data.total} devices checked`);

                // Refresh dashboard silently
                loadDashboard();

            } catch (error) {
                console.error('Auto-monitoring failed:', error);
            }
        }

        function stopAutoMonitoring() {
            if (autoMonitorInterval) {
                clearInterval(autoMonitorInterval);
                autoMonitorInterval = null;
                console.log('Auto-monitoring stopped');
            }
        }

        function setupAutoMonitoring() {
            const checkbox = document.getElementById('autoMonitor');

            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    startAutoMonitoring();
                } else {
                    stopAutoMonitoring();
                }
            });

            if (checkbox.checked) {
                startAutoMonitoring();
            }
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboard();
            setupAutoMonitoring();
        });

        loadDashboard();
    </script>

<?php require_once 'views/layouts/footer.php'; ?>