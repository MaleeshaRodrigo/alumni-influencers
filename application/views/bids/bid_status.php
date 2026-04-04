<section class="card" style="max-width:760px; margin:0 auto;">
	<h2 style="margin-top:0;">Blind Bid Status</h2>
	<p style="color:#555; margin-top:0;">
		Bidding cycle: <strong><?php echo html_escape((string) $cycle_id); ?></strong>
	</p>

	<?php if (!empty($bid_status['has_bid'])): ?>
		<?php if ($bid_status['status'] === 'winning'): ?>
			<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
				You are currently <strong>WINNING</strong>.
			</div>
		<?php else: ?>
			<div style="margin:12px 0; padding:10px; border:1px solid #f59e0b; background:#fffbeb; color:#92400e; border-radius:6px;">
				You are currently <strong>LOSING</strong>.
			</div>
		<?php endif; ?>
	<?php else: ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #93c5fd; background:#eff6ff; color:#1e40af; border-radius:6px;">
			You have not placed a bid for today yet.
		</div>
	<?php endif; ?>

	<p style="color:#555;">
		For blind bidding fairness, bid amounts are never displayed here.
	</p>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('bids/place'); ?>">Place or increase your bid</a>
	</p>
</section>
