The Membership plugin will keep track of projects that people have paid for.


		   ------ Website Info Form ------

wp_post (custom type lpo_site)

  x wp_user
  x post_title
  x post_name
  x post_exerpt
  x post_date

lpo_site_metadata	

  x _lpo_domain		
  x _lpo_entry_id	
  x _lpo_dropbox_account
  x _lpo_status		

  * _lpo_competition

			   Site Style Form

lpo_site_metadata	

  x _lpo_theme_provider
  x _lpo_theme_name
  * _lpo_colors
  * _lpo_fonts		
  

/*
 * Not yet (maybe never?) - Content type can be a (you guessed it) a
 * post or a page.  * they will just have additional data (parental
 * hiearchy) that * separates the proposed pages from the actual
 * website.
 */

 /*
  * Also note that actual files like graphics (logos) etc. Will be
  * contained in dropbox.
  */
  
			  Site Content Form
lpo_content		

  * _lpo_keywords
  * _lpo_categories
  * _lpo_pages
  * _lpo_menus

			   Site Users Form

lpo_users		

  x _lpo_user_first
  x _lpo_user_last
  x _lpo_user_email
  x _lpo_user_username
  x _lpo_user_password
  x _lpo_user_organization
  x _lpo_user_role
  