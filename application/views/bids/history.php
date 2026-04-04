<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Bid History</h2>

	<?php if (!empty($eligibility)): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #93c5fd; background:#eff6ff; color:#1e40af; border-radius:6px;">
			Month: <strong><?php echo html_escape($eligibility['month']); ?></strong><br>
			Actual featured wins this month: <strong><?php echo (int) $eligibility['wins_this_month']; ?></strong><br>
			Max wins allowed this month: <strong><?php echo (int) $eligibility['max_slots']; ?></strong><br>
			Remaining opportunities: <strong><?php echo (int) $eligibility['remaining_slots']; ?></strong><br>
			Event bonus active: <strong><?php echo !empty($eligibility['has_event_bonus']) ? 'Yes' : 'No'; ?></strong>
		</div>
	<?php endif; ?>

	<p style="color:#555;">
		Blind fairness is preserved: this page never shows other users' bid values or any highest bid amount.
	</p>

	<?php if (empty($history)): ?>
		<p>No bid history yet.</p>
	<?php else: ?>
		<table style="width:100%; border-collapse:collapse;">
			<thead>
				<tr>
					<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Cycle</th>
					<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Your Bid</th>
					<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Blind Status</th>
					<th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px;">Submitted At</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($history as $row): ?>
					<tr>
						<td style="border-bottom:1px solid #f3f4f6; padding:8px;"><?php echo html_escape((string) $row['cycle_id']); ?></td>
						<td style="border-bottom:1px solid #f3f4f6; padding:8px;"><?php echo number_format((float) $row['amount'], 2); ?> <?php echo html_escape((string) $row['currency']); ?></td>
						<td style="border-bottom:1px solid #f3f4f6; padding:8px;"><?php echo strtoupper(html_escape((string) $row['blind_status'])); ?></td>
						<td style="border-bottom:1px solid #f3f4f6; padding:8px;"><?php echo html_escape((string) $row['submitted_at']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('bids/place'); ?>">Back to place bid</a> |
		<a href="<?php echo site_url('bids/status'); ?>">View current status</a>
	</p>
</section>
