<?php $this->load->view('dashboard/common/header'); ?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">University Login</h4>
                <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                <?php endif; ?>
                <form action="<?= site_url('auth/login') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" required placeholder="name@westminster.ac.uk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="<?= site_url('dashboard/register') ?>">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('dashboard/common/footer'); ?>
