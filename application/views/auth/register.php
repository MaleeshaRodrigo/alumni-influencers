<section class="card" style="max-width:680px; margin:0 auto;">
	<h2 style="margin-top:0;">University Registration</h2>
	<p style="color:#555; margin-top:0;">
		Use your university email address to create an account.
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

	<form method="post" action="<?php echo site_url('auth/do-register'); ?>" novalidate>
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<div style="margin-bottom:14px;">
			<label for="email" style="display:block; font-weight:bold; margin-bottom:6px;">University Email</label>
			<input
				id="email"
				name="email"
				type="email"
				required
				value="<?php echo html_escape(set_value('email')); ?>"
				placeholder="name@university.edu"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
			<small style="color:#555;">
				Allowed domains: <?php echo html_escape(implode(', ', (array) $allowed_domains)); ?>
			</small>
		</div>

		<div style="margin-bottom:14px;">
			<label for="password" style="display:block; font-weight:bold; margin-bottom:6px;">Password</label>
			<input
				id="password"
				name="password"
				type="password"
				required
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
			<small style="color:#555;">
				Minimum 12 chars, include uppercase, lowercase, number, special char, and no spaces.
			</small>
		</div>

		<div style="margin-bottom:18px;">
			<label for="password_confirm" style="display:block; font-weight:bold; margin-bottom:6px;">Confirm Password</label>
			<input
				id="password_confirm"
				name="password_confirm"
				type="password"
				required
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Register
		</button>
	</form>
</section>
