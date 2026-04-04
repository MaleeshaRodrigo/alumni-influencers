<section class="card" style="max-width:680px; margin:0 auto;">
	<h2 style="margin-top:0;">Verify Your Email</h2>
	<p>
		<?php if ($this->session->flashdata('verify_email')): ?>
			We sent a verification link to <strong><?php echo html_escape($this->session->flashdata('verify_email')); ?></strong>.
		<?php else: ?>
			Please check your inbox for the verification link.
		<?php endif; ?>
	</p>
	<p style="color:#555;">
		You must verify your email before logging in.
	</p>

	<?php if ($this->session->flashdata('auth_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('auth_success')); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('auth_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('auth_error')); ?>
		</div>
	<?php endif; ?>

	<?php if (ENVIRONMENT !== 'production' && $this->session->flashdata('dev_verify_link')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #f59e0b; background:#fffbeb; color:#92400e; border-radius:6px;">
			<strong>Development mode:</strong> Email delivery is stubbed.<br>
			Use this link to verify:
			<a href="<?php echo html_escape($this->session->flashdata('dev_verify_link')); ?>">
				<?php echo html_escape($this->session->flashdata('dev_verify_link')); ?>
			</a>
		</div>
	<?php endif; ?>

	<p style="margin-top:18px;">
		<a href="<?php echo site_url('register'); ?>">Back to registration</a>
	</p>
</section>
