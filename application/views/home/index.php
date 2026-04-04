<section class="card">
	<h2 style="margin-top:0;"><?php echo html_escape($app_name); ?></h2>
	<p><?php echo html_escape($status_message); ?></p>
	<p>
		Base URL:
		<strong><?php echo html_escape(base_url()); ?></strong>
	</p>
	<p>
		Health route:
		<a href="<?php echo site_url('ping'); ?>"><?php echo site_url('ping'); ?></a>
	</p>
	<hr style="border:none; border-top:1px solid #e5e7eb; margin:16px 0;">
	<p><strong>Quick Demo Links</strong></p>
	<p><a href="<?php echo site_url('auth/login'); ?>">Login</a> | <a href="<?php echo site_url('register'); ?>">Register</a></p>
	<p><a href="<?php echo site_url('profile/dashboard'); ?>">Profile Dashboard</a> | <a href="<?php echo site_url('bids/place'); ?>">Blind Bidding</a></p>
	<p><a href="<?php echo site_url('api/featured-today'); ?>">Public API: featured today</a></p>
	<p><a href="<?php echo site_url('api-docs'); ?>">Swagger UI</a></p>
	<p><a href="<?php echo site_url('admin/api_keys'); ?>">Admin API Keys</a> | <a href="<?php echo site_url('admin/usage_logs'); ?>">Admin Usage Logs</a></p>
</section>
