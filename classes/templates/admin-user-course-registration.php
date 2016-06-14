<h1>Course Registrations: <?php echo $context->first_name .' '. $context->last_name; ?></h1>
<?php

$courseLink = admin_url('admin.php?page=gnlms-course');
echo do_shortcode('[gnlms_admin_user_current_courses title="Registered Courses" link="'.$courseLink.'" hideid="1" context_key="user_id"]');
echo do_shortcode('[gnlms_data_support_form name="user-course-assignment" context="user_id"]');
echo do_shortcode('[gnlms_admin_user_completed_courses title="Completed Courses" link="'.$courseLink.'" hideid="1"  context_key="user_id"]');

?>
<p><a href="<?php echo get_edit_user_link($context->ID); ?>">User profile page...</a></p>
