<?php

  /*
   * Handle the login form and log out link
   *
   * Shortcodes:
   *	- webman-login-form
   *	- webman-logout-link
   *
   */

/**
 * Create the short code to add a login form to any page or post.
 * We will just use the WordPress login form to do this.  We'll wrap
 * in <div> and a class for stylizing.  We will include a link
 * for recovering the password as well.
 *
 * If the user is already logged in, we'll return a bit of code
 * that says so and give them a chance to log out..
 */

add_shortcode( 'webman-login-form', 'webman_create_login_form');
function webman_create_login_form($atts, $content = null)
{
  $html = '';

  // set the attributes defaults and prepare to get them
  $atts = array(
		'login_redirect'	=> site_url ('/members/'),
		);

  // Get the attributes we'll be using
  extract (shortcode_atts ($atts, $atts));

  // Is our user logged in?
  $logged_in = is_user_logged_in();

  /*
   * if our user is not logged-in we'll display the login form
   * with that will redirect them to the members area (or where ever
   * we are told to send them.
   *
   * If the user is logged in, we'll let them know
   * the are and redirect them to the logged in redirect.
   * 
   */
  if ($logged_in === false) {

    // Once the user is logged in we'll will send them to this page.
    $redirect = $atts['login_redirect'];

    // Options for wp_login_form( );
    $opts = array (
		   'echo'	=> false,
		   'redirect'	=> $redirect, 
		   );

    // create some variables, simply to make the html below more readable
    $loginform_html = wp_login_form( $opts );
    $recover_url = wp_lostpassword_url( site_url ('/login/') );
    $register_url = site_url('/shop/join/');


    // ***** -------------------  Warning HTML COMMING! ---------------- ****
    ?>
      <div class="login-form-wrapper">

	 <div class="login-form"> 
	     <?php echo $loginform_html ?>

	 <div class='login-recovery-wrap'>

	   <div class='login-register'>    
	     Have not joined yet?  <a href="<?php echo $register_url ?>">Join here </a>
           </div><!-- login-register -->

	   <div class="login-recovery">
	     Lost your password? <a href="<?php echo $recover_url ?>">Recover Password</a>
	   </div><!-- login-recoevery -->

	 </div><!-- login-recoever-wrap -->

      </div> <!-- login-form -->

     </div><!-- login-form-wrapper -->

    <?php	     


  } else {

    global $current_user;
    get_currentuserinfo();
    
    /*
     * This case the user is already logged in however they do not
     * have the appropriate capability (e.g. lpo_member) to see this
     * content.  Give them a chance to logout or register.
     */

    ?> <!-- ----------------- HTML ------------------ -->
      <div class="login-form-wrapper">
	 You are already logged in as: <b><?php $current_user->user_login ?></b>
	 <br/>
	 Click here to <?php webman_create_logout_link(true); ?>
      </div><!-- login-form-wrapper -->
    <?php
  }    

}

/*
 * Create the short code to allow people to log out of the
 * site.
 */
add_shortcode( 'webman-logout-link', 'webman_create_logout_link');
function webman_create_logout_link($return = 0)
{
  $html .= '<a href="' . wp_logout_url( home_url() ) . '">Logout Now</a>';
  if ($return) {
    return $html;
  }

  return $html;
}

?>
