<?php
	global $wpdb, $wp_roles, $current_user;
	echo '<div class="wrap">';
	echo "<h2>Settings</h2>";
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}	
	?>	
	<h3 class="nav-tab-wrapper">
	<?php
	global $type, $tab, $pagenow;	
	$tabs = array(
		'ocb-general'    => array( 'label' => __( 'General' ),    'url' => 'edit.php?post_type=ocbmembers&page=ocb-settings&block=general&tab=1'),
		'ocb-email'    => array( 'label' => __( 'Email' ),    'url' => 'edit.php?post_type=ocbmembers&page=ocb-settings&block=email&tab=2'),
		'ocb-user-import-export-email'    => array( 'label' => __( 'User Import Export Email' ),    'url' => 'edit.php?post_type=ocbmembers&page=ocb-settings&block=user-import-export-email&tab=3'),
		'ocb-export'    => array( 'label' => __( 'Export' ),    'url' => 'edit.php?post_type=ocbmembers&page=ocb-settings&block=export&tab=4'),
	);
	
	if(!isset($_GET['block'])) $_GET['block'] = 'general';
	if(!isset($_GET['tab'])) $_GET['tab'] = 1;
	
	$tabnow = $_GET['tab'];
	$flag= 1;
	foreach ( $tabs as $tab_id => $tab ) {
		$class = ( substr($tab['url'], -1) == $tabnow ) ? ' nav-tab-active' : '';		
		if(!isset($_GET['tab']) && $flag == 1){
			$class = ' nav-tab-active';
			$flag = 0;
		}		
		echo '<a href="' . $tab['url'] .'" class="nav-tab' . $class . '">' .  esc_html( $tab['label'] ) . '</a>';
	}
	?>
	</h3>	
	<?php	
	switch ($_GET['page']) {
	 case 'ocb-settings':
		if($_GET['block'] == 'general')
			require_once( OCB_ROOT . '/include/ocb-settings-general.php');
		elseif($_GET['block'] == 'email')
			require_once( OCB_ROOT . '/include/ocb-settings-email.php');				
		elseif($_GET['block'] == 'user-import-export-email')
			require_once( OCB_ROOT . '/include/ocb-settings-user-import-export-email.php');				
		elseif($_GET['block'] == 'export')
			require_once( OCB_ROOT . '/include/ocb-settings-export.php');				
		else
			require_once( OCB_ROOT . '/include/ocb-settings-general.php');
	
	}	
		
	echo '</div>';	
?>