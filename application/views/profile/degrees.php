<?php
$is_edit = !empty($edit_item);
$form_action = $is_edit ? site_url('profile/degrees/update/'.(int) $edit_item['id']) : site_url('profile/degrees/add');
?>

<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Degrees</h2>
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

	<h3><?php echo $is_edit ? 'Edit Degree' : 'Add Degree'; ?></h3>
	<form method="post" action="<?php echo $form_action; ?>">
		<p><label>Institution<br><input type="text" name="institution" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('institution', $is_edit ? $edit_item['institution'] : '')); ?>"></label></p>
		<p><label>Qualification<br><input type="text" name="qualification" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('qualification', $is_edit ? $edit_item['qualification'] : '')); ?>"></label></p>
		<p><label>Field of Study<br><input type="text" name="field_of_study" maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('field_of_study', $is_edit ? $edit_item['field_of_study'] : '')); ?>"></label></p>
		<p><label>Grade / Classification<br><input type="text" name="grade_or_classification" maxlength="100" style="width:100%;" value="<?php echo html_escape(set_value('grade_or_classification', $is_edit ? $edit_item['grade_or_classification'] : '')); ?>"></label></p>
		<p><label>Start Date<br><input type="date" name="started_on" value="<?php echo html_escape(set_value('started_on', $is_edit ? $edit_item['started_on'] : '')); ?>"></label></p>
		<p><label>Completion Date<br><input type="date" name="completed_on" value="<?php echo html_escape(set_value('completed_on', $is_edit ? $edit_item['completed_on'] : '')); ?>"></label></p>
		<button type="submit"><?php echo $is_edit ? 'Update Degree' : 'Add Degree'; ?></button>
		<?php if ($is_edit): ?>
			<a href="<?php echo site_url('profile/degrees'); ?>" style="margin-left:8px;">Cancel</a>
		<?php endif; ?>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">
	<h3>Existing Degrees</h3>
	<?php if (empty($items)): ?>
		<p>No degrees added yet.</p>
	<?php else: ?>
		<?php foreach ($items as $row): ?>
			<div style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:10px;">
				<strong><?php echo html_escape($row['qualification']); ?></strong> — <?php echo html_escape($row['institution']); ?><br>
				<small><?php echo html_escape((string) $row['started_on']); ?> to <?php echo html_escape((string) $row['completed_on']); ?></small><br>
				<a href="<?php echo site_url('profile/degrees/edit/'.(int) $row['id']); ?>">Edit</a>
				<form method="post" action="<?php echo site_url('profile/degrees/delete/'.(int) $row['id']); ?>" style="display:inline;">
					<button type="submit" onclick="return confirm('Delete this degree?');">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
