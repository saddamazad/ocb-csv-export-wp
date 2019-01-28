<?php
/**
 * Plugin Name: Osky Community Band
 * Plugin URI: http://oskyblue.com
 * Version: 1.0
 * Description: Osky Community Band
 * Author: Osky Blue
 * Author URI: http://www.oskyblue.com
**/

// Define contants
define('OCB_ROOT', dirname(__FILE__));
define('OCB_URL', plugins_url('/', __FILE__));
define('OCB_HOME', home_url('/'));

require_once( OCB_ROOT . '/include/ocb-functions.php');
require_once( OCB_ROOT . '/include/ocb-meta-box-config.php');
require_once( OCB_ROOT . '/include/ocb-shortcodes.php');
require_once( OCB_ROOT . '/include/ocb-shortcodes-profile-update.php');
require_once( OCB_ROOT . '/include/ocb-email-reminders.php');

class OskyCommunityBand{	
    /**
     * Constructor 
     */
    public function __construct() {    		
		/* Init Custom Post and Taxonomy Types */
		register_activation_hook(__FILE__, array($this, 'default_data_load_for_activation'));
		
        /* Init Custom Post and Taxonomy Types */
        add_action('init', array(&$this, 'register_ocb_custom_post'));
		
        /* Init Custom Post and Taxonomy Types */
        add_action('init', array(&$this, 'cmb_initialize_cmb_meta_boxes'), 9999);
		
		/* Add admin menu */
		add_action('admin_menu', array(&$this, 'ocb_settings_page'));
		
		/*Add script for this plugin*/
		add_action('wp_enqueue_scripts', array(&$this, 'load_ocb_scripts'));
		
		/*Member profile detils page*/
		add_filter('single_template', array(&$this, 'get_ocb_members_post_type_template'));
    }
	
    /**
     * Plugins settings page
     */
    public function ocb_settings_page() {
		add_submenu_page( 'edit.php?post_type=ocbmembers', 'Import', 'Import', 'manage_options', 'ocb-import', array(&$this, 'ocb_import_plug_page'));
		add_submenu_page( 'edit.php?post_type=ocbmembers', 'Email', 'Email', 'manage_options', 'ocb-email', array(&$this, 'ocb_email_plug_page'));
		add_submenu_page( 'edit.php?post_type=ocbmembers', 'Settings', 'Settings', 'manage_options', 'ocb-settings', array(&$this, 'ocb_settings_plug_page'));
	}
	
    /**
     * Plugins import page
     */
    public function ocb_import_plug_page() {
		require_once( OCB_ROOT . '/include/ocb-import.php');
	}			

    /**
     * Plugins settings page
     */
    public function ocb_settings_plug_page() {
		require_once( OCB_ROOT . '/include/ocb-settings.php');
	}			

    /**
     * Plugins Email page
     */
    public function ocb_email_plug_page() {
		require_once( OCB_ROOT . '/include/ocb-email.php');
	}			
	
    /**
     * Load default plugin data
     */
    public function default_data_load_for_activation() {
		$this->register_ocb_custom_post();
		require_once( OCB_ROOT . '/include/ocb-activation.php');	
	}	
	
    /**
     * Load default custom post type for osky community band member info
     */
    public function register_ocb_custom_post() {
		require_once( OCB_ROOT . '/include/ocb-custom-post-type.php');			
	}
	
	/**
	 * Initialize the metabox class.
	 */
	public function cmb_initialize_cmb_meta_boxes() {
		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once( OCB_ROOT . '/assets/CMB/init.php');	
	}	
	
	/**
	 * Initialize the metabox class.
	 */
	public function get_ocb_members_post_type_template($single_template) {
		 global $post;
	
		 if ($post->post_type == 'ocbmembers') {
			  $single_template = dirname( __FILE__ ) . '/templates/single-ocbmembers.php';
		 }
		 return $single_template;
	}	
	
	
	/**
	 * Load OCB script.
	 */
	public function load_ocb_scripts() {
		wp_enqueue_style( 'style-ocb', plugins_url( '/assets/ocb-style.css', __FILE__ ) );
	}

}
// eof class


