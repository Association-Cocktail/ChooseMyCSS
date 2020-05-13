<?php
/**
 * ChooseMyCSS Plugin for MantisBT
 * @link https://github.com/Association-cocktail/ChooseMyCSS
 *
 * @author    Marc-Antoine TURBET-DELOF<marc-antoine.turbet-delof@asso-cocktail.fr>
 * @copyright Copyright (c) 2020 Association Cocktail, Marc-Antoine TURBET-DELOF
 */

form_security_validate( 'plugin_ChooseMyCSS_config_edit' );

auth_reauthenticate( );

$f_submit_type = gpc_get_string( 'submit' );
$t_file_table = plugin_table( 'file' );
$t_user_table = plugin_table( 'user' );
$t_rows_affected = 0;

if( $f_submit_type == plugin_lang_get( 'config_new_file' ) ) {
    $f_file_data      = gpc_get_string( 'file_data' );
    $f_file_title     = gpc_get_string( 'file_title' );
    $f_file_mandatory = gpc_get_bool( 'file_mandatory' );
    $t_query = "INSERT INTO $t_file_table (title, data, mandatory)
				VALUES (?, ?, ?)";
    $t_result = db_query( $t_query, array( trim( $f_file_title ), $f_file_data, $f_file_mandatory ) );
    $t_rows_affected = db_num_rows( $t_result );
} else if( $f_submit_type == plugin_lang_get( 'config_save_file' ) ) {
    $f_file_id        = gpc_get_int( 'file_id' );
    $f_file_data      = gpc_get_string( 'file_data' );
    $f_file_title     = gpc_get_string( 'file_title' );
    $f_file_mandatory = gpc_get_bool( 'file_mandatory' );
    $t_query = "UPDATE $t_file_table
				SET title = ?,
					data  = ?,
					mandatory = ?
				WHERE id = ?";
    $t_result = db_query( $t_query, array( trim( $f_file_title ), $f_file_data, (int)$f_file_mandatory, $f_file_id ) );
    $t_rows_affected = db_num_rows( $t_result );
} else if( $f_submit_type == plugin_lang_get( 'config_delete_file' ) ) {
    $f_file_id    = gpc_get_string( 'file_id' );
    $t_query = "DELETE f.*, u.*
				FROM      $t_file_table f
				LEFT JOIN $t_user_table u
					ON u.file_id = f.id
				WHERE f.id = ?";
    $t_result = db_query( $t_query, array( $f_file_id ) );
    $t_rows_affected = db_num_rows( $t_result );
} else {
    trigger_error(ERROR_INVALID_REQUEST_METHOD, ERROR );
}

# FIXME : allways 0
#if( $t_rows_affected == 0 ) {
#    trigger_error(ERROR_DB_QUERY_FAILED, ERROR );
#}

form_security_purge( 'plugin_ChooseMyCSS_config_edit' );

$t_redirect_url = plugin_page( 'config_page', TRUE );

layout_page_header( null, $t_redirect_url );
layout_page_begin( );
html_operation_successful( $t_redirect_url );
layout_page_end( );

