<?php
$is_edit = !empty($edit_item);
$form_action = $is_edit ? site_url('profile/licences/update/'.(int) $edit_item['id']) : site_url('profile/licences/add');
?>

<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Licences</h2>
	<p><a href="<?php echo site_url('profile/dashboard'); ?>">Back to profile dashboard</a></p>

	<?php if ($this->session->flashdata('section_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo strip_tags((string) $this->session->flashdata('section_error'), '<p><br><strong><em><ul><li>'); ?>
		</div>
	<?php endif; ?>
	<?php if ($this->session->flashdata('section_success')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #22c55e; background:#f0fdf4; color:#166534; border-radius:6px;">
			<?php echo html_escape($this->session->flashdata('section_success')); ?>
		</div>
	<?php endif; ?>

	<h3><?php echo $is_edit ? 'Edit Licence' : 'Add Licence'; ?></h3>
	<form method="post" action="<?php echo $form_action; ?>">
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<p><label>Title<br><input type="text" name="title" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('title', $is_edit ? $edit_item['title'] : '')); ?>"></label></p>
		<p><label>Issuing Body<br><input type="text" name="issuing_body" maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('issuing_body', $is_edit ? $edit_item['issuing_body'] : '')); ?>"></label></p>
		<p><label>Licence Number<br><input type="text" name="licence_number" maxlength="128" style="width:100%;" value="<?php echo html_escape(set_value('licence_number', $is_edit ? $edit_item['licence_number'] : '')); ?>"></label></p>
		<p><label>Jurisdiction<br><input type="text" name="jurisdiction" maxlength="128" style="width:100%;" value="<?php echo html_escape(set_value('jurisdiction', $is_edit ? $edit_item['jurisdiction'] : '')); ?>"></label></p>
		<p><label>Valid From<br><input type="date" name="valid_from" value="<?php echo html_escape(set_value('valid_from', $is_edit ? $edit_item['valid_from'] : '')); ?>"></label></p>
		<p><label>Valid To<br><input type="date" name="valid_to" value="<?php echo html_escape(set_value('valid_to', $is_edit ? $edit_item['valid_to'] : '')); ?>"></label></p>
		<button type="submit"><?php echo $is_edit ? 'Update Licence' : 'Add Licence'; ?></button>
		<?php if ($is_edit): ?>
			<a href="<?php echo site_url('profile/licences'); ?>" style="margin-left:8px;">Cancel</a>
		<?php endif; ?>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">
	<h3>Existing Licences</h3>
	<?php if (empty($items)): ?>
		<p>No licences added yet.</p>
	<?php else: ?>
		<?php foreach ($items as $row): ?>
			<div style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:10px;">
				<strong><?php echo html_escape($row['title']); ?></strong>
				<?php if (!empty($row['issuing_body'])): ?> — <?php echo html_escape($row['issuing_body']); ?><?php endif; ?><br>
				<small><?php echo html_escape((string) $row['valid_from']); ?> to <?php echo html_escape((string) $row['valid_to']); ?></small><br>
				<a href="<?php echo site_url('profile/licences/edit/'.(int) $row['id']); ?>">Edit</a>
				<form method="post" action="<?php echo site_url('profile/licences/delete/'.(int) $row['id']); ?>" style="display:inline;">
					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
					<button type="submit" onclick="return confirm('Delete this licence?');">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
