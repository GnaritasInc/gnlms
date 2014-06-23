<?php if (!(current_user_can('level_0'))){ ?>
<h2>Login</h2>
<div class="padding">
<form id="login-form" action="<?php echo get_option('home'); ?>/user-account/" method="post">

    <p><label for="log">Email Address</label><input type="text" name="log" id="log" value="<?php echo wp_specialchars(stripslashes($user_login), 1) ?>" size="20" /> </p>

    <p><label for="pwd">Password</label><input type="password" name="pwd" id="pwd" size="20" /></p>

    <p align="right"><input id="login-button" type="submit" name="submit" value="Login" class="gnlms-button" /></p>

    <p>
       <label id="login-remember" for="rememberme"><input name="rememberme" id="rememberme" type="checkbox"  value="forever" /> Remember me</label>
       <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
    </p>
</form>

<ul style="margin-top:5px; margin-bottom:5px">
<li>
<a href="<?php echo get_option('home'); ?>/wp-register.php">Register</a><span style="width:40px"></span>
</li>
<li>
<a href="<?php echo get_option('home'); ?>/wp-login.php?action=lostpassword">Recover password</a>
</li>
</ul>
<?php } else { ?>
        <!--<ul class="admin_box">

            <li><a href="<?php echo wp_logout_url(get_option('siteurl'));?>">logout</a></li>
        </ul>
	-->

</div>
<?php }?>