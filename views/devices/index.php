<?php require_once 'views/layouts/header.php'; ?>

<div class="container">
    <h2>Device Management</h2>
    <p style="color: #ccc; margin-bottom: 20px;">Add and manage network devices for monitoring</p>

    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="add-device-form">
            <h3>Add New Device</h3>
            <p style="color: #ccc; margin-bottom: 15px;">Configure a new device for network monitoring</p>
            <form id="addDeviceForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Device Name *</label>
                        <input type="text" name="name" class="form-input" placeholder="e.g., Main Router" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP Address *</label>
                        <input type="text" name="ip_address" class="form-input" placeholder="e.g., 192.168.1.1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Device Group *</label>
                        <select name="device_group" class="form-select" required>
                            <option value="">Select a group...</option>
                            <?php foreach (Config::$device_groups as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monitor Type *</label>
                        <select name="monitor_type" class="form-select" required id="monitorTypeSelect">
                            <option value="">Select monitor type...</option>
                            <?php foreach (Config::$monitor_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port Number</label>
                        <input type="number" name="port" class="form-input" placeholder="Only required for TCP monitoring" id="portField">
                        <small style="color: #888; margin-top: 5px; display: block;">Leave empty for ping/HTTP monitoring</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" placeholder="e.g., Server Room A">
                    </div>
                    <div class="form-group form-grid-full">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-input" placeholder="Brief description of the device">
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="critical_device" id="criticalDevice">
                            <label for="criticalDevice">Mark as Critical Device</label>
                        </div>
                        <small style="color: #888; margin-top: 5px; display: block;">Critical devices generate immediate alerts when down</small>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Add Device</button>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="notice">
            <p><strong>Note:</strong> You have read-only access. Contact an administrator to add or modify devices.</p>
        </div>
    <?php endif; ?>

    <!-- Device List Section with Enhanced Controls -->
    <div class="device-list-section" style="margin-top: 30px;">
        <div style="background: #2a2a2a; border-radius: 10px; border-left: 4px solid #00d4aa;">
            <!-- Collapsible Header -->
            <div class="device-list-header" onclick="toggleDeviceList()" style="padding: 20px; cursor: pointer; user-select: none; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <span id="deviceListToggle">▶</span>
                        <span>Existing Devices</span>
                        <span id="deviceCount" style="color: #888; font-size: 0.9em; font-weight: normal;">(Loading...)</span>
                    </h3>
                    <p style="color: #ccc; margin: 5px 0 0 30px; font-size: 0.9em;">Click to expand device list</p>
                </div>
                <div style="color: #888; font-size: 0.9em;">
                    <span id="deviceListStatus">Collapsed</span>
                </div>
            </div>

            <!-- Collapsible Content -->
            <div id="deviceListContent" style="display: none; border-top: 1px solid #444;">
                <!-- Search and Filter Controls -->
                <div class="device-management-filters" style="padding: 20px; background: #1a1a1a;">
                    <div style="display: grid; grid-template-columns: 1fr 200px 150px 120px; gap: 15px; align-items: end;">
                        <!-- Search Bar -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Search Devices</label>
                            <input type="text" id="deviceManagementSearch" class="form-input" placeholder="Search by name, IP, location..."
                                   oninput="applyDeviceManagementFilters()" style="background: #2a2a2a;">
                        </div>

                        <!-- Category Filter -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Category</label>
                            <select id="deviceManagementCategory" class="form-select" onchange="applyDeviceManagementFilters()" style="background: #2a2a2a;">
                                <option value="">All Categories</option>
                                <?php foreach (Config::$device_groups as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Status</label>
                            <select id="deviceManagementStatus" class="form-select" onchange="applyDeviceManagementFilters()" style="background: #2a2a2a;">
                                <option value="">All Status</option>
                                <option value="operational">Operational</option>
                                <option value="degraded">Degraded</option>
                                <option value="down">Down</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        </div>

                        <!-- Clear Filters -->
                        <div>
                            <button class="btn btn-secondary" onclick="clearDeviceManagementFilters()" style="padding: 8px 16px; font-size: 14px;">
                                🔄 Clear
                            </button>
                        </div>
                    </div>

                    <!-- Filter Status Display -->
                    <div id="deviceManagementFilterStatus" style="margin-top: 15px; padding: 10px; background: #2a2a2a; border-radius: 6px; font-size: 0.9em; color: #888; display: none;">
                        <span id="deviceManagementFilterText"></span>
                    </div>
                </div>

                <!-- Device Table -->
                <div id="devicesList" style="padding: 0 20px 20px;">
                    <div class="loading" style="text-align: center; padding: 40px;">
                        <div class="spinner"></div>
                        <p>Loading devices...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Device Modal -->
<div id="editDeviceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Device</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editDeviceForm">
                <input type="hidden" id="editDeviceId" name="device_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Device Name *</label>
                        <input type="text" id="editDeviceName" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IP Address *</label>
                        <input type="text" id="editDeviceIP" name="ip_address" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Device Group *</label>
                        <select id="editDeviceGroup" name="device_group" class="form-select" required>
                            <?php foreach (Config::$device_groups as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monitor Type *</label>
                        <select id="editMonitorType" name="monitor_type" class="form-select" required onchange="updateEditPortField()">
                            <?php foreach (Config::$monitor_types as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port Number</label>
                        <input type="number" id="editDevicePort" name="port" class="form-input" placeholder="Only required for TCP monitoring">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" id="editDeviceLocation" name="location" class="form-input">
                    </div>
                    <div class="form-group form-grid-full">
                        <label class="form-label">Description</label>
                        <input type="text" id="editDeviceDescription" name="description" class="form-input">
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="editCriticalDevice" name="critical_device">
                            <label for="editCriticalDevice">Mark as Critical Device</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn">Update Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Enhanced Device Management Styles */
    .device-list-header:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .device-list-header h3 span:first-child {
        transition: transform 0.3s ease;
        display: inline-block;
    }

    .device-list-header.expanded span:first-child {
        transform: rotate(90deg);
    }

    .device-table {
        width: 100%;
        background: #2a2a2a;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 20px;
    }

    .device-table th,
    .device-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #444;
    }

    .device-table th {
        background: #333;
        font-weight: bold;
        color: #ccc;
    }

    .device-table tr:hover {
        background: #333;
    }

    .device-table tr.filtered-out {
        display: none;
    }

    .device-actions {
        display: flex;
        gap: 8px;
    }

    .device-actions button {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        font-weight: bold;
    }

    .btn-edit {
        background: #3498db;
        color: white;
    }

    .btn-edit:hover {
        background: #2980b9;
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
    }

    .btn-delete:hover {
        background: #c0392b;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: #2a2a2a;
        border-radius: 15px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px;
        border-bottom: 1px solid #444;
    }

    .modal-header h3 {
        margin: 0;
        color: #00d4aa;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 28px;
        color: #888;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        color: #fff;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 20px 25px;
        border-top: 1px solid #444;
    }

    /* Search highlighting */
    .search-highlight {
        background-color: rgba(0, 212, 170, 0.3);
        padding: 2px 4px;
        border-radius: 3px;
    }

    /* Status styling in table */
    .status-cell {
        font-weight: bold;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
    }

    .status-operational {
        color: #00d4aa;
        background: rgba(0, 212, 170, 0.1);
    }

    .status-degraded {
        color: #f39c12;
        background: rgba(243, 156, 18, 0.1);
    }

    .status-down {
        color: #e74c3c;
        background: rgba(231, 76, 60, 0.1);
    }

    .status-unknown {
        color: #888;
        background: rgba(136, 136, 136, 0.1);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .device-management-filters > div {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .modal-content {
            width: 95%;
            margin: 20px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Global variables for device management
    let allDevicesData = [];
    let filteredDevicesData = [];
    let isDeviceListExpanded = false;

    // Update port field requirement based on monitor type (for add form)
    document.getElementById('monitorTypeSelect').addEventListener('change', function() {
        const portField = document.getElementById('portField');
        const monitorType = this.value;

        if (monitorType === 'tcp') {
            portField.placeholder = 'Port number required for TCP monitoring';
            portField.style.borderColor = '#f39c12';
        } else {
            portField.placeholder = 'Not required for ' + monitorType + ' monitoring';
            portField.style.borderColor = '#444';
            portField.value = '';
        }
    });

    // Update port field for edit form
    function updateEditPortField() {
        const portField = document.getElementById('editDevicePort');
        const monitorType = document.getElementById('editMonitorType').value;

        if (monitorType === 'tcp') {
            portField.placeholder = 'Port number required for TCP monitoring';
            portField.style.borderColor = '#f39c12';
        } else {
            portField.placeholder = 'Not required for ' + monitorType + ' monitoring';
            portField.style.borderColor = '#444';
            if (!portField.value || portField.value === '0') {
                portField.value = '';
            }
        }
    }

    // Toggle device list expansion
    function toggleDeviceList() {
        const content = document.getElementById('deviceListContent');
        const toggle = document.getElementById('deviceListToggle');
        const status = document.getElementById('deviceListStatus');
        const header = document.querySelector('.device-list-header');

        isDeviceListExpanded = !isDeviceListExpanded;

        if (isDeviceListExpanded) {
            content.style.display = 'block';
            toggle.textContent = '▼';
            status.textContent = 'Expanded';
            header.classList.add('expanded');

            // Load devices when first expanded
            if (allDevicesData.length === 0) {
                loadDevices();
            }
        } else {
            content.style.display = 'none';
            toggle.textContent = '▶';
            status.textContent = 'Collapsed';
            header.classList.remove('expanded');
        }
    }

    // Load devices data
    async function loadDevices() {
        try {
            const response = await fetch('?action=get_dashboard_data');
            const data = await response.json();

            // Flatten devices from all groups
            allDevicesData = [];
            Object.entries(data.devices).forEach(([groupKey, devices]) => {
                devices.forEach(device => {
                    device.group_name = data.device_groups[device.device_group] || device.device_group;
                    device.group_key = device.device_group;
                    allDevicesData.push(device);
                });
            });

            // Update device count
            document.getElementById('deviceCount').textContent = `(${allDevicesData.length} devices)`;

            // Apply current filters
            applyDeviceManagementFilters();

        } catch (error) {
            document.getElementById('devicesList').innerHTML =
                '<div class="error">Error loading devices: ' + error.message + '</div>';
        }
    }

    // Apply search and category filters
    function applyDeviceManagementFilters() {
        if (allDevicesData.length === 0) return;

        const searchTerm = document.getElementById('deviceManagementSearch').value.toLowerCase().trim();
        const selectedCategory = document.getElementById('deviceManagementCategory').value;
        const selectedStatus = document.getElementById('deviceManagementStatus').value;

        // Start with all devices
        let devices = [...allDevicesData];

        // Apply category filter
        if (selectedCategory) {
            devices = devices.filter(device => device.group_key === selectedCategory);
        }

        // Apply status filter
        if (selectedStatus) {
            devices = devices.filter(device => {
                const deviceStatus = device.status || 'unknown';
                return deviceStatus === selectedStatus;
            });
        }

        // Apply search filter
        if (searchTerm) {
            devices = devices.filter(device => {
                const searchFields = [
                    device.name,
                    device.ip_address,
                    device.location || '',
                    device.group_name,
                    device.description || '',
                    device.monitor_type
                ].join(' ').toLowerCase();

                return searchFields.includes(searchTerm);
            });
        }

        filteredDevicesData = devices;
        updateDeviceManagementDisplay();
        updateDeviceManagementFilterStatus(searchTerm, selectedCategory, selectedStatus);
    }

    // Clear all filters
    function clearDeviceManagementFilters() {
        document.getElementById('deviceManagementSearch').value = '';
        document.getElementById('deviceManagementCategory').value = '';
        document.getElementById('deviceManagementStatus').value = '';
        applyDeviceManagementFilters();
    }

    // Update filter status display
    function updateDeviceManagementFilterStatus(searchTerm, selectedCategory, selectedStatus) {
        const statusDiv = document.getElementById('deviceManagementFilterStatus');
        const statusText = document.getElementById('deviceManagementFilterText');

        const filters = [];

        if (selectedCategory) {
            const categoryName = <?= json_encode(Config::$device_groups) ?>[selectedCategory] || selectedCategory;
            filters.push(`Category: ${categoryName}`);
        }

        if (selectedStatus) {
            filters.push(`Status: ${selectedStatus}`);
        }

        if (searchTerm) {
            filters.push(`Search: "${searchTerm}"`);
        }

        if (filters.length > 0) {
            statusText.innerHTML = `
            <strong>Active filters:</strong> ${filters.join(', ')}
            <span style="margin-left: 15px; color: #00d4aa;">
                Showing ${filteredDevicesData.length} of ${allDevicesData.length} devices
            </span>
        `;
            statusDiv.style.display = 'block';
        } else {
            statusDiv.style.display = 'none';
        }
    }

    // Update device management display
    function updateDeviceManagementDisplay() {
        const container = document.getElementById('devicesList');

        if (filteredDevicesData.length === 0) {
            const searchTerm = document.getElementById('deviceManagementSearch').value;
            const selectedCategory = document.getElementById('deviceManagementCategory').value;
            const selectedStatus = document.getElementById('deviceManagementStatus').value;

            let emptyMessage = 'No devices found';
            if (searchTerm || selectedCategory || selectedStatus) {
                emptyMessage = 'No devices match your current filters';
            }

            container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #888;">
                <h4 style="color: #ccc; margin-bottom: 10px;">${emptyMessage}</h4>
                <p>Try adjusting your search terms or filters</p>
            </div>
        `;
            return;
        }

        // Build table HTML
        let html = `
        <table class="device-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>IP Address</th>
                    <th>Group</th>
                    <th>Monitor Type</th>
                    <th>Port</th>
                    <th>Status</th>
                    <th>Last Check</th>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
    `;

        const searchTerm = document.getElementById('deviceManagementSearch').value.toLowerCase().trim();

        filteredDevicesData.forEach(device => {
            const status = device.status || 'unknown';
            const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never';
            const port = device.port ? device.port : '-';

            // Highlight search terms
            let deviceName = device.name;
            let deviceIP = device.ip_address;
            let deviceLocation = device.location || '';

            if (searchTerm) {
                deviceName = highlightSearchTerm(device.name, searchTerm);
                deviceIP = highlightSearchTerm(device.ip_address, searchTerm);
                deviceLocation = device.location ? highlightSearchTerm(device.location, searchTerm) : '';
            }

            html += `
            <tr>
                <td>
                    <strong>${deviceName}</strong>
                    ${deviceLocation ? `<br><small style="color: #888;">${deviceLocation}</small>` : ''}
                </td>
                <td style="font-family: 'Courier New', monospace; color: #00d4aa;">${deviceIP}</td>
                <td style="color: #f39c12;">${device.group_name}</td>
                <td>${device.monitor_type.toUpperCase()}</td>
                <td>${port}</td>
                <td><span class="status-cell status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                <td><small>${lastCheck}</small></td>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <td>
                    <div class="device-actions">
                        <button onclick="editDevice(${device.id})" class="btn-edit">Edit</button>
                        <button onclick="deleteDevice(${device.id})" class="btn-delete">Delete</button>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        `;
        });

        html += `
            </tbody>
        </table>
        <div style="margin-top: 20px; padding: 15px; background: #1a1a1a; border-radius: 6px; color: #888;">
            <strong>Total Devices:</strong> ${allDevicesData.length} |
            <strong>Showing:</strong> ${filteredDevicesData.length}
        </div>
    `;

        container.innerHTML = html;
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

    // Edit device function
    function editDevice(deviceId) {
        const device = allDevicesData.find(d => d.id == deviceId);
        if (!device) {
            alert('Device not found');
            return;
        }

        // Populate edit form
        document.getElementById('editDeviceId').value = device.id;
        document.getElementById('editDeviceName').value = device.name;
        document.getElementById('editDeviceIP').value = device.ip_address;
        document.getElementById('editDeviceGroup').value = device.device_group;
        document.getElementById('editMonitorType').value = device.monitor_type;
        document.getElementById('editDevicePort').value = device.port || '';
        document.getElementById('editDeviceLocation').value = device.location || '';
        document.getElementById('editDeviceDescription').value = device.description || '';
        document.getElementById('editCriticalDevice').checked = device.critical_device == 1;

        // Update port field styling
        updateEditPortField();

        // Show modal
        document.getElementById('editDeviceModal').style.display = 'flex';
    }

    // Close edit modal
    function closeEditModal() {
        document.getElementById('editDeviceModal').style.display = 'none';
    }

    // Handle edit form submission
    document.getElementById('editDeviceForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const monitorType = formData.get('monitor_type');
        const port = formData.get('port');

        if (monitorType === 'tcp' && (!port || port.trim() === '')) {
            alert('Port number is required for TCP monitoring');
            document.getElementById('editDevicePort').focus();
            return;
        }

        if (monitorType !== 'tcp') {
            formData.set('port', '');
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Adding Device...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('?action=add_device', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'success';
                successDiv.textContent = 'Device added successfully!';
                e.target.parentNode.insertBefore(successDiv, e.target);

                e.target.reset();

                // Reload devices if list is expanded
                if (isDeviceListExpanded) {
                    loadDevices();
                } else {
                    // Update device count
                    allDevicesData.push({id: 'new'}); // Temporary placeholder
                    document.getElementById('deviceCount').textContent = `(${allDevicesData.length} devices)`;
                }

                setTimeout(() => successDiv.remove(), 3000);
            } else {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error';
                errorDiv.textContent = 'Error: ' + result.message;
                e.target.parentNode.insertBefore(errorDiv, e.target);

                setTimeout(() => errorDiv.remove(), 5000);
            }
        } catch (error) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = 'Network error: ' + error.message;
            e.target.parentNode.insertBefore(errorDiv, e.target);

            setTimeout(() => errorDiv.remove(), 5000);
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });

    // Delete device function
    async function deleteDevice(deviceId) {
        const device = allDevicesData.find(d => d.id == deviceId);
        const deviceName = device ? device.name : 'Unknown Device';

        if (!confirm(`Are you sure you want to delete "${deviceName}"?\n\nThis action cannot be undone and will remove all monitoring history for this device.`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('device_id', deviceId);

            const response = await fetch('?action=delete_device', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'success';
                successDiv.textContent = `Device "${deviceName}" deleted successfully!`;
                successDiv.style.position = 'fixed';
                successDiv.style.top = '20px';
                successDiv.style.right = '20px';
                successDiv.style.zIndex = '9999';
                document.body.appendChild(successDiv);

                setTimeout(() => successDiv.remove(), 3000);

                // Reload devices
                loadDevices();
            } else {
                alert('Error deleting device: ' + result.message);
            }
        } catch (error) {
            alert('Network error: ' + error.message);
        }
    }

    // Close modal when clicking outside
    document.getElementById('editDeviceModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('editDeviceModal').style.display === 'flex') {
            closeEditModal();
        }
    });

    // Initialize device count on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Load initial device count
        fetch('?action=get_dashboard_data')
            .then(response => response.json())
            .then(data => {
                let totalDevices = 0;
                Object.values(data.devices).forEach(deviceGroup => {
                    totalDevices += deviceGroup.length;
                });
                document.getElementById('deviceCount').textContent = `(${totalDevices} devices)`;
            })
            .catch(error => {
                console.error('Error loading device count:', error);
                document.getElementById('deviceCount').textContent = '(Error loading count)';
            });
    });
</script>

<?php require_once 'views/layouts/footer.php'; ?>

const submitBtn = e.target.querySelector('button[type="submit"]');
const originalText = submitBtn.textContent;
submitBtn.textContent = 'Updating...';
submitBtn.disabled = true;

try {
const response = await fetch('?action=edit_device', {
method: 'POST',
body: formData
});

const result = await response.json();

if (result.success) {
closeEditModal();
loadDevices(); // Reload devices

// Show success message
const successDiv = document.createElement('div');
successDiv.className = 'success';
successDiv.textContent = 'Device updated successfully!';
successDiv.style.position = 'fixed';
successDiv.style.top = '20px';
successDiv.style.right = '20px';
successDiv.style.zIndex = '9999';
document.body.appendChild(successDiv);

setTimeout(() => successDiv.remove(), 3000);
} else {
alert('Error updating device: ' + result.message);
}
} catch (error) {
alert('Network error: ' + error.message);
} finally {
submitBtn.textContent = originalText;
submitBtn.disabled = false;
}
});

// Add device form submission (existing functionality)
document.getElementById('addDeviceForm').addEventListener('submit', async (e) => {
e.preventDefault();

const formData = new FormData(e.target);
const monitorType = formData.get('monitor_type');
const port = formData.get('port');

if (monitorType === 'tcp' && (!port || port.trim() === '')) {
alert('Port number is required for TCP monitoring');
document.getElementById('portField').focus();
return;
}

if (monitorType !== 'tcp') {
formData.set('port', '');
}