<h2><?php echo htmlspecialchars($atts['title']); ?></h2>
<div class="gnlms-checkout full_span">

<?php if($selectedCourses): 
	$totalCost = 0; 
	$action="gnlms_shopping_cart_remove";
	$actionText = "Remove";
?>
<h3>Selected Courses</h3>
<table class="gnlms-shopping-cart">
<tr><th>Title</th><th>Price</th><th>&nbsp;</th></tr>
<?php foreach($selectedCourses as $course): $id=$course->id; $price = $this->getCoursePrice($course->id, $userID); $totalCost += $price; ?>
	<tr><td><?php echo htmlspecialchars($course->title); ?></td><td><?php echo "$".number_format($price, 2); ?></td><td><?php include("_shopping_cart_update.php"); ?></td></tr>
<?php endforeach; ?>
<tr><th>Total:</th><th><?php echo "$".number_format($totalCost, 2); ?></th><th>&nbsp;</th></tr>
</table>

<?php include($formFile); ?>

<?php else: ?>
<p>Your shopping cart is empty.</p>
<?php endif; ?>
</div>