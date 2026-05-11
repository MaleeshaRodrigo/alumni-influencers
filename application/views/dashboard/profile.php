<?php $this->load->view('dashboard/common/header'); ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= htmlspecialchars($profile['display_name']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= site_url('dashboard/alumni') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Alumni
        </a>
    </div>
</div>

<!-- Profile Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Profile Information</h5>
                <dl class="row">
                    <dt class="col-sm-3">Full Name:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($profile['display_name']) ?></dd>

                    <dt class="col-sm-3">Bio:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($profile['bio'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">Location:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($profile['location'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-3">Website:</dt>
                    <dd class="col-sm-9">
                        <?php if (!empty($profile['portfolio_url'])): ?>
                            <a href="<?= htmlspecialchars($profile['portfolio_url']) ?>" target="_blank">
                                <?= htmlspecialchars($profile['portfolio_url']) ?>
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-3">Member Since:</dt>
                    <dd class="col-sm-9"><?= date('F j, Y', strtotime($profile['created_on'] ?? 'now')) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm bg-light">
            <div class="card-body text-center">
                <h5 class="card-title">Profile Stats</h5>
                <p class="text-muted">
                    Degrees: <strong><?= count($degrees) ?></strong><br>
                    Certifications: <strong><?= count($certifications) ?></strong><br>
                    Licences: <strong><?= count($licences) ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Education Section -->
<?php if (!empty($degrees)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Education</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($degrees as $degree): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1">
                            <strong><?= htmlspecialchars($degree['field_of_study'] ?? 'N/A') ?></strong>
                        </h6>
                        <p class="mb-1 text-muted">
                            <?= htmlspecialchars($degree['institution'] ?? 'N/A') ?>
                        </p>
                        <small class="text-secondary">
                            <?php if (!empty($degree['completed_on'])): ?>
                                Completed: <?= date('F Y', strtotime($degree['completed_on'])) ?>
                            <?php else: ?>
                                Status: In Progress
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Employment Section -->
<?php if (!empty($employment)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Employment History</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($employment as $job): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <strong><?= htmlspecialchars($job['job_title'] ?? 'N/A') ?></strong>
                                </h6>
                                <p class="mb-1 text-muted">
                                    <?= htmlspecialchars($job['employer'] ?? 'N/A') ?>
                                </p>
                                <small class="text-secondary">
                                    <?= date('M Y', strtotime($job['start_date'] ?? 'now')) ?>
                                    -
                                    <?php if (!empty($job['end_date'])): ?>
                                        <?= date('M Y', strtotime($job['end_date'])) ?>
                                    <?php else: ?>
                                        Present
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php if ($job['is_current'] == 1): ?>
                                <span class="badge bg-success">Current</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Certifications Section -->
<?php if (!empty($certifications)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Certifications</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($certifications as $cert): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1">
                            <strong><?= htmlspecialchars($cert['certification_name'] ?? 'N/A') ?></strong>
                        </h6>
                        <p class="mb-1 text-muted">
                            <?= htmlspecialchars($cert['issuer'] ?? 'N/A') ?>
                        </p>
                        <small class="text-secondary">
                            Issued: <?= date('F Y', strtotime($cert['issue_date'] ?? 'now')) ?>
                            <?php if (!empty($cert['expiry_date'])): ?>
                                | Expires: <?= date('F Y', strtotime($cert['expiry_date'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Licences Section -->
<?php if (!empty($licences)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Licences</h5>
        </div>
        <div class="card-body">
            <div class="list-group">
                <?php foreach ($licences as $licence): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1">
                            <strong><?= htmlspecialchars($licence['licence_name'] ?? 'N/A') ?></strong>
                        </h6>
                        <p class="mb-1 text-muted">
                            <?= htmlspecialchars($licence['issuing_authority'] ?? 'N/A') ?>
                        </p>
                        <small class="text-secondary">
                            Issued: <?= date('F Y', strtotime($licence['issue_date'] ?? 'now')) ?>
                            <?php if (!empty($licence['expiry_date'])): ?>
                                | Expires: <?= date('F Y', strtotime($licence['expiry_date'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $this->load->view('dashboard/common/footer'); ?>
