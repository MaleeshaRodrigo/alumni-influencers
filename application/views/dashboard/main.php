<?php $this->load->view('dashboard/common/header'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">University Analytics Overview</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="bi bi-calendar"></i> This Year
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title opacity-75">Total Alumni</h6>
                <h2 id="stat-total-alumni">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <h6 class="card-title opacity-75">Skills Acquired Post-Grad</h6>
                <h2 id="stat-total-skills">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm border-0 bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title opacity-75">Critical Gaps</h6>
                <h2 id="stat-critical-gaps">0</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm border-0 bg-info text-white">
            <div class="card-body">
                <h6 class="card-title opacity-75">Industry Connections</h6>
                <h2 id="stat-industries">0</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">Alumni Growth by Graduation Year</div>
            <div class="card-body">
                <canvas id="growthChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">Distribution by Programme</div>
            <div class="card-body">
                <canvas id="programmeChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Critical Skills Gaps (Detected via Post-Grad Certifications)</span>
                <span class="badge bg-danger">Requires Curriculum Update</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Skill / Certification</th>
                                <th>Frequency among Alumni</th>
                                <th>Timeline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="skills-gap-table">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    async function fetchData(endpoint) {
        const response = await fetch(`<?= site_url('api/analytics/') ?>${endpoint}`);
        return await response.json();
    }

    // Load Overview Data
    fetchData('alumni_distribution').then(res => {
        if(res.ok) {
            const data = res.data;
            document.getElementById('stat-total-alumni').textContent = data.by_degree.reduce((a, b) => a + parseInt(b.count), 0);
            document.getElementById('stat-industries').textContent = data.by_industry.length;

            // Growth Chart
            new Chart(document.getElementById('growthChart'), {
                type: 'line',
                data: {
                    labels: data.by_year.map(i => i.year).reverse(),
                    datasets: [{
                        label: 'Graduates',
                        data: data.by_year.map(i => i.count).reverse(),
                        borderColor: '#3498db',
                        tension: 0.1
                    }]
                }
            });

            // Programme Chart
            new Chart(document.getElementById('programmeChart'), {
                type: 'doughnut',
                data: {
                    labels: data.by_degree.map(i => i.programme),
                    datasets: [{
                        data: data.by_degree.map(i => i.count),
                        backgroundColor: ['#1abc9c', '#3498db', '#9b59b6', '#f1c40f', '#e67e22']
                    }]
                }
            });
        }
    });

    fetchData('skills_gap').then(res => {
        if(res.ok) {
            document.getElementById('stat-total-skills').textContent = res.data.length;
            document.getElementById('stat-critical-gaps').textContent = res.data.filter(i => i.count > 5).length;

            const tbody = document.getElementById('skills-gap-table');
            res.data.forEach(item => {
                const row = `<tr>
                    <td><strong>${item.skill}</strong></td>
                    <td>${item.count} Alumni</td>
                    <td>12-18 months post-grad</td>
                    <td><span class="badge ${item.count > 5 ? 'bg-danger' : 'bg-warning'}">${item.count > 5 ? 'Critical' : 'Significant'}</span></td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }
    });
});
</script>

<?php $this->load->view('dashboard/common/footer'); ?>
