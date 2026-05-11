<?php $this->load->view('dashboard/common/header'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detailed Analytics Graphs</h1>
</div>

<div class="row">
    <!-- Chart 1: Bar Chart - Alumni by Industry -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Top 10 Employer Industries</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('industryBarChart', 'alumni_by_industry')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body">
                <canvas id="industryBarChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Chart 2: Line Chart - Certification Trends -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Certification Acquisition Trends</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('certTrendChart', 'certification_trends')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body">
                <canvas id="certTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart 3: Pie Chart - Degree Distribution -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Degree Distribution</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('degreePieChart', 'degree_distribution')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body">
                <canvas id="degreePieChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Chart 4: Radar Chart - Professional Development -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Professional Development</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('devRadarChart', 'professional_development')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body">
                <canvas id="devRadarChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Chart 5: Doughnut Chart - Post-Grad Success -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Short Course Completion</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('courseDoughnutChart', 'short_courses')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body">
                <canvas id="courseDoughnutChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart 6: Stacked Bar - Career Pathways -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Career Pathways: Degrees to Job Roles</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadChart('pathwayBubbleChart', 'career_pathways')">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="pathwayBubbleChart"></canvas>
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

    // Industry Bar Chart
    fetchData('alumni_distribution').then(res => {
        if(res.ok) {
            new Chart(document.getElementById('industryBarChart'), {
                type: 'bar',
                data: {
                    labels: res.data.by_industry.map(i => i.sector),
                    datasets: [{
                        label: 'Alumni Count',
                        data: res.data.by_industry.map(i => i.count),
                        backgroundColor: '#3498db'
                    }]
                }
            });

            new Chart(document.getElementById('degreePieChart'), {
                type: 'pie',
                data: {
                    labels: res.data.by_degree.map(i => i.programme),
                    datasets: [{
                        data: res.data.by_degree.map(i => i.count),
                        backgroundColor: ['#1abc9c', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#e74c3c']
                    }]
                }
            });
        }
    });

    // Trends & Courses
    fetchData('trends').then(res => {
        if(res.ok) {
            // Cert Trend
            const years = [...new Set(res.data.certification_trends.map(i => i.year))].sort();
            new Chart(document.getElementById('certTrendChart'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Certifications Issued',
                        data: years.map(y => res.data.certification_trends.filter(i => i.year == y).reduce((a,b)=>a+parseInt(b.count),0)),
                        borderColor: '#2ecc71',
                        fill: true,
                        backgroundColor: 'rgba(46, 204, 113, 0.1)'
                    }]
                }
            });

            // Short Courses
            new Chart(document.getElementById('courseDoughnutChart'), {
                type: 'doughnut',
                data: {
                    labels: res.data.top_short_courses.map(i => i.title),
                    datasets: [{
                        data: res.data.top_short_courses.map(i => i.count),
                        backgroundColor: ['#34495e', '#7f8c8d', '#95a5a6', '#bdc3c7', '#ecf0f1']
                    }]
                }
            });
        }
    });

    // Skills Radar
    fetchData('skills_gap').then(res => {
        if(res.ok) {
            new Chart(document.getElementById('devRadarChart'), {
                type: 'radar',
                data: {
                    labels: res.data.slice(0, 6).map(i => i.skill),
                    datasets: [{
                        label: 'Skill Frequency',
                        data: res.data.slice(0, 6).map(i => i.count),
                        backgroundColor: 'rgba(231, 76, 60, 0.2)',
                        borderColor: '#e74c3c'
                    }]
                }
            });
        }
    });

    // Career Pathways Bubble (using a proxy scatter since bubble needs r)
    fetchData('career_pathways').then(res => {
        if(res.ok) {
            new Chart(document.getElementById('pathwayBubbleChart'), {
                type: 'bubble',
                data: {
                    datasets: res.data.map((item, index) => ({
                        label: `${item.degree} -> ${item.job_title}`,
                        data: [{
                            x: Math.random() * 100, // Randomizing for visualization
                            y: Math.random() * 100,
                            r: item.count * 5
                        }],
                        backgroundColor: `hsl(${index * 40}, 70%, 50%)`
                    }))
                },
                options: {
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }
    });
});

function downloadChart(canvasId, filename) {
    const canvas = document.getElementById(canvasId);
    const link = document.createElement('a');
    link.download = filename + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>

<?php $this->load->view('dashboard/common/footer'); ?>
