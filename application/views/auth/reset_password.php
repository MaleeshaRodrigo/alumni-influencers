<section class="card" style="max-width:680px; margin:0 auto;">
	<h2 style="margin-top:0;">Reset Password</h2>
	<p style="color:#555; margin-top:0;">
		Set a new strong password for your account.
	</p>

	<?php if ($this->session->flashdata('auth_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('auth_error'); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo site_url('auth/do_reset_password'); ?>" novalidate>
		<input type="hidden" name="token" value="<?php echo html_escape($token); ?>">

		<div style="margin-bottom:14px;">
			<label for="password" style="display:block; font-weight:bold; margin-bottom:6px;">New Password</label>
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
			<label for="password_confirm" style="display:block; font-weight:bold; margin-bottom:6px;">Confirm New Password</label>
			<input
				id="password_confirm"
				name="password_confirm"
				type="password"
				required
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Update Password
		</button>
	</form>
</section>
