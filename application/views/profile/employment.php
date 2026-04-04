<?php
$is_edit = !empty($edit_item);
$form_action = $is_edit ? site_url('profile/employment/update/'.(int) $edit_item['id']) : site_url('profile/employment/add');
$default_current = $is_edit ? ((int) $edit_item['is_current'] === 1) : FALSE;
?>

<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Employment History</h2>
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

	<h3><?php echo $is_edit ? 'Edit Employment' : 'Add Employment'; ?></h3>
	<form method="post" action="<?php echo $form_action; ?>">
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
		<p><label>Employer<br><input type="text" name="employer" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('employer', $is_edit ? $edit_item['employer'] : '')); ?>"></label></p>
		<p><label>Job Title<br><input type="text" name="job_title" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('job_title', $is_edit ? $edit_item['job_title'] : '')); ?>"></label></p>
		<p><label>Location<br><input type="text" name="location" maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('location', $is_edit ? $edit_item['location'] : '')); ?>"></label></p>
		<p><label>Start Date<br><input type="date" name="started_on" value="<?php echo html_escape(set_value('started_on', $is_edit ? $edit_item['started_on'] : '')); ?>"></label></p>
		<p><label>End Date<br><input type="date" name="ended_on" value="<?php echo html_escape(set_value('ended_on', $is_edit ? $edit_item['ended_on'] : '')); ?>"></label></p>
		<p>
			<label>
				<input type="checkbox" name="is_current" value="1" <?php echo set_checkbox('is_current', '1', $default_current); ?>>
				Currently working here
			</label>
		</p>
		<p><label>Description<br><textarea name="description" rows="5" style="width:100%;"><?php echo html_escape(set_value('description', $is_edit ? $edit_item['description'] : '')); ?></textarea></label></p>
		<button type="submit"><?php echo $is_edit ? 'Update Employment' : 'Add Employment'; ?></button>
		<?php if ($is_edit): ?>
			<a href="<?php echo site_url('profile/employment'); ?>" style="margin-left:8px;">Cancel</a>
		<?php endif; ?>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">
	<h3>Existing Employment Records</h3>
	<?php if (empty($items)): ?>
		<p>No employment records added yet.</p>
	<?php else: ?>
		<?php foreach ($items as $row): ?>
			<div style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:10px;">
				<strong><?php echo html_escape($row['job_title']); ?></strong> — <?php echo html_escape($row['employer']); ?><br>
				<small><?php echo html_escape((string) $row['started_on']); ?> to <?php echo (int) $row['is_current'] === 1 ? 'Present' : html_escape((string) $row['ended_on']); ?></small><br>
				<a href="<?php echo site_url('profile/employment/edit/'.(int) $row['id']); ?>">Edit</a>
				<form method="post" action="<?php echo site_url('profile/employment/delete/'.(int) $row['id']); ?>" style="display:inline;">
					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
					<button type="submit" onclick="return confirm('Delete this employment record?');">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
