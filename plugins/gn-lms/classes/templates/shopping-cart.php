<h2><?php echo $atts['title']; ?></h2>
<div class="full_span<?php echo count($context) ? "" : " gn-empty"?>">
<?php if($this->err): ?><p class="gnlms-msg gnlms-err"><?php echo $this->err; ?></p><?php endif; ?>
<?php include("_shopping_cart_content.php"); ?>

</div>
