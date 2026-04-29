<?php $this->load->view('dashboard/common/header'); ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title text-center mb-4">University Registration</h4>
                <p class="text-muted text-center small">Only university email domains allowed.</p>
                
                <?php if(validation_errors()): ?>
                    <div class="alert alert-danger"><?= validation_errors() ?></div>
                <?php endif; ?>

                <form action="<?= site_url('auth/register') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= set_value('full_name') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">University Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= set_value('email') ?>" placeholder="name@westminster.ac.uk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="form-text">Min 12 chars, must include upper, lower, number, and symbol.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Register</button>
                </form>
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="<?= site_url('dashboard/login') ?>">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('dashboard/common/footer'); ?>
