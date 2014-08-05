<div class="gnlms-shopping-cart-content">
<?php if(count($context)): 
	$action = "gnlms_shopping_cart_remove";
	$actionText = "Remove";
	$formFile = dirname(dirname(__FILE__))."/forms/_shopping_cart_update.php"
?>
	<ul class="gnlms-shopping-cart">
	<?php foreach($context as $course): $id = $course->id; ?>
		<li class="gnlms-course-title">
			<a href="/course-detail/?id=<?php echo $course->id; ?>"><?php echo htmlspecialchars($course->title); ?></a>
			<?php include($formFile); ?>
		</li>
	<?php endforeach; ?>
	</ul>
<p><a href="/checkout/">Proceed to checkout</a></p>
<?php else: ?>
<p>No courses selected.</p>
<?php endif; ?>
</div>