add_action( 'ocb_email_reminders', 'send_remainder_email_to_members' );
function send_remainder_email_to_members() {
	$today = date('m/d/Y');
	$date = strtotime($today);
	$date = strtotime('+'.get_option('ocb_when_to_send').' day', $date);
	$next_event_date = date('m/d/Y', $date);
	
	$event_args = array(
				'post_type' => 'ocbevents',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_key' => '_ocb_event_date',
				'meta_value' => $next_event_date,
				'meta_compare' => '='
			);
	$event_posts = new WP_Query( $event_args );	
	if($event_posts->have_posts()) {
		global $post;
		$event_ids = array();
		while($event_posts->have_posts()): $event_posts->the_post();
			$event_ids[] = $post->ID;
		endwhile;
		wp_reset_postdata();
	}

	if( isset($event_ids) && (sizeof($event_ids) > 0) ) {
		foreach($event_ids as $event_id) {
			//$email_date = date('m/d/Y', strtotime('-'.get_option('ocb_when_to_send').' day', strtotime($event_date)));
			$event_ensembles = get_post_meta($event_id, '_ocb_event_ensembles', true);
			$args = array(
						'post_type' => 'ocbmembers',
						'meta_key' => '_remainder_email_unsubscribe_member',
						'meta_value' => 'yes',
						'meta_compare' => 'NOT EXISTS',
						'tax_query' => array(
							array(
							  'taxonomy' => 'memberensembles',
							  'field' => 'term_id',
							  'terms' => $event_ensembles[0]
							)
						)
					);
			$member_posts = new WP_Query( $args );
			if($member_posts->have_posts()) {
				global $post;
				while($member_posts->have_posts()): $member_posts->the_post();
	
					$user_id = get_post_meta($post->ID, '_ocb_user_id', true);
					$user_info = get_userdata($user_id);
					$user_email = $user_info->user_email;
					$email_subject = 'Reminder for Upcoming Event';
					
					$search = array();
					$replace = array();
				
					$search[] = '%%name%%';
					$replace[] = get_the_title();
	
					$message = '';
					$message .= get_option('reminder_email_message');
					$message .= '<br /><strong>=======</strong>';
					$message .= '<p><strong>Time: </strong>'.$next_event_date.' &nbsp;<strong>From </strong>'.get_post_meta($event_id, '_ocb_event_start_time', true).' - '.get_post_meta($event_id, '_ocb_event_end_time', true).'</p>';
					$message .= '<p><strong>Location: </strong>'.get_post_meta($event_id, '_ocb_event_location', true).'</p>';
	
					$content_event = get_post($event_id);
					$content_event_text = $content_event->post_content;
					$content_event_text = apply_filters('the_content', $content_event_text);
					$message .= '<p><strong>Description: </strong>'.$content_event_text.'</p>';
					$message .= '<a href="'.esc_url( home_url( '/?member_id='.$post->ID.'&user_action=unsubscribe' ) ).'">Unsubscribe</a> from reminder emails';
					
					$filtered_message = str_replace($search, $replace, $message);
	
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	
					@wp_mail( $user_email, $email_subject, $filtered_message, $headers );
	
				endwhile;
				wp_reset_postdata();
			}
		}
	}
}

// send test email
function send_test_event_remainder_email() {
	if( isset($_GET['send_reminder_email']) && ($_GET['send_reminder_email'] == 'yes') ) {
		send_remainder_email_to_members();
	}
}
add_action('wp_loaded', 'send_test_event_remainder_email');



