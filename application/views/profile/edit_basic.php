<?php
$existing_full_name = isset($profile['display_name']) ? $profile['display_name'] : '';
$existing_bio = isset($profile['bio']) ? $profile['bio'] : '';
$existing_linkedin = isset($profile['linkedin_url']) ? $profile['linkedin_url'] : '';
$existing_photo = isset($profile['photo_path']) ? $profile['photo_path'] : '';
?>

<section class="card" style="max-width:760px; margin:0 auto;">
	<h2 style="margin-top:0;">Edit Basic Profile</h2>
	<p style="color:#555; margin-top:0;">
		Update your name, bio, and LinkedIn profile link.
	</p>

	<?php if ($this->session->flashdata('profile_error')): ?>
		<div style="margin:12px 0; padding:10px; border:1px solid #ef4444; background:#fef2f2; color:#991b1b; border-radius:6px;">
			<?php echo $this->session->flashdata('profile_error'); ?>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo site_url('profile/save-basic'); ?>" enctype="multipart/form-data" novalidate>
		<div style="margin-bottom:14px;">
			<label for="full_name" style="display:block; font-weight:bold; margin-bottom:6px;">Full Name</label>
			<input
				id="full_name"
				name="full_name"
				type="text"
				required
				maxlength="150"
				value="<?php echo html_escape(set_value('full_name', $existing_full_name)); ?>"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<div style="margin-bottom:14px;">
			<label for="bio" style="display:block; font-weight:bold; margin-bottom:6px;">Bio</label>
			<textarea
				id="bio"
				name="bio"
				rows="6"
				maxlength="5000"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			><?php echo html_escape(set_value('bio', $existing_bio)); ?></textarea>
		</div>

		<div style="margin-bottom:18px;">
			<label for="linkedin_url" style="display:block; font-weight:bold; margin-bottom:6px;">LinkedIn URL</label>
			<input
				id="linkedin_url"
				name="linkedin_url"
				type="url"
				maxlength="512"
				placeholder="https://www.linkedin.com/in/your-handle"
				value="<?php echo html_escape(set_value('linkedin_url', $existing_linkedin)); ?>"
				style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:6px;"
			>
		</div>

		<div style="margin-bottom:18px;">
			<label for="profile_image" style="display:block; font-weight:bold; margin-bottom:6px;">Profile Image</label>
			<?php if (!empty($existing_photo)): ?>
				<p style="margin:0 0 8px;">
					<img src="<?php echo base_url($existing_photo); ?>" alt="Current profile image" style="max-width:140px; max-height:140px; border-radius:8px; border:1px solid #d1d5db;">
				</p>
			<?php endif; ?>
			<input
				id="profile_image"
				name="profile_image"
				type="file"
				accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
				style="width:100%;"
			>
			<small style="color:#555;">Allowed: JPG, PNG, WEBP. Max size: 2MB.</small>
		</div>

		<button type="submit" style="background:#1f2937; color:#fff; border:none; padding:10px 16px; border-radius:6px; cursor:pointer;">
			Save Basic Profile
		</button>
	</form>

	<p style="margin-top:16px;">
		<a href="<?php echo site_url('profile/dashboard'); ?>">Back to dashboard</a>
	</p>
</section>
