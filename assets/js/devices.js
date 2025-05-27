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
        portField.value = '';
    }
});

async function loadDevices() {
    try {
        const response = await fetch('?action=get_dashboard_data');
        const data = await response.json();
        
        let html = '<table class="device-table"><thead><tr><th>Name</th><th>IP Address</th><th>Group</th><th>Monitor Type</th><th>Port</th><th>Status</th><th>Last Check</th><th>Actions</th></tr></thead><tbody>';
        
        let deviceCount = 0;
        for (const [groupKey, devices] of Object.entries(data.devices)) {
            devices.forEach(device => {
                deviceCount++;
                const status = device.status || 'unknown';
                const lastCheck = device.last_check ? new Date(device.last_check).toLocaleString() : 'Never';
                const port = device.port ? device.port : '-';
                
                html += `
                    <tr>
                        <td><strong>${device.name}</strong><br><small style="color: #888;">${device.location || 'No location'}</small></td>
                        <td>${device.ip_address}</td>
                        <td>${data.device_groups[device.device_group] || device.device_group}</td>
                        <td>${device.monitor_type.toUpperCase()}</td>
                        <td>${port}</td>
                        <td class="status-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</td>
                        <td>${lastCheck}</td>
                        <td><button onclick="deleteDevice(${device.id})" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</button></td>
                    </tr>
                `;
            });
        }
        
        if (deviceCount === 0) {
            html += '<tr><td colspan="8" style="text-align: center; color: #888; padding: 40px;">No devices configured yet. Add your first device above.</td></tr>';
        }
        
        html += '</tbody></table>';
        html += `<div style="margin-top: 20px; padding: 15px; background: #2a2a2a; border-radius: 6px;"><strong>Total Devices:</strong> ${deviceCount}</div>`;
        
        document.getElementById('devicesList').innerHTML = html;
    } catch (error) {
        document.getElementById('devicesList').innerHTML = '<div class="error">Error loading devices: ' + error.message + '</div>';
    }
}

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
            loadDevices();
            
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
            loadDevices();
        } else {
            alert('Error deleting device: ' + result.message);
        }
    } catch (error) {
        alert('Network error: ' + error.message);
    }
}

// Load devices when page loads
loadDevices();