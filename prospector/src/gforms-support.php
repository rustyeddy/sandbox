<?php

require_once( 'logger.php' );
require_once( 'mini-audits.php' );


/*
 * Handle some GForms things
 */
function lpop_gforms_init()
{
    global $lpop_miniaudit_upload_form_id;

    $upload_callback = 'gform_after_submission_' . $lpop_miniaudit_upload_form_id;
    add_action( $upload_callback, 'lpop_load_mini_audits', 10, 2);
}

/**
 * This function is called as a result of the mini audit upload
 * form being called.
 *
 * This function will unzip the uploaded file.  Parse the file name to
 * determine the keyword and processing date.
 *
 * It will unzip the uploaded file, store them into the corresponding
 * destination folder.
 *
 * It will then discover all .csv files and html files and it will
 * then parse the .html files and create a single mini_audit post type
 * for every uploaded mini audit.
 */
function lpop_load_mini_audits( $entry, $form )
{
    $fname = $form['title'];
    if ( $fname != "Mini Audit Uploader" ) {		/* XXX - Make this configurable! */
	echo "HMMM: I don't know how to process form: $fid<br/>";
	return;
    }

    /*
     * Process the incoming leads
     */
    LPOP_Logger::logit ( "Loading Mini Audits: Form =  " .
			 $fname . ", Entry = " . $entry['id'] );
    LPOP_Mini_Audits::process_mini_audit_entry( $entry );
    LPOP_Logger::flush();
}

/*
 * This is used for testing
 */
function lpop_gform_get_entry ( $attrs )
{
    /* Parse the id */
    $entry = RGFormsModel::get_lead( 1 );
    $form  = GFFormsModel::get_form_meta( $entry['form_id'] );

    LPOP_Logger::logit ( "Loading Mini Audits: Form =  " .
			 $form['title'] . ", Entry = " . $entry['id'] );
    LPOP_Mini_Audits::process_mini_audit_entry( $entry );
    LPOP_Logger::flush();
}

?>