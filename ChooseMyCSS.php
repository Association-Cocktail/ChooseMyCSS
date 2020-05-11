<?php
/**
 * ChooseMyCSS Plugin for MantisBT
 * @link https://github.com/Association-cocktail/ChooseMyCSS
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

		$this->version = '0.1';     # Plugin version string
		$this->requires = array(    # Plugin dependencies
		    'MantisCore' => '2.24',  # Should always depend on an appropriate
		                            # version of MantisBT
		);

		$this->author = 'Association Cocktail';         # Author/team name
		$this->contact = 'resp-infra@asso-cocktail.fr';        # Author/team e-mail address
		$this->url = 'https://asso-cocktail.fr';            # Support webpage
    }

	function hooks() {
        return array(
            'EVENT_LAYOUT_RESOURCES' => 'head_layout',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM' => 'account_pref'
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
		$t_user_id = auth_get_current_user_id();

		$t_mandatory_css_array = $this->get_mandatory_css();
		foreach( $t_mandatory_css_array as $title => $data ) { 
			echo '<style id="' . $title . '">' . $data . '</style>';
		}
		$t_user_css_array = $this->get_user_css( $t_user_id );
		foreach( $t_user_css_array as $title => $data ) { 
			echo '<style id="' . $title . '">' . $data . '</style>';
		}
	}

	function account_pref() {
		return null;
	}
}

