<?php
$is_edit = !empty($edit_item);
$form_action = $is_edit ? site_url('profile/certifications/update/'.(int) $edit_item['id']) : site_url('profile/certifications/add');
?>

<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Certifications</h2>
	<p><a href="<?php echo site_url('profile/dashboard'); ?>">Back to profile dashboard</a></p>

	<?php if ($this->session->flashdata('section_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('section_error'); ?>
		</div>
	<?php endif; ?>
	<?php if ($this->session->flashdata('section_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('section_success')); ?>
		</div>
	<?php endif; ?>

	<h3><?php echo $is_edit ? 'Edit Certification' : 'Add Certification'; ?></h3>
	<form method="post" action="<?php echo $form_action; ?>">
		<p><label>Name<br><input type="text" name="name" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('name', $is_edit ? $edit_item['name'] : '')); ?>"></label></p>
		<p><label>Issuer<br><input type="text" name="issuer" maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('issuer', $is_edit ? $edit_item['issuer'] : '')); ?>"></label></p>
		<p><label>Credential ID / URL<br><input type="text" name="credential_id" maxlength="128" style="width:100%;" value="<?php echo html_escape(set_value('credential_id', $is_edit ? $edit_item['credential_id'] : '')); ?>"></label></p>
		<p><label>Issued Date<br><input type="date" name="issued_on" value="<?php echo html_escape(set_value('issued_on', $is_edit ? $edit_item['issued_on'] : '')); ?>"></label></p>
		<p><label>Expiry Date<br><input type="date" name="expires_on" value="<?php echo html_escape(set_value('expires_on', $is_edit ? $edit_item['expires_on'] : '')); ?>"></label></p>
		<button type="submit"><?php echo $is_edit ? 'Update Certification' : 'Add Certification'; ?></button>
		<?php if ($is_edit): ?>
			<a href="<?php echo site_url('profile/certifications'); ?>" style="margin-left:8px;">Cancel</a>
		<?php endif; ?>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">
	<h3>Existing Certifications</h3>
	<?php if (empty($items)): ?>
		<p>No certifications added yet.</p>
	<?php else: ?>
		<?php foreach ($items as $row): ?>
			<div style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:10px;">
				<strong><?php echo html_escape($row['name']); ?></strong>
				<?php if (!empty($row['issuer'])): ?> — <?php echo html_escape($row['issuer']); ?><?php endif; ?><br>
				<small><?php echo html_escape((string) $row['issued_on']); ?> to <?php echo html_escape((string) $row['expires_on']); ?></small><br>
				<a href="<?php echo site_url('profile/certifications/edit/'.(int) $row['id']); ?>">Edit</a>
				<form method="post" action="<?php echo site_url('profile/certifications/delete/'.(int) $row['id']); ?>" style="display:inline;">
					<button type="submit" onclick="return confirm('Delete this certification?');">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
