<section class="card" style="max-width:980px; margin:0 auto;">
	<h2 style="margin-top:0;">Admin - API Keys</h2>
	<p style="color:#555; margin-top:0;">Create, review, and revoke bearer tokens.</p>

	<?php if ($this->session->flashdata('admin_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo strip_tags((string) $this->session->flashdata('admin_error'), '<p><br><strong><em><ul><li>'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('admin_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('admin_success')); ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($raw_token_once)): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #f59e0b; background:#fffbeb; color:#92400e; border-radius:6px;">
			<strong>Copy now (shown once):</strong><br>
			<code style="word-break:break-all;"><?php echo html_escape($raw_token_once); ?></code>
		</div>
	<?php endif; ?>

	<h3>Create API Key</h3>
	<form method="post" action="<?php echo site_url('admin/create_api_key'); ?>" novalidate>
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
			<div>
				<label for="name" style="display:block; font-weight:bold; margin-bottom:6px;">Key Name</label>
				<input id="name" name="name" type="text" required value="<?php echo html_escape(set_value('name')); ?>" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
			</div>
			<div>
				<label for="user_id" style="display:block; font-weight:bold; margin-bottom:6px;">User ID (optional)</label>
				<input id="user_id" name="user_id" type="number" min="1" value="<?php echo html_escape(set_value('user_id')); ?>" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
			</div>
			<div>
				<label for="scopes" style="display:block; font-weight:bold; margin-bottom:6px;">Scopes (comma-separated)</label>
				<input id="scopes" name="scopes" type="text" value="<?php echo html_escape(set_value('scopes')); ?>" placeholder="featured.read,usage.read" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
			</div>
			<div>
				<label for="expires_at" style="display:block; font-weight:bold; margin-bottom:6px;">Expires At (optional)</label>
				<input id="expires_at" name="expires_at" type="text" value="<?php echo html_escape(set_value('expires_at')); ?>" placeholder="YYYY-MM-DD HH:MM:SS" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;">
			</div>
		</div>

		<p style="margin-top:12px;">
			<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">Create API Key</button>
			<a href="<?php echo site_url('admin/usage_logs'); ?>" style="margin-left:12px;">View usage logs</a>
		</p>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:16px 0;">
	<h3 style="margin-top:0;">Existing Keys</h3>

	<?php if (empty($keys)): ?>
		<p><em>No API keys found.</em></p>
	<?php else: ?>
		<div style="overflow:auto;">
			<table style="width:100%; border-collapse:collapse;">
				<thead>
					<tr>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">ID</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Name</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Owner</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Prefix</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Scopes</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Status</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Last Used</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($keys as $key): ?>
						<tr>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo (int) $key['id']; ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo html_escape($key['name']); ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo !empty($key['owner_email']) ? html_escape($key['owner_email']) : ('User #'.(int) $key['user_id']); ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><code><?php echo html_escape($key['key_prefix']); ?>...</code></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo html_escape($key['scopes']); ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;">
								<?php if ((int) $key['is_revoked'] === 1): ?>
									<span style="color:#991b1b;">Revoked</span>
								<?php elseif (!empty($key['expires_at']) && strtotime($key['expires_at']) <= time()): ?>
									<span style="color:#92400e;">Expired</span>
								<?php else: ?>
									<span style="color:#166534;">Active</span>
								<?php endif; ?>
							</td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo !empty($key['last_used_at']) ? html_escape($key['last_used_at']) : '<em>Never</em>'; ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;">
								<?php if ((int) $key['is_revoked'] === 0): ?>
									<form method="post" action="<?php echo site_url('admin/revoke_api_key/'.(int) $key['id']); ?>" style="margin:0;">
										<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
										<input type="text" name="reason" placeholder="Reason (optional)" style="padding:6px; border:1px solid #d1d5db; border-radius:6px; width:160px;">
										<button type="submit" style="background:#7f1d1d; color:#fff; border:none; padding:7px 10px; border-radius:6px; cursor:pointer;">Revoke</button>
									</form>
								<?php else: ?>
									<em>—</em>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>
