<section class="card" style="max-width:680px; margin:0 auto;">
	<h2 style="margin-top:0;">Login</h2>
	<p style="color:#555; margin-top:0;">
		Sign in with your verified university account.
	</p>

	<?php if ($this->session->flashdata('auth_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('auth_error'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('auth_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('auth_success')); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo site_url('auth/do_login'); ?>" novalidate>
		<div style="margin-bottom:14px;">
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

		<div style="margin-bottom:18px;">
			<label for="password" style="display:block; font-weight:bold; margin-bottom:6px;">Password</label>
			<input
				id="password"
				name="password"
				type="password"
				required
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Login
		</button>
	</form>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('auth/forgot_password'); ?>">Forgot your password?</a>
	</p>
	<p>
		No account yet? <a href="<?php echo site_url('register'); ?>">Register</a>
	</p>
</section>
