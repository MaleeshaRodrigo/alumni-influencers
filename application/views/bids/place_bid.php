<section class="card" style="max-width:760px; margin:0 auto;">
	<h2 style="margin-top:0;">Place Blind Bid</h2>
	<p style="color:#555; margin-top:0;">
		Bidding cycle: <strong><?php echo html_escape((string) $cycle_id); ?></strong>
	</p>
	<p style="color:#555;">
		You can place one bid per day. If you bid again today, it must be higher than your existing bid.
	</p>

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

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Submit Bid
		</button>
	</form>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('bids/status'); ?>">Check bid status</a>
	</p>
</section>
