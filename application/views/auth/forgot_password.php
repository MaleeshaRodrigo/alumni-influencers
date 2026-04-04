<section class="card" style="max-width:680px; margin:0 auto;">
	<h2 style="margin-top:0;">Forgot Password</h2>
	<p style="color:#555; margin-top:0;">
		Enter your account email to receive a reset link.
	</p>

	<?php if ($this->session->flashdata('auth_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo strip_tags((string) $this->session->flashdata('auth_error'), '<p><br><strong><em><ul><li>'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('auth_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('auth_success')); ?>
		</div>
	<?php endif; ?>

	<?php if (ENVIRONMENT !== 'production' && $this->session->flashdata('dev_reset_link')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #f59e0b; background:#fffbeb; color:#92400e; border-radius:6px;">
			<strong>Development mode:</strong> email sending is stubbed.<br>
			Reset link:
			<a href="<?php echo html_escape($this->session->flashdata('dev_reset_link')); ?>">
				<?php echo html_escape($this->session->flashdata('dev_reset_link')); ?>
			</a>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo site_url('auth/send_reset'); ?>" novalidate>
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<div style="margin-bottom:18px;">
			<label for="email" style="display:block; font-weight:bold; margin-bottom:6px;">Email</label>
			<input
				id="email"
				name="email"
				type="email"
				required
				value="<?php echo html_escape(set_value('email')); ?>"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Send Reset Link
		</button>
	</form>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('auth/login'); ?>">Back to login</a>
	</p>
</section>