add_action( 'ocb_missed_rehearsal_email_reminders', 'send_missed_rehearsal_email_remainder_to_members' );
function send_missed_rehearsal_email_remainder_to_members() {
	$today = date('m/d/Y');
	//$date = strtotime($today);
	//$date = strtotime('+'.get_option('ocb_when_to_send').' day', $date);
	//$next_event_date = date('m/d/Y', $date);

	$curr_hour = date('h:i A'); //date('g:i')
	$event_end_hour = date('h', strtotime($curr_hour) - 60 * 60 * get_option('ocb_mr_when_to_send'));
	//echo $event_end_time;
	
	$event_args = array(
				'post_type' => 'ocbevents',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => '_ocb_event_end_time',
						'value'   => '^'.$event_end_hour,
						'compare' => 'REGEXP',
					),
					array(
						'key'     => '_ocb_event_date',
						'value'   => $today,
						'compare' => '=',
					),
				),
			);
	$event_posts = new WP_Query( $event_args );
	if($event_posts->have_posts()) {
		global $post;
		$event_ids = array();
		while($event_posts->have_posts()): $event_posts->the_post();
			$event_ids[] = $post->ID;
		endwhile;
		wp_reset_postdata();
	}

	if( isset($event_ids) && (sizeof($event_ids) > 0) ) {
		foreach($event_ids as $event_id) {
			$event_ensembles = get_post_meta($event_id, '_ocb_event_ensembles', true);
			$args = array(
						'post_type' => 'ocbmembers',
						'tax_query' => array(
							array(
							  'taxonomy' => 'memberensembles',
							  'field' => 'term_id',
							  'terms' => $event_ensembles[0]
							)
						)
					);
			$member_posts = new WP_Query( $args );	
			if($member_posts->have_posts()) {
				global $post;
				while($member_posts->have_posts()): $member_posts->the_post();
	
					$user_id = get_post_meta($post->ID, '_ocb_user_id', true);
					$user_info = get_userdata($user_id);
					$user_email = $user_info->user_email;
					$email_subject = 'Missed Rehearsal Email';
					
					$search = array();
					$replace = array();
				
					$search[] = '%%name%%';
					$replace[] = get_the_title();
	
					$message = '';
					$message .= get_option('missed_rehearsal_email_message');
					$message .= '<br /><strong>=======</strong>';
					$message .= '<p><strong>Time: </strong>'.$today.' &nbsp;<strong>From </strong>'.get_post_meta($event_id, '_ocb_event_start_time', true).' - '.get_post_meta($event_id, '_ocb_event_end_time', true).'</p>';
					$message .= '<p><strong>Location: </strong>'.get_post_meta($event_id, '_ocb_event_location', true).'</p>';
	
					$content_event = get_post($event_id);
					$content_event_text = $content_event->post_content;
					$content_event_text = apply_filters('the_content', $content_event_text);
					$message .= '<p><strong>Description: </strong>'.$content_event_text.'</p>';
					
					$filtered_message = str_replace($search, $replace, $message);
	
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	
					@wp_mail( $user_email, $email_subject, $filtered_message, $headers );
	
				endwhile;
				wp_reset_postdata();
			}
		}
	}
}

// send test email
function send_test_event_missed_rehearsal_email() {
	if( isset($_GET['send_event_missed_rehearsal_email']) && ($_GET['send_event_missed_rehearsal_email'] == 'yes') ) {
		send_missed_rehearsal_email_remainder_to_members();
	}
}
add_action('wp_loaded', 'send_test_event_missed_rehearsal_email');


function unsubscribe_member_from_remainder_email() {
	if(isset($_GET['member_id']) && isset($_GET['user_action']) && ($_GET['user_action'] == 'unsubscribe')) {
		$member_id = $_GET['member_id'];
		update_post_meta($member_id, '_remainder_email_unsubscribe_member', 'yes');
?>
		<script type="text/javascript">
			window.location = "<?php echo home_url( '/' ) . '?member_status=unsubscribed'; ?>";
		</script>
<?php
	}

	if( isset($_GET['member_status']) && $_GET['member_status'] == 'unsubscribed' ) {
		echo '<p style="font-size:20px; text-align:center;">Successfully Unsubscribed</p>';
	}
}
add_action('init', 'unsubscribe_member_from_remainder_email');


