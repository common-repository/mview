<?php
/**
 * Name: mView
 * Description: mView plugin build for wordpress
 * Version: 1.0.1
 * Author: mufeng
 * Url: http://mufeng.me
 */

function mview_install ()
{
	global $wpdb , $mview_table_name;

	if ( !mview_table_existed () ) {
		$wpdb->query ( "CREATE TABLE {$mview_table_name} (
				id           INT(11) NOT NULL AUTO_INCREMENT,
				movie_id     INT(11) NOT NULL,
				movie_title  VARCHAR(255) NOT NULL,
				movie_cover  VARCHAR(255) NOT NULL,
				movie_rating VARCHAR(56) NOT NULL,
				movie_status VARCHAR(56) NOT NULL,
				created      DATETIME NOT NULL,
				UNIQUE KEY id (id)
			) ENGINE = mviewISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT=1" );
	}
}

function mview_table_existed ()
{
	global $wpdb , $mview_table_name;
	$existed = $wpdb->get_var ( "show tables like '{$mview_table_name}'" );

	return $existed == $mview_table_name;
}

function mview_uninstall ()
{
	global $wpdb , $mview_table_name;

	$wpdb->query ( "DROP TABLE IF EXISTS {$mview_table_name}" );
}

function mView ()
{
	global $mView;

	$data = $mView->get_by_page ( 1 );
	$mView->render_html ( $data );
}
