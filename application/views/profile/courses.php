<?php
$is_edit = !empty($edit_item);
$form_action = $is_edit ? site_url('profile/courses/update/'.(int) $edit_item['id']) : site_url('profile/courses/add');
?>

<section class="card" style="max-width:900px; margin:0 auto;">
	<h2 style="margin-top:0;">Short Courses</h2>
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

	<h3><?php echo $is_edit ? 'Edit Course' : 'Add Course'; ?></h3>
	<form method="post" action="<?php echo $form_action; ?>">
		<p><label>Title<br><input type="text" name="title" required maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('title', $is_edit ? $edit_item['title'] : '')); ?>"></label></p>
		<p><label>Provider (or website URL)<br><input type="text" name="provider" maxlength="255" style="width:100%;" value="<?php echo html_escape(set_value('provider', $is_edit ? $edit_item['provider'] : '')); ?>"></label></p>
		<p><label>Completion Date<br><input type="date" name="completed_on" value="<?php echo html_escape(set_value('completed_on', $is_edit ? $edit_item['completed_on'] : '')); ?>"></label></p>
		<p><label>Hours<br><input type="number" step="0.01" min="0" name="hours" value="<?php echo html_escape(set_value('hours', $is_edit ? $edit_item['hours'] : '')); ?>"></label></p>
		<button type="submit"><?php echo $is_edit ? 'Update Course' : 'Add Course'; ?></button>
		<?php if ($is_edit): ?>
			<a href="<?php echo site_url('profile/courses'); ?>" style="margin-left:8px;">Cancel</a>
		<?php endif; ?>
	</form>

	<hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">
	<h3>Existing Courses</h3>
	<?php if (empty($items)): ?>
		<p>No courses added yet.</p>
	<?php else: ?>
		<?php foreach ($items as $row): ?>
			<div style="padding:10px; border:1px solid #e5e7eb; border-radius:6px; margin-bottom:10px;">
				<strong><?php echo html_escape($row['title']); ?></strong>
				<?php if (!empty($row['provider'])): ?> — <?php echo html_escape($row['provider']); ?><?php endif; ?><br>
				<small>Completed: <?php echo html_escape((string) $row['completed_on']); ?> | Hours: <?php echo html_escape((string) $row['hours']); ?></small><br>
				<a href="<?php echo site_url('profile/courses/edit/'.(int) $row['id']); ?>">Edit</a>
				<form method="post" action="<?php echo site_url('profile/courses/delete/'.(int) $row['id']); ?>" style="display:inline;">
					<button type="submit" onclick="return confirm('Delete this course?');">Delete</button>
				</form>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>
