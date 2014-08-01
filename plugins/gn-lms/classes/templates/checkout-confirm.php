<h2><?php echo $atts['title']; ?></h2>
<div class="gnlms-checkout-confirm full_span">
<p>You have registered for the following courses:</p>
<ul>
<?php foreach($context as $course): ?>
<li><?php echo htmlspecialchars($course->title);?></li>
<?php endforeach; ?>
</ul>
<?php echo apply_filters("gnlms_checkout_confirm_message", ""); ?>
<p>Thank you!</p>
<p><a href="/">Back to My Dashboard</a></p>
</div>