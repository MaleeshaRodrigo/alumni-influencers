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
</section>
