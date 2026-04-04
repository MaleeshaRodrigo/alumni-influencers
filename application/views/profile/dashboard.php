<section class="card" style="max-width:760px; margin:0 auto;">
	<h2 style="margin-top:0;">Profile Dashboard</h2>
	<p style="color:#555; margin-top:0;">
		Manage your basic alumni profile details.
	</p>

	<?php if ($this->session->flashdata('profile_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('profile_error'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('profile_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('profile_success')); ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($profile)): ?>
		<p><strong>Full name:</strong> <?php echo html_escape($profile['display_name']); ?></p>
		<p><strong>Bio:</strong> <?php echo !empty($profile['bio']) ? nl2br(html_escape($profile['bio'])) : '<em>Not set</em>'; ?></p>
		<p>
			<strong>LinkedIn:</strong>
			<?php if (!empty($profile['linkedin_url'])): ?>
				<a href="<?php echo html_escape($profile['linkedin_url']); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo html_escape($profile['linkedin_url']); ?>
				</a>
			<?php else: ?>
				<em>Not set</em>
			<?php endif; ?>
		</p>
	<?php else: ?>
		<p style="margin:0 0 12px;">You have not created your basic profile yet.</p>
	<?php endif; ?>

	<p style="margin-top:18px;">
		<a href="<?php echo site_url('profile/basic'); ?>">Edit basic profile</a>
	</p>
</section>
