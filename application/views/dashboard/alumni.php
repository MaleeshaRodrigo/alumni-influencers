<?php $this->load->view('dashboard/common/header'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Alumni Explorer</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportToCSV()">
            <i class="bi bi-download"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-file-pdf"></i> Print / Save PDF
        </button>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Programme</label>
                <input type="text" name="programme" class="form-control" placeholder="e.g. Computer Science">
            </div>
            <div class="col-md-3">
                <label class="form-label">Graduation Year</label>
                <select name="graduation_year" class="form-select">
                    <option value="">All Years</option>
                    <?php for($y=date('Y'); $y>=2010; $y--): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Industry Sector</label>
                <input type="text" name="industry" class="form-control" placeholder="e.g. Google">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 me-2">Filter</button>
                <button type="button" class="btn btn-outline-secondary" onclick="saveFilterPreset()">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="alumniTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Programme</th>
                        <th>Graduation Date</th>
                        <th>Current Industry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="alumni-list-body">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let alumniData = [];

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadAlumni();
    });

    // Load presets if any
    const saved = localStorage.getItem('alumni_filter_preset');
    if (saved) {
        const data = JSON.parse(saved);
        Object.keys(data).forEach(key => {
            const el = filterForm.querySelector(`[name="${key}"]`);
            if (el) el.value = data[key];
        });
    }

    loadAlumni();
});

function saveFilterPreset() {
    const formData = new FormData(document.getElementById('filterForm'));
    const data = Object.fromEntries(formData.entries());
    localStorage.setItem('alumni_filter_preset', JSON.stringify(data));
    alert('Filter preset saved!');
}

async function loadAlumni() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData).toString();
    
    const response = await fetch(`<?= site_url('api/analytics/alumni_list') ?>?${params}`);
    const res = await response.json();
    
    if(res.ok) {
        alumniData = res.data;
        const tbody = document.getElementById('alumni-list-body');
        tbody.innerHTML = '';
        
        if(alumniData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No alumni found matching criteria.</td></tr>';
            return;
        }

        alumniData.forEach(item => {
            const row = `<tr>
                <td><strong>${item.display_name}</strong></td>
                <td>${item.programme || 'N/A'}</td>
                <td>${item.graduation_date || 'N/A'}</td>
                <td>${item.industry || 'N/A'}</td>
                <td><button class="btn btn-sm btn-link">View Profile</button></td>
            </tr>`;
            tbody.innerHTML += row;
        });
    }
}

function exportToCSV() {
    if (alumniData.length === 0) return;
    
    const headers = Object.keys(alumniData[0]).join(',');
    const rows = alumniData.map(row => Object.values(row).join(','));
    const csvContent = "data:text/csv;charset=utf-8," + headers + "\n" + rows.join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "alumni_export.csv");
    document.body.appendChild(link);
    link.click();
}
</script>

<?php $this->load->view('dashboard/common/footer'); ?>
