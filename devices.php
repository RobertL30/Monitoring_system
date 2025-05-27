<?php// devices.php - Device management pageglobal $device_groups, $monitor_types; ?> <!DOCTYPE html> 
<html lang="en">
<head> 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Device Management - Network Monitor</title>
<style> * { margin: 0; padding: 0; box-sizing: border-box; } 
body { 
    font-family: 'Segoe UI', sans-serif; 
    background: #1a1a1a; color: #fff; } .header { 
        background: linear-gradient(135deg, #2c5aa0, #1e3a5f); 
        padding: 20px; } .header-content { 
            max-width: 1200px; 
            margin: 0 auto; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; } .logo { 
                display: flex; 
                align-items: center; 
                gap: 15px; } .logo-icon { 
                    width: 50px; 
                    height: 50px; 
                    background: #00d4aa; 
                    border-radius: 10px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-weight: bold; 
                    font-size: 20px; 
                    color: #1a1a1a; } .nav { 
                        display: flex; 
                        gap: 20px; } .nav a { 
                            color: white; 
                            text-decoration: none; 
                            padding: 10px 20px; 
                            border-radius: 5px; 
                            transition: background 0.3s; } .nav a:hover, .nav a.active { 
                                background: rgba(255,255,255,0.1); } .container { 
                                    max-width: 1200px; 
                                    margin: 0 auto; 
                                    padding: 20px; } .btn { 
                                        background: #00d4aa; 
                                        color: white; 
                                        border: none; 
                                        padding: 12px 24px; 
                                        border-radius: 6px; 
                                        cursor: pointer; 
                                        font-size: 16px; 
                                        text-decoration: none; 
                                        display: inline-block; transition: background 0.3s; } .btn:hover { background: #00b894; } .btn-danger { background: #e74c3c; } .btn-danger:hover { background: #c0392b; } .form-group { margin-bottom: 20px; } .form-label { display: block; margin-bottom: 8px; color: #ccc; font-weight: 500; } .form-input, .form-select { width: 100%; padding: 12px; background: #2a2a2a; border: 2px solid #444; border-radius: 6px; color: white; font-size: 14px; } .form-input:focus, .form-select:focus { outline: none; border-color: #00d4aa; } .form-input::placeholder { color: #888; } .device-table { width: 100%; background: #2a2a2a; border-radius: 10px; overflow: hidden; margin-top: 20px; } .device-table th, .device-table td { padding: 15px; text-align: left; border-bottom: 1px solid #444; } .device-table th { background: #333; font-weight: bold; } .device-table tr:hover { background: #333; } .status-operational { color: #00d4aa; font-weight: bold; } .status-down { color: #e74c3c; font-weight: bold; } .status-degraded { color: #f39c12; font-weight: bold; } .status-unknown { color: #888; } .add-device-form { background: #2a2a2a; padding: 25px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #00d4aa; } .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; } .form-grid-full { grid-column: 1 / -1; } .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 10px; } .loading { text-align: center; padding: 40px; color: #888; } .error { color: #e74c3c; background: #2a1a1a; padding: 15px; border-radius: 6px; margin: 10px 0; } .success { color: #00d4aa; background: #1a2a1a; padding: 15px; border-radius: 6px; margin: 10px 0; } </style> </head> <body> <div class="header"> <div class="header-content"> <div class="logo"> <div class="logo-icon">NET</div> <div><h1>Network Monitor</h1></div> </div> <div class="nav"> <a href="?page=dashboard">Dashboard</a> <a href="?page=devices" class="active">Device Management</a> <a href="?page=reports">Reports</a> <a href="?logout=1">Logout</a> </div> </div> </div> <div class="container"> <h2>Device Management</h2> <p style="color: #ccc; margin-bottom: 20px;">Add and manage network devices for monitoring</p> <?php if ($_SESSION['role'] === 'admin'): ?> <div class="add-device-form"> <h3>Add New Device</h3> <p style="color: #ccc; margin-bottom: 15px;">Configure a new device for network monitoring</p> <form id="addDeviceForm"> <div class="form-grid"> <div class="form-group"> <label class="form-label">Device Name *</label> <input type="text" name="name" class="form-input" placeholder="e.g., Main Router" required> </div> <div class="form-group"> <label class="form-label">IP Address *</label> <input type="text" name="ip_address" class="form-input" placeholder="e.g., 192.168.1.1" required> </div> <div class="form-group"> <label class="form-label">Device Group *</label> <select name="device_group" class="form-select" required> <option value="">Select a group...</option> <?php foreach ($device_groups as $key => $label): ?> <option value="<?= $key ?>"><?= $label ?></option> <?php endforeach; ?> </select> </div> <div class="form-group"> <label class="form-label">Monitor Type *</label> <select name="monitor_type" class="form-select" required id="monitorTypeSelect"> <option value="">Select monitor type...</option> <?php foreach ($monitor_types as $key => $label): ?> <option value="<?= $key ?>"><?= $label ?></option> <?php endforeach; ?> </select> </div> <div class="form-group"> <label class="form-label">Port Number</label> <input type="number" name="port" class="form-input" placeholder="Only required for TCP monitoring" id="portField"> <small style="color: #888; margin-top: 5px; display: block;">Leave empty for ping/HTTP monitoring</small> </div> <div class="form-group"> <label class="form-label">Location</label> <input type="text" name="location" class="form-input" placeholder="e.g., Server Room A"> </div> <div class="form-group form-grid-full"> <label class="form-label">Description</label> <input type="text" name="description" class="form-input" placeholder="Brief description of the device"> </div> <div class="form-group"> <div class="checkbox-group"> <input type="checkbox" name="critical_device" id="criticalDevice"> <label for="criticalDevice">Mark as Critical Device</label> </div> <small style="color: #888; margin-top: 5px; display: block;">Critical devices generate immediate alerts when down</small> </div> <div class="form-group"> <button type="submit" class="btn">Add Device</button> </div> </div> </form> </div> <?php else: ?> <div style="background: #2a2a2a; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #f39c12;"> <p><strong>Note:</strong> You have read-only access. Contact an administrator to add or modify devices.</p> </div> <?php endif; ?> <div id="devicesList" class="loading"> <p>Loading devices...</p> </div> </div>

<script>
        // Update port field requirement based on monitor type
        document.getElementById('monitorTypeSelect').addEventListener('change', function() {
            const portField = document.getElementById('portField');
            const monitorType = this.value;
            
            if (monitorType === 'tcp') {
                portField.placeholder = 'Port number required for TCP monitoring';
portField.style.borderColor = '#f39c12';
            } else {
                portField.placeholder = 'Not required for ' + monitorType + ' monitoring';
portField.style.borderColor = '#444';
                portField.value = ''; // Clear the field
            }
        });
 
        async function loadDevices() {
            try {
                const response = await fetch('?action=get_dashboard_data');
                const data = await response.json();
                
                let html = '<table class="device-table"><thead><tr><th>Name</th><th>IP Address</th><th>Group</th><th>Monitor Type</th><th>Port</th><th>Status</th><th>Last Check</th>';
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                html += '<th>Actions</th>';
                <?php endif; ?>
                
                html += '</tr></thead><tbody>';
                
                let deviceCount = 0;
                for (const [groupKey, devices] of Object.entries(data.devices)) {
                    devices.forEach(device => {
                        deviceCount++;
                        const status = device.status || 'unknown';
                        const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never';
                        const port = device.port ? device.port : '-';
                        
                        html += `
                            <tr>
${device.name}</strong><br><small style="color: #888;">${device.location || 'No location'}</small></td>
                                <td>${device.ip_address}</td>
                                <td>${data.device_groups[device.device_group] || device.device_group}</td>
                                <td>${device.monitor_type.toUpperCase()}</td>
                                <td>${port}</td>
                                <td class="status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</td>
                                <td>${lastCheck}</td>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
<button onclick="deleteDevice(${device.id})" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</button></td>
                                <?php endif; ?>
                            </tr>
                        `;
                    });
                }
                
                if (deviceCount === 0) {
                    html += '<tr><td colspan="8" style="text-align: center; color: #888; padding: 40px;">No devices configured yet. Add your first device above.</td></tr>';
                }
                
                html += '</tbody></table>';
                
                // Add summary
                html += `<div style="margin-top: 20px; padding: 15px; background: #2a2a2a; border-radius: 6px;">
                    <strong>Total Devices:</strong> ${deviceCount}
                </div>`;
                
                document.getElementById('devicesList').innerHTML = html;
            } catch (error) {
                document.getElementById('devicesList').innerHTML = '<div class="error">Error loading devices: ' + error.message + '</div>';
            }
        }
 
        <?php if ($_SESSION['role'] === 'admin'): ?>
        document.getElementById('addDeviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
const formData = new FormData(e.target);
            
            // Get monitor type to check if port is required
            const monitorType = formData.get('monitor_type');
            const port = formData.get('port');
            
            // Only require port for TCP monitoring
            if (monitorType === 'tcp' && (!port || port.trim() === '')) {
                alert('Port number is required for TCP monitoring');
                document.getElementById('portField').focus();
                return;
            }
            
            // Clear port field for non-TCP monitoring types
            if (monitorType !== 'tcp') {
                formData.set('port', '');
            }
            
            // Show loading state
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
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'success';
                    successDiv.textContent = 'Device added successfully!';
e.target.parentNode.insertBefore(successDiv, e.target);
                    
                    // Clear form and reload devices
e.target.reset();
                    loadDevices();
                    
                    // Remove success message after 3 seconds
                    setTimeout(() => successDiv.remove(), 3000);
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error';
                    errorDiv.textContent = 'Error: ' + result.message;
e.target.parentNode.insertBefore(errorDiv, e.target);
                    
                    // Remove error message after 5 seconds
                    setTimeout(() => errorDiv.remove(), 5000);
                }
            } catch (error) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error';
                errorDiv.textContent = 'Network error: ' + error.message;
e.target.parentNode.insertBefore(errorDiv, e.target);
                
                setTimeout(() => errorDiv.remove(), 5000);
            } finally {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
 
        async function deleteDevice(deviceId) {
            if (!confirm('Are you sure you want to delete this device? This action cannot be undone.')) {
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
                    loadDevices(); // Reload the device list
                } else {
                    alert('Error deleting device: ' + result.message);
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            }
        }
        <?php endif; ?>
 
        // Load devices when page loads
        loadDevices();
    </script>
</body>
</html>