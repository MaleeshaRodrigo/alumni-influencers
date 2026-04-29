<?php $this->load->view('dashboard/common/header'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Security & Usage Monitoring</h1>
</div>

<div class="row">
    <!-- API Key Scoping Info -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="card-title mb-0">Configured Client API Keys</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Client Name</th>
                                <th>Key Prefix</th>
                                <th>Permissions (Scopes)</th>
                                <th>Status</th>
                                <th>Last Used</th>
                            </tr>
                        </thead>
                        <tbody id="api-keys-body">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Logs -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Real-time API Usage Logs (Last 50)</h5>
                <button class="btn btn-sm btn-light" onclick="loadUsageStats()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Timestamp</th>
                                <th>Client</th>
                                <th>Endpoint (Route)</th>
                                <th>Method</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody id="usage-logs-body">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function loadUsageStats() {
    const API_KEY = 'DASHBOARD_INTERNAL_KEY';
    
    try {
        const response = await fetch('<?= site_url('api/analytics/usage_stats') ?>', {
            headers: { 'Authorization': `Bearer ${API_KEY}` }
        });
        const res = await response.json();
        
        if(res.ok) {
            populateKeys(res.data.api_keys);
            populateLogs(res.data.recent_logs);
        }
    } catch (error) {
        console.error('Error fetching usage stats:', error);
    }
}

function populateKeys(keys) {
    const tbody = document.getElementById('api-keys-body');
    tbody.innerHTML = '';
    keys.forEach(key => {
        const statusBadge = key.is_revoked == 1 ? 
            '<span class="badge bg-danger">Revoked</span>' : 
            '<span class="badge bg-success">Active</span>';
        
        const row = `<tr>
            <td><strong>${key.name}</strong></td>
            <td><code>${key.key_prefix}...</code></td>
            <td>${key.scopes.split(',').map(s => `<span class="badge bg-info text-dark me-1">${s}</span>`).join('')}</td>
            <td>${statusBadge}</td>
            <td>${key.last_used_at || 'Never'}</td>
        </tr>`;
        tbody.innerHTML += row;
    });
}

function populateLogs(logs) {
    const tbody = document.getElementById('usage-logs-body');
    tbody.innerHTML = '';
    logs.forEach(log => {
        const statusClass = log.response_code >= 400 ? 'text-danger' : (log.response_code >= 300 ? 'text-warning' : 'text-success');
        
        const row = `<tr>
            <td><small>${log.created_at}</small></td>
            <td>${log.api_key_name || '<span class="text-muted">Anonymous</span>'}</td>
            <td><code>${log.route}</code></td>
            <td><span class="badge bg-secondary">${log.http_method}</span></td>
            <td><small>${log.ip_address}</small></td>
            <td class="${statusClass} fw-bold">${log.response_code}</td>
            <td>${log.duration_ms ? log.duration_ms + 'ms' : '-'}</td>
        </tr>`;
        tbody.innerHTML += row;
    });
}

document.addEventListener('DOMContentLoaded', loadUsageStats);
</script>

<?php $this->load->view('dashboard/common/footer'); ?>
