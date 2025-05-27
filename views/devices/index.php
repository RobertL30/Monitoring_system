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

    <div id="devicesList" class="loading">
        <p>Loading devices...</p>
    </div>
</div>

<script src="assets/js/devices.js"></script>

<?php require_once 'views/layouts/footer.php'; ?>