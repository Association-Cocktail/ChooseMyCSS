<?php
/**
 * ChooseMyCSS Plugin for MantisBT
 * @link https://github.com/mantisbt-plugins/ChooseMyCSS
 *
 * @author	Marc-Antoine TURBET-DELOF<marc-antoine.turbet-delof@asso-cocktail.fr>
 * @copyright Copyright (c) 2020 Association Cocktail, Marc-Antoine TURBET-DELOF
 */

require_api( 'helper_api.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );
print_manage_menu( 'manage_plugin_page.php' );

$t_file_table = plugin_table('file');

$t_form_security_field  = form_security_field( 'plugin_ChooseMyCSS_config_edit' );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >

	<form id="newfile-form" method="post" action="<?php echo plugin_page('config_edit') ?>">
		<?php echo $t_form_security_field ?>
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-plus"></i>
					<?php echo plugin_lang_get('title') . ': ' . plugin_lang_get('config_new_file') ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="form-container">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-striped">
								<tr>
									<td class="category">
										<span class="required">*</span>
										<label for="new_file_title"><?php echo plugin_lang_get('new_file_title') ?></label>
									</td>
									<td>
										<input name="file_title" maxlength="30" size="30" value="" required/>
									</td>
								</tr>
								<tr>
									<td class="category">
										<span class="required">*</span>
										<label for="new_file_data"><?php echo plugin_lang_get('new_file_data') ?></label>
									</td>
									<td>
										<textarea name="file_data" cols="70" rows="5" class"form-control" required></textarea>
									</td>
								</tr>
								<tr>
									<td class="category">
										<label for="new_file_mandatory"><?php echo plugin_lang_get('new_file_mandatory') ?></label>
									</td>
									<td>
										<input type="checkbox" name="file_mandatory"/>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
	
				<div class="widget-toolbox padding-8 clearfix">
					<input type="submit" name="submit" class="btn btn-primary btn-new btn-round"
						  value="<?php echo plugin_lang_get('config_new_file') ?>" />
				</div>
	
			</div>
		</div>
	</form>
	<br><br>
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-th-list"></i>
					<?php echo plugin_lang_get('title') . ': ' . plugin_lang_get('config_existing') ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="form-container">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-striped">

								<tr class="category">
									<th><label for="config_file_title">
										<?php echo plugin_lang_get( 'config_file_title' ); ?></label></th>
									<th><label for="config_file_data">
										<?php echo plugin_lang_get( 'config_file_data' ); ?></label></th>
									<th class="center"><label for="config_file_mandatory">
										<?php echo plugin_lang_get( 'config_file_mandatory' ); ?></label></th>
									<th class="center"><label for="config_file_action">
										<?php echo plugin_lang_get( 'config_file_action' ); ?></label></th>
								</tr>

								<?php
								$i = 0;
								$t_query = "SELECT id, title, data, mandatory
											FROM $t_file_table";
								$t_result = db_query($t_query);
								while( $t_row = db_fetch_array( $t_result ) ) {
									$i++;
									extract( $t_row, EXTR_PREFIX_ALL, 'v' );
									$v_id		 = string_display_line( $v_id );
									$v_title	  = string_display_line( $v_title );
									$v_data	   = string_display_line( $v_data );
									$v_mandatory  = string_display_line( $v_mandatory );
								?>
								<form id="editfile-form-<?php echo $v_id; ?>" method="post" action="<?php echo plugin_page('config_edit') ?>">
									<?php echo $t_form_security_field ?>
									<tr>
										<input type="hidden" name="file_id" value="<?php echo $v_id; ?>" />
										<td>
											<input name="file_title" maxlength="30" size="30" value="<?php echo $v_title; ?>" />
										</td>
										<td>
											<textarea name="file_data" cols="70" rows="5" class="form-control" required><?php echo $v_data; ?></textarea>
										</td>
										<td class="center">
											<input type="checkbox" name="file_mandatory"
												<?php check_checked( (int)$v_mandatory, ON ); ?> />
										</td>
										<td class="center">
											<?php
												echo '<input type="submit" name="submit" '
													. 'class="btn btn-primary btn-send btn-round btn-xs" value="'
													. plugin_lang_get('config_save_file') . '" />';
												echo '&#160;';
												echo '/';
												echo '&#160;';
												echo '<input type="submit" name="submit" '
													. 'class="btn btn-primary btn-remove btn-round btn-xs" value="'
													. plugin_lang_get('config_delete_file') . '" />';
											?>
										</td>
									</tr>
								</form>
								<?php
								} # end for loop

								if( $i == 0 ) {
									echo '<tr><td colspan="2">'. plugin_lang_get('no_files_configured').'</td></tr>';
								} ?>
							</table>
						</div>
					</div>
				</div>

				<div class="widget-toolbox padding-8 clearfix"></div>
			</div>
		</div>
	</div>
	<div class="space-10"></div>
</div>


<?php
layout_page_end();

