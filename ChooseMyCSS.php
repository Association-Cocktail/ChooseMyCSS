<?php
/**
 * ChooseMyCSS Plugin for MantisBT
 * @link https://github.com/mantisbt-plugins/ChooseMyCSS
 *
 * @author    Marc-Antoine TURBET-DELOF<marc-antoine.turbet-delof@asso-cocktail.fr>
 * @copyright Copyright (c) 2020 Association Cocktail, Marc-Antoine TURBET-DELOF
 */

class ChooseMyCSSPlugin extends MantisPlugin {
	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );    # Proper name of plugin
		$this->description = plugin_lang_get( 'description' );    # Short description of the plugin
		$this->page = 'config_page';           # Default plugin page

		$this->version = '0.2.1';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
		    'MantisCore' => '2.24',  # Should always depend on an appropriate
		                            # version of MantisBT
		);

		$this->author = 'Association Cocktail';         # Author/team name
		$this->contact = 'resp-infra@asso-cocktail.fr';        # Author/team e-mail address
		$this->url = 'https://github.com/mantisbt-plugins/ChooseMyCSS';            # Support webpage
    }

	function hooks() {
        return array(
            'EVENT_LAYOUT_RESOURCES' => 'head_layout',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM' => 'account_pref',
			'EVENT_ACCOUNT_PREF_UPDATE' => 'account_update',
        );
    }

	function schema() {
		return array(
            array('CreateTableSQL', array( plugin_table('file'), "
                    id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                    title              C(32)   NOTNULL,
                    data               XL      NOTNULL,
					mandatory          L       NOTNULL"
                )
            ),
            array('CreateTableSQL', array( plugin_table('user'), "
                    user_id            I      NOTNULL UNSIGNED PRIMARY,
                    file_id            I      NOTNULL UNSIGNED"
                )
            )
		);
	}

	function get_available_css( $p_userid ) {
		$t_file_table = plugin_table('file');
		$t_user_table = plugin_table('user');
		$t_file_array = array();
		$t_query = "SELECT DISTINCT id, title, user_id
		            FROM $t_file_table
					LEFT JOIN $t_user_table
						ON $t_file_table.id = $t_user_table.file_id
						AND $t_user_table.user_id = $p_userid
					WHERE mandatory IS FALSE";
		$t_result = db_query($t_query);
		while( $t_row = db_fetch_array( $t_result ) ) {
			array_push( $t_file_array , $t_row );
		}
		return $t_file_array;
	}

	function get_user_css( $p_userid ) {
		$t_file_table = plugin_table('file');
		$t_user_table = plugin_table('user');
		$t_file_array = array();
		$t_query = "SELECT title, data
		            FROM $t_file_table
					INNER JOIN $t_user_table
						ON $t_file_table.id = $t_user_table.file_id
					WHERE mandatory IS FALSE
						AND $t_user_table.user_id = $p_userid";
		$t_result = db_query($t_query);
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_file_array[ $t_row['title'] ] = $t_row['data'];
		}
		return $t_file_array;
	}

	function get_mandatory_css() {
		$t_file_table = plugin_table('file');
		$t_file_array = array();
		$t_query = "SELECT title, data
		            FROM $t_file_table
					WHERE mandatory IS TRUE";
		$t_result = db_query($t_query);
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_file_array[ $t_row['title'] ] = $t_row['data'];
		}
		return $t_file_array;
	}

	function head_layout( $p_event ) {
		$t_mandatory_css_array = $this->get_mandatory_css();
		foreach( $t_mandatory_css_array as $title => $data ) { 
			echo '<style id="' . $title . '">' . $data . '</style>';
		}
		if( auth_is_user_authenticated() ) {
			$t_user_id = auth_get_current_user_id();
			$t_user_css_array = $this->get_user_css( $t_user_id );
			foreach( $t_user_css_array as $title => $data ) { 
				echo '<style id="' . $title . '">' . $data . '</style>';
			}
		}
	}

	/**
	 * When updating user preferences, allowing user to choose
	 * an additional CSS flie.
	 * @param string Event name
	 * @param int User ID
	 */
	function account_pref( $p_event, $p_user_id ) {
		require_api( 'helper_api.php' );

		$t_user_css_array = $this->get_available_css( $p_user_id );

		echo '<tr>'
            . '<td class="category">' . plugin_lang_get( 'pref_file_title' ) . '</td>'
			. '<td>'
			. '<select name="file_id">'
			. '<option value="0" selected>' . plugin_lang_get( 'pref_no_file' ) . '</option>';
		$t_user_css_array = $this->get_available_css( $p_user_id );
        foreach( $t_user_css_array as $t_file_array ) {
            echo '<option value=' . $t_file_array['id'];
			echo check_selected( (int)$t_file_array['user_id'], (int)$p_user_id );
			echo '>' . $t_file_array['title'] . '</option>';
        }

		echo '<select/>'
			. '</td>'
			. '</tr>';
	}

	function account_update( $p_event, $p_user_id ) {
		$f_file_id = gpc_get_int( 'file_id' );
		$t_user_table = plugin_table('user');
		$t_query = "DELETE FROM $t_user_table
					WHERE user_id = $p_user_id";
		$t_result = db_query($t_query);
    	$t_rows_affected = db_num_rows( $t_result );
		if( 0 != $f_file_id ) {
			$t_query = "INSERT INTO $t_user_table (user_id, file_id)
            		    VALUES (?, ?)";
			$t_result = db_query( $t_query, array( $p_user_id, $f_file_id ) );
		    $t_rows_affected = db_num_rows( $t_result );
		}
	}
}

