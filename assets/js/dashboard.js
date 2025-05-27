async function loadDashboard() {
    try {
        const response = await fetch('?action=get_dashboard_data');
        const data = await response.json();
        
        document.getElementById('loading').style.display = 'none';
        
        let html = `
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">${data.stats.total}</div>
                    <div class="stat-label">Total Systems</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number operational">${data.stats.operational}</div>
                    <div class="stat-label">Operational</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number degraded">${data.stats.degraded}</div>
                    <div class="stat-label">Degraded</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number down">${data.stats.down}</div>
                    <div class="stat-label">Down/Issues</div>
                </div>
            </div>
            
            <p>Dashboard loaded successfully! You can now add devices through Device Management.</p>
        `;
        
        document.getElementById('content').innerHTML = html;
    } catch (error) {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('content').innerHTML = '<p style="color: red; text-align: center;">Error loading dashboard: ' + error.message + '</p>';
    }
}

async function runMonitoring() {
    const btn = document.querySelector('.btn');
    const originalText = btn.textContent;

    // Show loading state
    btn.textContent = '🔄 Checking...';
    btn.disabled = true;

    try {
        const response = await fetch('?action=monitor_all');
        const data = await response.json();

        // Check if no devices exist
        if (data.total === 0) {
            alert('Monitoring feature ready! Add some devices first.');
            return;
        }

        // Show monitoring results
        let message = `Monitoring Complete!\n\n`;
        message += `Total devices checked: ${data.total}\n\n`;

        data.results.forEach(result => {
            message += `${result.device}: ${result.status}`;
            if (result.response_time) {
                message += ` (${result.response_time}ms)`;
            }
            message += '\n';
        });

        alert(message);

        // Refresh dashboard data to show updated status
        loadDashboard();

    } catch (error) {
        alert('Error running monitoring: ' + error.message);
    } finally {
        // Restore button
        btn.textContent = originalText;
        btn.disabled = false;
    }
}

// Load initial data
loadDashboard();