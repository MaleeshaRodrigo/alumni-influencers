<section class="card" style="max-width:760px; margin:0 auto;">
	<h2 style="margin-top:0;">Place Blind Bid</h2>
	<p style="color:#555; margin-top:0;">
		Bidding cycle: <strong><?php echo html_escape((string) $cycle_id); ?></strong>
	</p>
	<p style="color:#555;">
		You can place one bid per day. If you bid again today, it must be higher than your existing bid.
	</p>

	<?php if (!empty($eligibility)): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #93c5fd; background:#eff6ff; color:#1e40af; border-radius:6px;">
			Monthly wins: <strong><?php echo (int) $eligibility['wins_this_month']; ?></strong> /
			<strong><?php echo (int) $eligibility['max_slots']; ?></strong><br>
			Remaining feature opportunities this month:
			<strong><?php echo (int) $eligibility['remaining_slots']; ?></strong><br>
			Event bonus eligibility:
			<strong><?php echo !empty($eligibility['has_event_bonus']) ? 'Yes (4th slot enabled)' : 'No'; ?></strong>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('bid_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('bid_error'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->session->flashdata('bid_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('bid_success')); ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($current_bid)): ?>
		<p style="margin:0 0 12px;">
			Your current bid for today is recorded.
		</p>
	<?php endif; ?>

	<form method="post" action="<?php echo site_url('bids/store'); ?>" novalidate>
		<div style="margin-bottom:14px;">
			<label for="bid_amount" style="display:block; font-weight:bold; margin-bottom:6px;">Bid Amount</label>
			<input
				id="bid_amount"
				name="bid_amount"
				type="number"
				step="0.01"
				min="0.01"
				required
				value="<?php echo html_escape(set_value('bid_amount')); ?>"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<?php if (!empty($eligibility) && empty($eligibility['can_win_more'])): ?>
			<button type="button" disabled style="background:#9ca3af; color:#fff; border:none; padding:10px 16px; border-radius:6px;">
				Monthly win limit reached
			</button>
		<?php else: ?>
			<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
				Submit Bid
			</button>
		<?php endif; ?>
	</form>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('bids/status'); ?>">Check bid status</a>
	</p>
	<p>
		<a href="<?php echo site_url('bids/history'); ?>">View bid history</a>
	</p>
</section>
