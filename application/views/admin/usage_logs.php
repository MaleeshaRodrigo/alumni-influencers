<section class="card" style="max-width:1080px; margin:0 auto;">
	<h2 style="margin-top:0;">Admin - API Usage Logs</h2>
	<p style="color:#555; margin-top:0;">Review bearer-token endpoint usage and response outcomes.</p>

	<form method="get" action="<?php echo site_url('admin/usage_logs'); ?>" style="margin:12px 0;">
		<label for="api_key_id" style="font-weight:bold; margin-right:8px;">Filter by API key ID:</label>
		<input id="api_key_id" name="api_key_id" type="number" min="1" value="<?php echo (int) $filter_api_key_id > 0 ? (int) $filter_api_key_id : ''; ?>" style="padding:8px; border:1px solid #d1d5db; border-radius:6px;">
		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:8px 14px; border-radius:6px; cursor:pointer;">Apply</button>
		<a href="<?php echo site_url('admin/usage_logs'); ?>" style="margin-left:8px;">Reset</a>
		<a href="<?php echo site_url('admin/api_keys'); ?>" style="margin-left:8px;">Back to API keys</a>
	</form>

	<?php if ((int) $filter_api_key_id > 0 && $total_for_key !== NULL): ?>
		<p style="margin:8px 0 14px;">
			<strong>Total logs for key #<?php echo (int) $filter_api_key_id; ?>:</strong>
			<?php echo (int) $total_for_key; ?>
		</p>
	<?php endif; ?>

	<?php if (empty($logs)): ?>
		<p><em>No usage logs found.</em></p>
	<?php else: ?>
		<div style="overflow:auto;">
			<table style="width:100%; border-collapse:collapse;">
				<thead>
					<tr>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">ID</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">API Key</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Method</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Route</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">IP</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Status</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Duration</th>
						<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Created At</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($logs as $log): ?>
						<tr>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo (int) $log['id']; ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;">
								<?php if (!empty($log['api_key_id'])): ?>
									#<?php echo (int) $log['api_key_id']; ?>
									<?php if (!empty($log['key_prefix'])): ?>
										(<code><?php echo html_escape($log['key_prefix']); ?>...</code>)
									<?php endif; ?>
								<?php else: ?>
									<em>None</em>
								<?php endif; ?>
							</td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo html_escape($log['http_method']); ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><code><?php echo html_escape($log['route']); ?></code></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo html_escape($log['ip_address']); ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo (int) $log['response_code']; ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo isset($log['duration_ms']) && $log['duration_ms'] !== NULL ? ((int) $log['duration_ms'].' ms') : '<em>n/a</em>'; ?></td>
							<td style="border-bottom:1px solid #f1f5f9; padding:8px;"><?php echo html_escape($log['created_at']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>