add_action('admin_init', 'download_csv_file');
function download_csv_file() {
	if(isset($_POST['download_csv'])){
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename=members-export.csv');

		$meta_fields = $_POST['ocb_member_fields'];
		
		// create a file pointer connected to the output stream
		if( is_dir(WP_CONTENT_DIR . '/ocb-csv') ) {
			$abs_file_url = WP_CONTENT_DIR . '/ocb-csv/members-export.csv';
		} else {
			mkdir(WP_CONTENT_DIR . '/ocb-csv', 0700);
			$abs_file_url = WP_CONTENT_DIR . '/ocb-csv/members-export.csv';
		}
		//$output = fopen($abs_file_url, 'w');

		$output = fopen('php://output', 'w');
		//$contentUrl = content_url();
		
		// output the column headings
		$columnArr = array();
		$columnArr[] = 'Name';
		if( in_array("_ocb_short_description", $meta_fields) ) {
			$columnArr[] = 'Short Description';
		}
		if( in_array("_ocb_cell_phone", $meta_fields) ) {
			$columnArr[] = 'Cell Phone';
		}
		if( in_array("_ocb_cell_phone_public", $meta_fields) ) {
			$columnArr[] = 'Cell Phone Display Public?';
		}
		if( in_array("_ocb_address", $meta_fields) ) {
			$columnArr[] = 'Address';
		}
		if( in_array("_ocb_address_public", $meta_fields) ) {
			$columnArr[] = 'Address Display Public?';
		}
		if( in_array("_ocb_birthplace", $meta_fields) ) {
			$columnArr[] = 'Birthplace';
		}
		if( in_array("_ocb_birthplace_public", $meta_fields) ) {
			$columnArr[] = 'Birthplace Display Public?';
		}
		if( in_array("_ocb_favorite_animal", $meta_fields) ) {
			$columnArr[] = 'Favorite Animal';
		}
		if( in_array("_ocb_favorite_animal_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Animal Display Public?';
		}
		if( in_array("_ocb_favorite_car", $meta_fields) ) {
			$columnArr[] = 'Favorite Car';
		}
		if( in_array("_ocb_favorite_car_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Car Display Public?';
		}
		if( in_array("_ocb_musical_hero", $meta_fields) ) {
			$columnArr[] = 'Musical Hero';
		}
		if( in_array("_ocb_musical_hero_public", $meta_fields) ) {
			$columnArr[] = 'Musical Hero Display Public?';
		}
		if( in_array("_ocb_favorite_composer", $meta_fields) ) {
			$columnArr[] = 'Favorite Composer';
		}
		if( in_array("_ocb_favorite_composer_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Composer Display Public?';
		}
		if( in_array("_ocb_favorite_instrument", $meta_fields) ) {
			$columnArr[] = 'Favorite Instrument';
		}
		if( in_array("_ocb_favorite_instrument_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Instrument Display Public?';
		}
		if( in_array("_ocb_favorite_color", $meta_fields) ) {
			$columnArr[] = 'Favorite Color';
		}
		if( in_array("_ocb_favorite_color_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Color Display Public?';
		}
		if( in_array("_ocb_you_grew_up", $meta_fields) ) {
			$columnArr[] = 'What did you want to be when you grew up?';
		}
		if( in_array("_ocb_you_grew_up_public", $meta_fields) ) {
			$columnArr[] = 'Grew Up Display Public?';
		}
		if( in_array("_ocb_current_occupation", $meta_fields) ) {
			$columnArr[] = 'Current Occupation';
		}
		if( in_array("_ocb_current_occupation_public", $meta_fields) ) {
			$columnArr[] = 'Current Occupation Display Public?';
		}
		if( in_array("_ocb_you_laugh_the_most", $meta_fields) ) {
			$columnArr[] = 'What makes you laugh the most?';
		}
		if( in_array("_ocb_you_laugh_the_most_public", $meta_fields) ) {
			$columnArr[] = 'Laugh The Most Display Public?';
		}
		if( in_array("_ocb_motivates_you_to_work", $meta_fields) ) {
			$columnArr[] = 'What motivates you to work hard?';
		}
		if( in_array("_ocb_motivates_you_to_work_public", $meta_fields) ) {
			$columnArr[] = 'Work Hard Display Public?';
		}
		if( in_array("_ocb_proudest_accomplishment", $meta_fields) ) {
			$columnArr[] = 'Proudest Accomplishment?';
		}
		if( in_array("_ocb_proudest_accomplishment_public", $meta_fields) ) {
			$columnArr[] = 'Proudest Accomplishment Display Public?';
		}

		if( in_array("_ocb_favorite_sport", $meta_fields) ) {
			$columnArr[] = 'Favorite Sport?';
		}
		if( in_array("_ocb_favorite_sport_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Sport Display Public?';
		}
		if( in_array("_ocb_only_eat_one_meal", $meta_fields) ) {
			$columnArr[] = 'If you could only eat one meal for the rest of your life, what would it be?';
		}
		if( in_array("_ocb_only_eat_one_meal_public", $meta_fields) ) {
			$columnArr[] = 'Only Eat One Meal Display Public?';
		}
		if( in_array("_ocb_favorite_author", $meta_fields) ) {
			$columnArr[] = 'Favorite Author?';
		}
		if( in_array("_ocb_favorite_author_public", $meta_fields) ) {
			$columnArr[] = 'Favorite Author Display Public?';
		}
		if( in_array("_ocb_biggest_animal_fear", $meta_fields) ) {
			$columnArr[] = 'Biggest Animal Fear?';
		}
		if( in_array("_ocb_biggest_animal_fear_public", $meta_fields) ) {
			$columnArr[] = 'Biggest Animal Fear Display Public?';
		}
		if( in_array("_ocb_sing_at_karaoke_night", $meta_fields) ) {
			$columnArr[] = 'What would you sing at Karaoke night?';
		}
		if( in_array("_ocb_sing_at_karaoke_night_public", $meta_fields) ) {
			$columnArr[] = 'Karaoke Night Display Public?';
		}
		if( in_array("_ocb_hobbies", $meta_fields) ) {
			$columnArr[] = 'Hobbies?';
		}
		if( in_array("_ocb_hobbies_public", $meta_fields) ) {
			$columnArr[] = 'Hobbies Display Public?';
		}
		/*if( isset($_POST['ocb_member_taxonomies']) ) {
			$columnArr[] = 'Taxonomies';
		}*/
		$taxonomies = $_POST['ocb_member_taxonomies'];

		if( in_array("membersection", $taxonomies) ) {
			$columnArr[] = 'Sections';
		}
		if( in_array("memberensembles", $taxonomies) ) {
			$columnArr[] = 'Ensembles';
		}
		if( in_array("instruments", $taxonomies) ) {
			$columnArr[] = 'Instruments';
		}

	
		fputcsv($output, $columnArr);

		
		$args = array(
			'post_type' => 'ocbmembers',
			'post_status' => 'any',
			'posts_per_page' => -1
		);
		$ocbmembers = new WP_Query( $args );
	
		global $post;
		while ( $ocbmembers->have_posts() ) : $ocbmembers->the_post();
			$contentArr = array();
			$contentArr[] = get_the_title();

			if( in_array("_ocb_short_description", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_short_description', true );
			}
			if( in_array("_ocb_cell_phone", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_cell_phone', true );
			}
			if( in_array("_ocb_cell_phone_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_cell_phone_public', true );
			}
			if( in_array("_ocb_address", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_address', true );
			}
			if( in_array("_ocb_address_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_address_public', true );
			}
			if( in_array("_ocb_birthplace", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_birthplace', true );
			}
			if( in_array("_ocb_birthplace_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_birthplace_public', true );
			}
			if( in_array("_ocb_favorite_animal", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_animal', true );
			}
			if( in_array("_ocb_favorite_animal_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_animal_public', true );
			}
			if( in_array("_ocb_favorite_car", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_car', true );
			}
			if( in_array("_ocb_favorite_car_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_car_public', true );
			}
			if( in_array("_ocb_musical_hero", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_musical_hero', true );
			}
			if( in_array("_ocb_musical_hero_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_musical_hero_public', true );
			}
			if( in_array("_ocb_favorite_composer", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_composer', true );
			}
			if( in_array("_ocb_favorite_composer_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_composer_public', true );
			}
			if( in_array("_ocb_favorite_instrument", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_instrument', true );
			}
			if( in_array("_ocb_favorite_instrument_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_instrument_public', true );
			}
			if( in_array("_ocb_favorite_color", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_color', true );
			}
			if( in_array("_ocb_favorite_color_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_color_public', true );
			}
			if( in_array("_ocb_you_grew_up", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_you_grew_up', true );
			}
			if( in_array("_ocb_you_grew_up_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_you_grew_up_public', true );
			}
			if( in_array("_ocb_current_occupation", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_current_occupation', true );
			}
			if( in_array("_ocb_current_occupation_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_current_occupation_public', true );
			}
			if( in_array("_ocb_you_laugh_the_most", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_you_laugh_the_most', true );
			}
			if( in_array("_ocb_you_laugh_the_most_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_you_laugh_the_most_public', true );
			}
			if( in_array("_ocb_motivates_you_to_work", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_motivates_you_to_work', true );
			}
			if( in_array("_ocb_motivates_you_to_work_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_motivates_you_to_work_public', true );
			}
			if( in_array("_ocb_proudest_accomplishment", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_proudest_accomplishment', true );
			}
			if( in_array("_ocb_proudest_accomplishment_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_proudest_accomplishment_public', true );
			}
			if( in_array("_ocb_favorite_sport", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_sport', true );
			}
			if( in_array("_ocb_favorite_sport_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_sport_public', true );
			}
			if( in_array("_ocb_only_eat_one_meal", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_only_eat_one_meal', true );
			}
			if( in_array("_ocb_only_eat_one_meal_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_only_eat_one_meal_public', true );
			}
			if( in_array("_ocb_favorite_author", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_author', true );
			}
			if( in_array("_ocb_favorite_author_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_favorite_author_public', true );
			}
			if( in_array("_ocb_biggest_animal_fear", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_biggest_animal_fear', true );
			}
			if( in_array("_ocb_biggest_animal_fear_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_biggest_animal_fear_public', true );
			}
			if( in_array("_ocb_sing_at_karaoke_night", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_sing_at_karaoke_night', true );
			}
			if( in_array("_ocb_sing_at_karaoke_night_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_sing_at_karaoke_night_public', true );
			}
			if( in_array("_ocb_hobbies", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_hobbies', true );
			}
			if( in_array("_ocb_hobbies_public", $meta_fields) ) {
				$contentArr[] = get_post_meta( $post->ID, '_ocb_hobbies_public', true );
			}


			if( in_array("membersection", $taxonomies) ) {
				$terms = wp_get_post_terms( $post->ID, 'membersection' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
					$tax_terms = '';
					$count = 0;
					foreach ( $terms as $term ) {
						if($count > 0) $sep = ', ';
						else $sep = '';
						$tax_terms .= $sep . $term->name;
						$count++;
					}
					$contentArr[] = $tax_terms;
				}
 			}
			if( in_array("memberensembles", $taxonomies) ) {
				$terms = wp_get_post_terms( $post->ID, 'memberensembles' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
					$tax_terms = '';
					$count = 0;
					foreach ( $terms as $term ) {
						if($count > 0) $sep = ', ';
						else $sep = '';
						$tax_terms .= $sep . $term->name;
						$count++;
					}
					$contentArr[] = $tax_terms;
				}
			}
			if( in_array("instruments", $taxonomies) ) {
				$terms = wp_get_post_terms( $post->ID, 'instruments' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
					$tax_terms = '';
					$count = 0;
					foreach ( $terms as $term ) {
						if($count > 0) $sep = ', ';
						else $sep = '';
						$tax_terms .= $sep . $term->name;
						$count++;
					}
					$contentArr[] = $tax_terms;
				}
			}


			/*if( isset($_POST['ocb_member_taxonomies']) ) {
				$taxonomies = '';
				$count = 0;
				foreach($_POST['ocb_member_taxonomies'] as $taxonomy) {
					if($count > 0) $sep = ', ';
					else $sep = '';
					$taxonomies .= $sep . $taxonomy;
					$count++;
				}
				$contentArr[] = $taxonomies;
			}*/

			fputcsv($output, $contentArr);
		endwhile;
		
		//fclose($output);
		exit();
		//readfile($abs_file_url);
		//unlink($abs_file_url);
	}
}


function load_csv_download_scripts() {
?>
	<script type="text/javascript">
		jQuery( document ).ready(function() {
			jQuery( "#select_meta_fields" ).click(function() {
				if( jQuery('input[name="ocb_member_fields[]"]').is(':checked') ) {
					jQuery( 'input[name="ocb_member_fields[]"]' ).removeAttr('checked');
				} else {
					jQuery( 'input[name="ocb_member_fields[]"]' ).attr('checked', 'checked');
				}
			});

			jQuery( "#select_meta_taxonomies" ).click(function() {
				if( jQuery('input[name="ocb_member_taxonomies[]"]').is(':checked') ) {
					jQuery( 'input[name="ocb_member_taxonomies[]"]' ).removeAttr('checked');
				} else {
					jQuery( 'input[name="ocb_member_taxonomies[]"]' ).attr('checked', 'checked');
				}
			});
		});
  	</script>
<?php
}
add_action('admin_head', 'load_csv_download_scripts');


global $OskyCommunityBand;
$OskyCommunityBand = new OskyCommunityBand();	