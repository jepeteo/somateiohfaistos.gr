<?php
// Define the v_getUrl function
function v_getUrl() {
  // Check if the connection is over HTTPS
  $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
  // Concatenate the server name to the URL
  $url .= '://' . $_SERVER['SERVER_NAME'];
  // Check if the server port is 80 or 443, if not add it to the URL
  $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
  // Concatenate the request URI to the URL
  $url .= $_SERVER['REQUEST_URI'];
  // Return the final URL
  return $url;
}

// Define the v_forcelogin function
function v_forcelogin() {
  // Check if the user is not logged in
  if( !is_user_logged_in() ) {
    // Get the current URL
    $url = v_getUrl();
    // Call the apply_filters function with two parameters, v_forcelogin_whitelist and v_forcelogin_redirect
    $whitelist = apply_filters('v_forcelogin_whitelist', array());
    $redirect_url = apply_filters('v_forcelogin_redirect', $url);
    // Check if the URL is not the same as the login page URL and not part of the whitelist
    if( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) ) {
      // Redirect the user to the login page, passing the current URL as a parameter
      wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit();
    }
  }
}

// Add the v_forcelogin function to the WordPress init hook
add_action('init', 'v_forcelogin');

// Define the redirect_admin function
// Redirect to homepage after login
function redirect_admin( $redirect_to, $request, $user ){
    // Check if there is a user to check
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        // Check if the user is an administrator
        if ( in_array( 'administrator', $user->roles ) ) {
            // Set the redirect URL to the homepage
            $redirect_to = "/"; 
        }
    }
    // Return the redirect URL
    return $redirect_to;
}

// Add the redirect_admin function to the WordPress login_redirect hook
add_filter( 'login_redirect', 'redirect_admin', 10, 3 );

/**************************************************************/
/**************************************************************/

// // Show Tameio
function showTameio($atts) {
	ob_start();
	// Find current date time.
	$date_now = date('Y-m-d H:i:s');
	$time_now = strtotime($date_now);

	// Find date time 7 days ago.
	$time_before_week = strtotime('-7 day', $time_now);
	$date_before_week = date('Y-m-d H:i:s', $time_before_week);

	//Print form
	if ($_GET['evstartdate']) {
		$form_ev_from = date_i18n("Y-m-d", strtotime($_GET['evstartdate']));	
	} else {
		$form_ev_from = date_i18n("Y-m-d", $time_before_week);
	}
	if ($_GET['evenddate']) {
		$form_ev_to   = date_i18n("Y-m-d", strtotime($_GET['evenddate']));
	} else {
		$form_ev_to = date_i18n("Y-m-d", $time_now);
	}
	echo "<div class='tameio-filter'><span class='tameio-filter-label'>ΦΙΛΤΡΑ</span>
	<form class='tameio-filter-form' action='/tameio'>
		<label for='evstartdate'>Εμφάνιση ταμείου από: </label>
  		<input type='date' id='evstartdate' name='evstartdate' value='". $form_ev_from ."'>
		<label for='evenddate'>έως:</label>
  		<input type='date' id='evenddate' name='evenddate' value='". $form_ev_to ."'>
		<input id='tameio-filter-button' type='submit'>
	</form></div> <br>" ;	
	
	$ev_date_start = date('Y-m-d H:i:s', strtotime($form_ev_from));
	$ev_date_end   = date('Y-m-d H:i:s', strtotime($form_ev_to));
	
	// // // Query apodeikseis.
	$posts = get_posts(array(
		'posts_per_page' => -1,
		'post_type'      => 'apodeikseis',
		'meta_query'     => array(
			array(
				'key'         => 'apd-date',
				'compare'     => 'BETWEEN',
				'value'       => array( $ev_date_start, $ev_date_end ),
				'type'        => 'DATETIME'
			)
		),
/*		'order'          => 'ASC',
		'orderby'        => 'meta_value',
		'meta_key'       => 'apd-date',
		'meta_type'      => 'DATETIME' */
		'order'          => 'DESC',
		'orderby'        => 'meta_value_num',
		'meta_key'       => 'apd-id',
		'meta_type'      => 'NUMERIC'
	));

	if( $posts ) {
		$total = 0;
		foreach( $posts as $post ) {
			echo "<a class='tameio-link' href='" . get_permalink($post->ID) ."'> <div class='tameio-card'>";
			echo "<div class='tameio-card-title'>" . get_the_title($post->ID) . "</div>";
			$arithmos_apodeiksis = get_field("apd-id", $post->ID);
			echo "<div class='tameio-card-apid'>" . $arithmos_apodeiksis . " </div>";

			echo "<div class='tameio-card-date'>" . get_field('apd-date',$post->ID) . "</div>";

			$city = get_field("apd-city", $post->ID);
			$address = get_field("apd-address", $post->ID);
			$client_name = get_field("apd-name", $post->ID);
			$client_lname = get_field("apd-lastname", $post->ID);
			$poso_apodeiksis = get_field("apd-price", $post->ID);
			$total += $poso_apodeiksis;
			

			$fulladdress 	= "<div class='tameio-card-addr'>" . $address . ", " . $city . "</div>";
			$fullname 		= "<div class='tameio-card-name'>" . $client_name . " " . $client_lname  . "</div>";
			$title = $fulladdress . $fullname ;
			echo $title;
			echo "<div class='tameio-card-price'>" . $poso_apodeiksis . " €</div>";

			echo "</div></a>";

		}
		echo "<div class='tameio-card-total'>Σύνολο: " . number_format($total, 2) . " €</div>";
}
    return ob_get_clean();
}
add_shortcode( 'show-tameio', 'showTameio' );
/**************************************************************/
/**************************************************************/

function acf_load_member_info ( $field ) {
	//Get member id
	$member_id = get_field('apd-choose-member');
	
	$minfo1 = "<div class='member-info'><ul class='col1'>";
	$mname = "<li>Όνομα: " . get_field('mb-name', $member_id) ."</li>";
	$lname = "<li>Επώνυμο: " . get_field('mb-lname', $member_id) ."</li>";
	$fname = "<li>Πατρώνυμο: " . get_field('mb-fathername', $member_id) ."</li>";
	$email = "<li>Διεύθυνση email: " . get_field('mb-email', $member_id) ."</li></ul>";
	$column1 = $minfo1 . $mname . $lname . $fname . $email;

	$minfo2 = "<ul class='col2'>";
	$mbadd = "<li>Διεύθυνση Επιχείρησης: " . get_field('mb-business-address', $member_id) ."</li>";
	$mbdoi = "<li>Δ.Ο.Υ.: " . get_field('mb-doi', $member_id) ."</li>";
	$mbtel = "<li>Τηλέφωνο Επιχείρησης: " . get_field('mb-business-telephone', $member_id) ."</li>";
	$mbmob = "<li>Κινητό: " . get_field('mb-mobile', $member_id) ."</li>";
	$mbidn = "<li>Αριθμός Ταυτότητας: " . get_field('mb-idnumber', $member_id) ."</li></ul>";
	$column2 = $minfo2 . $mbadd . $mbdoi . $mbtel . $mbmob . $mbidn;

	$minfo3 = "<ul class='col3'>";
	$mlcsn = "<li>Αριθμός Άδειας: " . get_field('mb-license-number', $member_id) ."</li>";
	$mlsct = "<li>Τύπος Άδειας: " . get_field('mb-license-type', $member_id) ."</li>";
	$mlsch = "<li>Θεώρηση Αδείας: " . get_field('mb-theorisi-adeias', $member_id) ."</li>";
	$mlsce = "<li>Λήξη Αδείας: " . get_field('mb-liksi-adeias', $member_id) ."</li></ul></div>";
	$column3 = $minfo3 . $mlcsn . $mlsct . $mlsch . $mlsce;

	$field['message'] = $column1 . $column2 . $column3  ;
	
	return $field;
}
add_filter('acf/load_field/key=field_6331845ce7309', 'acf_load_member_info');

/*******************************/
/*******************************/
/*******************************/
/*******************************/
/*********** SETTINGS **********/

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'General Settings',
		'menu_title'	=> 'Settings',
		'menu_slug' 	=> 'apodeikseis-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}


/*******************************/
/*******************************/
/*******************************/
/*******************************/
/********* APODEIKSEIS *********/

/* apodeikseis - admin  */
/* readonly field Αριθμός Απόδειξης*/
add_filter('acf/load_field/name=apd-id', function($field) {
        $field['readonly'] = 0;
        $field['disabled'] = 0;
        return $field;
});

/* Ελεγχος πεδίου Όνομα πρίν την αποθήκευση */
function apd_check_name( $field ) {
	if(get_field('apd-choose-member')) {
		$member_id = get_field('apd-choose-member');
		if( empty($field)) {
			$field = get_field('mb-name', $member_id); 
		}
	}
	return $field;
}
add_filter('acf/load_value/name=apd-name', 'apd_check_name');

/* Ελεγχος πεδίου Επώνυμο πρίν την αποθήκευση */
function apd_check_lastname( $field ) {
	if(get_field('apd-choose-member')) {
		$member_id = get_field('apd-choose-member');
		if( empty($field)) {
			$field = get_field('mb-lname', $member_id); 
		}
	}
	return $field;
}
add_filter('acf/load_value/name=apd-lastname', 'apd_check_lastname');

/* Ελεγχος πεδίου Διευθυνση πρίν την αποθήκευση */
function apd_check_address( $field ) {
	if(get_field('apd-choose-member')) {
		$member_id = get_field('apd-choose-member');
		if( empty($field)) {
			$field = get_field('mb-business-address', $member_id); 
		}
	}
	return $field;
}
add_filter('acf/load_value/name=apd-address', 'apd_check_address');

/* Ελεγχος πεδίου Αριθμός Απόδειξης πρίν την αποθήκευση */
function my_acf_prepare_field( $field ) {
	if( empty($field)) {
		$getinvoicemax = get_field('apd-ac', 'option');
		$field = $getinvoicemax;
//		echo "Empty: " . $field;
		$latest_apodeiksi = get_posts("post_type=apodeikseis&numberposts=1");
		$latest_apod_id = get_field( "apd-id", $latest_apodeiksi[0]->ID );
		echo "<p style='text-align:right; margin:0 1em;'>Αριθμός Τελευταίας Απόδειξης: " . $latest_apod_id . "</p>";
		$getinvoicemax++;
		update_field('apd-ac',$getinvoicemax, 'option');

		return $field;
	} else {
//		echo "Not Empty: " . $field;
		return $field;
	}
}
add_filter('acf/load_value/name=apd-id', 'my_acf_prepare_field');

add_action('acf/save_post', 'my_acf_save_post');
function my_acf_save_post( $post_id ) {
    // exit if not custom post type apodeikseis
    if (get_post_type($post_id) != 'apodeikseis') { return; }

    $apd_id = get_field('apd-id');
    // check if apd-id already exists in another post
    $duplicate_post = get_posts(array(
        'post_type' => 'apodeikseis',
        'meta_query' => array(
            array(
                'key' => 'apd-id',
                'value' => $apd_id,
                'compare' => '='
            )
        )
    ));
    if(!empty($duplicate_post)) {
		if (count($duplicate_post) > 1) {
        //print_r($duplicate_post);
        wp_die('Σφαλμα: Αυτός ο αριθμός απόδειξης υπάρχει ήδη.');
		}
    } else {
        // update the post with the apd-id value
        update_field('apc-id', $apd_id);
        $max_apod_id = get_field('apd-ac', 'option');
        if ($apd_id > $max_apod_id) {
            update_field('apd-ac', $apd_id, 'option');
        }
    }
}

/* apodeikseis - admin  */

function show_apodeikseis_blog( $atts ){
	ob_start();
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type'=>'apodeikseis', // Your post type name
		'posts_per_page' => 30,
		'paged' => $paged,
	);

	$loop = new WP_Query( $args );
	
	if ( $loop->have_posts() ) {
		echo "<div class='apodeikseis-blog'>";
		while ( $loop->have_posts() ) : $loop->the_post();
				$apd_permalink = get_the_permalink();
				$apd_address = get_field("apd-address");
				$apd_city = get_field("apd-city");
				$apd_state = get_field("apd-state");
				$apd_name = get_field("apd-name");
				$apd_lname = get_field("apd-lastname");
				$apd_date = get_field("apd-date");
				$apd_price = get_field("apd-price");		
		
				echo "<a class='apodeiksi-card' href='" . $apd_permalink . "' title='Προβολή Απόδειξης'><div>";
				echo "<div class='apdname'><i class='fa-solid fa-user'></i> " . $apd_name . " " . $apd_lname . " </div>";

				echo "<div class='apdaddr'><i class='fa-solid fa-map-location-dot'></i> " . $apd_address . ", " . $apd_city ." </div>";
				echo "<div class='apddate'><i class='fa-solid fa-calendar-days'></i> " . $apd_date ." </div>";
				echo "<div class='apdcost'><i class='fa-solid fa-money-bill'></i> " . $apd_price ." € </div>";
		echo "</div></a>";
		endwhile;
		echo "</div>";
		$total_pages = $loop->max_num_pages;

		if ($total_pages > 1){

			$current_page = max(1, get_query_var('paged'));

			echo paginate_links(array(
				'base' => get_pagenum_link(1) . '%_%',
				'format' => '/page/%#%',
				'current' => $current_page,
				'total' => $total_pages,
				'prev_text'    => __('« prev'),
				'next_text'    => __('next »'),
			));
		}    
	}
//	printf( '<div>%s</div>', get_next_posts_link( 'Older posts', $loop->max_num_pages ) );
//	printf( '<div>%s</div>', get_previous_posts_link( 'Newer posts', $loop->max_num_pages ) );

	wp_reset_postdata(); 
	return ob_get_clean();
}	
add_shortcode( 'show-apodeikseis', 'show_apodeikseis_blog' );

function show_apodeikseis_info( $atts ){
	
	ob_start();
	if($_GET['s'] && !empty($_GET['s'])) { $stext = $_GET['s']; }

	$args= array(
		'post_type' => 'apodeikseis',
		'post_per_page' => -1,
		's' => $stext
	);
	$query = new WP_Query($args);

	echo "<table class='listapodeiksi'>";
	echo "<thead><tr><td>ΟΝΟΜΑ</td>";
	echo "<td>ΕΠΩΝΥΜΟ</td>";
	echo "<td>ΔΙΕΥΘΥΝΣΗ</td>";
	echo "<td>ΠΟΛΗ</td>";
	echo "<td>ΝΟΜΟΣ</td>";
	echo "<td>ΤΚ</td>";
	echo "<td>ΠΟΣΟ</td><td></td></tr></thead>";
	
		while ($query -> have_posts()) : $query -> the_post();
			
			$apd_permalink = get_the_permalink();
			$apd_address = get_field("apd-address");
			$apd_city = get_field("apd-city");
			$apd_state = get_field("apd-nomos");
			$apd_tk = get_field("apd-tk");
			$apd_name = get_field("apd-name");
			$apd_lname = get_field("apd-lastname");
			$apd_price = get_field("apd-price");


			echo "<tr><td class='apdname'>" . $apd_name ." </td>";
			echo "<td class='apdlnam'>" . $apd_lname ." </td>";
			echo "<td class='apdaddr'>" . $apd_address ." </td>";
			echo "<td class='apdcity'>" . $apd_city ." </td>";
			echo "<td class='apdstat'>" . $apd_state ." </td>";
			echo "<td class='apdpost'>" . $apd_tk ." </td>";
			echo "<td class='apdmobo'>" . $apd_price ." € </td><td class='viewapodeiksi' style='padding: 0 4px;'><a href='" . $apd_permalink . "' title='Προβολή Απόδειξης'><i class='fa-solid fa-eye'></i></a></td></tr>";
		endwhile; 
	echo "</table>";
	
	wp_reset_query();
	return ob_get_clean();
}
add_shortcode( 'apodeikseis-info', 'show_apodeikseis_info' );

/* APODEIKSEIS END */
/*******************************/
/*******************************/
/*******************************/
/*******************************/

/* EVENTS */
/*******************************/

// Show Rantevou
function showRantevou($atts) {
	ob_start();
	
// Find current date time.
$date_now = date('Y-m-d H:i:s');
$time_now = strtotime($date_now);

// Find date time in 7 days.
$time_next_week = strtotime('+7 day', $time_now);
$date_next_week = date('Y-m-d H:i:s', $time_next_week);

	/*
	$ev_date_now = date_i18n('l, d F Y', $time_now);
	$ev_date_nextweek = date_i18n('l, d F Y', $time_next_week);
	
	$ev_date_nowa = strtotime($_GET['evstartdate'] ?? $time_now);
	$ev_date_nowb = date_i18n('l, d F Y', $ev_date_nowa);

	$ev_date_nexa = strtotime($_GET['evenddate'] ?? $time_next_week);
	$ev_date_nexb = date_i18n('l, d F Y', $ev_date_nexa);
	*/

	//Print form
	if ($_GET['evstartdate']) {
		$form_ev_from = date_i18n("Y-m-d", strtotime($_GET['evstartdate']));	
	} else {
		$form_ev_from = date_i18n("Y-m-d", $time_now);
	}
	if ($_GET['evenddate']) {
		$form_ev_to   = date_i18n("Y-m-d", strtotime($_GET['evenddate']));
	} else {
		$form_ev_to = date_i18n("Y-m-d", $time_next_week);
	}
	echo "<div class='ev-filter'><span class='ev-filter-label'>ΦΙΛΤΡΑ</span>
	<form class='ev-filter-form' action='/events'>
		<label for='evstartdate'>Εμφάνιση προγραμματισμένων ραντεβού από:</label>
  		<input type='date' id='evstartdate' name='evstartdate' value='". $form_ev_from ."'>
		<label for='evenddate'>έως:</label>
  		<input type='date' id='evenddate' name='evenddate' value='". $form_ev_to ."'>
		<input id='ev-filter-button' type='submit'>
	</form></div> <br>" ;	
	
	$ev_date_start = date('Y-m-d H:i:s', strtotime($form_ev_from));
	$ev_date_end   = date('Y-m-d H:i:s', strtotime($form_ev_to));

	/* Debugging time
	echo $time_now. " - " . $time_next_week . "<br>";
	echo $form_ev_from . " - " . $form_ev_to . "<br>";
	echo strtotime($form_ev_from) . " - " . strtotime($form_ev_to) . "<br>";
	echo $ev_date_start . " - " . $ev_date_end;
	*/
	
// // // Query events.
// 
// 
$posts = get_posts(array(
    'posts_per_page' => -1,
    'post_type'      => 'events',
    'meta_query'     => array(
        array(
            'key'         => 'ev_datetime',
            'compare'     => 'BETWEEN',
            'value'       => array( $ev_date_start, $ev_date_end ),
//            'value'       => array( $date_now, $date_next_week ),
            'type'        => 'DATETIME'
        )
    ),
    'order'          => 'ASC',
    'orderby'        => 'meta_value',
    'meta_key'       => 'ev_datetime',
    'meta_type'      => 'DATETIME'
));

if( $posts ) {
	foreach( $posts as $post ) {
		echo "<a href='" . get_permalink($post->ID) ."'> <div class='event-card'>";
		echo "<div class='ev-card-title'>" . get_the_title($post->ID) . "</div>";
//		echo date_i18n('l d F Y, @ G:i', get_field('ev_datetime',$post->ID));
		echo "<div class='ev-card-date'>" . get_field('ev_datetime',$post->ID) . "</div>";

		
		$post_id = get_field('ev_client', $post->ID);
	
	    $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field("name", $post_id);
		$client_lname = get_field("lastname", $post_id);
		$client_tel = "";
		$client_mob = "";
		if( get_field("phone-home", $post_id) ): $client_tel = "<span class='ev-tel'></span>" . get_field('phone-home', $post_id); endif;
		if( get_field("mobile-personal", $post_id) ): $client_mob = "<span class='ev-mob'></span>" . get_field('mobile-personal', $post_id); endif;
		
		$fulladdress 	= "<div class='ev-card-addr'>" . $address . ", " . $city . "</div>";
		$fullname 		= "<div class='ev-card-name'>" . $client_name . " " . $client_lname  . "</div>";
		$fullcontact 	= "<div class='ev-card-cont'>" . $client_tel . " ". $client_mob  . "</div>" ;
        $title = $fulladdress . $fullname . $fullcontact  ;
		echo $title;
		echo "</div></a>";

	}
}
	// l d F Y, @ h:i a (για 12h με πμ,μμ)
	// date_i18n( "l d F Y, @ G:i", $unixtimestamp);
	
    return ob_get_clean();
}
add_shortcode( 'Show-Rantevou', 'showRantevou' );

// Edit Randevou
function editrandevouslink($atts) {
	ob_start();
	echo "<a href='" . get_edit_post_link() . "'>ΕΠΕΞΕΡΓΑΣΙΑ ΡΑΝΤΕΒΟΥ</a>";
    return ob_get_clean();
}
add_shortcode( 'EditRandevou', 'editrandevouslink' );

// Show Randevou Client information
function showrandevousclientinformation($atts) {
	ob_start();
	$post_id = get_field('ev_client', $post->ID);
	echo "<a href='" . get_permalink($post_id) . "'>ΠΡΟΒΟΛΗ ΠΕΛΑΤΗ</a>";
    return ob_get_clean();
}
add_shortcode( 'ShowRandevousClientInformation', 'showrandevousclientinformation' );

// Show Randevou Client
function showrandevousclient($atts) {
	ob_start();

		echo "<div class='rc-card'>";
		echo "<div class='rc-card-date'> Ημερομηνία Ραντεβού: " . get_field('ev_datetime',$post->ID) . "</div>";

		$post_id = get_field('ev_client', $post->ID);
	
	    $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field("name", $post_id);
		$client_lname = get_field("lastname", $post_id);
		$client_tel = "";
		$client_mob = "";
		if( get_field("phone-home", $post_id) ): $client_tel = "<a href='tel:" . get_field('phone-home', $post_id) . "'><span class='rc-tel'></span>" . get_field('phone-home', $post_id) . "</a>" ; endif;
		if( get_field("mobile-personal", $post_id) ): $client_mob = "<a href='tel:" . get_field('mobile-personal', $post_id) . "'><span class='rc-mob'></span>" . get_field('mobile-personal', $post_id) . "</a>" ; endif;
		
		$fulladdress 	= "<div class='rc-card-addr'> Διεύθυνση: " . $address . ", " . $city . "</div>";
		$fullname 		= "<div class='rc-card-name'> Όνομαεπώνυμο: " . $client_name . " " . $client_lname  . "</div>";
		$fullcontact 	= "<div class='rc-card-cont'> Τηλέφωνα Επικοινωνίας: <br> " . $client_tel . " ". $client_mob  . "</div>" ;
        $title = $fulladdress . $fullname . $fullcontact  ;
		echo $title;
		echo "</div>";
	
	
	return ob_get_clean();
}
add_shortcode( 'ShowRandevousClient', 'showrandevousclient' );

/* EVENTS END */
/*******************************/
/*******************************/
/*******************************/
/*******************************/
/*******************************/

// Shortcode to custom loop
function show_clients( $atts ){
	ob_start();
	$posts = get_posts(array(
	'posts_per_page'	=> 5,
	'post_type'			=> 'post'
));

if( $posts ) :
	echo "<div><ol>";
		foreach( $posts as $post ): 
		
			setup_postdata( $post );
			echo "<li><a href='";
			the_permalink();
			echo "'>";
			the_title();
			echo "</a></li>";

		endforeach;	
	echo "</ol></div>";
	wp_reset_postdata();
	endif;
}
add_shortcode( 'showclients', 'show_clients', 10,3 );


// Edit Client
function editclientlink($atts) {
	ob_start();
	echo "<a href='" . get_edit_post_link() . "'>ΕΠΕΞΕΡΓΑΣΙΑ ΠΕΛΑΤΗ</a>";
    return ob_get_clean();
}
add_shortcode( 'EditClient', 'editclientlink' );

// Repeater Field - Αρχεία
function show_files( $atts ){
	ob_start();
//ACF fields here
// Check rows exists.
if( have_rows('documents') ):

    // Loop through rows.
    while( have_rows('documents') ) : the_row();
        // Load sub field value.
        $docutitle = get_sub_field('document_title');
        $docufile = get_sub_field('document_file');
        echo "<a class='client-docu' href='" . $docufile . "' target='_blank'>" . $docutitle . "</a>";
    // End loop.
    endwhile;

// No value.
else :
    // Do something...
endif;
    return ob_get_clean();
}
add_shortcode( 'allfiles', 'show_files' );

// Repeater Field - Πληρωμές
function show_maintenance_history( $atts ){
	ob_start();
//ACF fields here
// Check rows exists.
if( have_rows('maintenance') ):

    echo "<ol>";
    // Loop through rows.
    while( have_rows('maintenance') ) : the_row();

        // Load sub field value.
        $lastmaint = get_sub_field('last-time-date-maintanance');
        $costmaint = get_sub_field('cost-maintanance');
        echo "<li>" . $lastmaint . " - " . $costmaint . " € </li>";
    // End loop.
    endwhile;
    echo "</ol>";

// No value.
else :
    // Do something...
endif;
    return ob_get_clean();
}
add_shortcode( 'maintenancehistory', 'show_maintenance_history' );

// Φωτογραφίες Gallery
function show_gimages( $atts ){
	ob_start();
	    $images = get_field('crm-gallery');
        $size = 'thumbnail'; // (thumbnail, medium, large, full or custom size)
        	if( $images ):
            	echo "<ul class='crm-gal'>";
                $new_img_title = get_the_title();
                $new_img_title = strip_tags($new_img_title); 
                foreach( $images as $image ):
				echo "<li><a data-fancybox='gallery' data-infobar='true' data-smallBtn='true' data-animationEffect='fade' data-toolbar='auto' data-fancybox-zoom='false' data-caption='" . $new_img_title . "' href='" . $image['url'] ."' title='" .  $new_img_title . "' alt='" . $new_img_title . "' itemprop='image'>" . wp_get_attachment_image( $image['ID'], $size ) . "</a></li>";
				endforeach;
                echo "</ul>";
                endif;
	return ob_get_clean();
}
add_shortcode( 'image-gallery', 'show_gimages' );

// Τηλέφωνο Οικίας
function show_phone_home( $atts ){
	ob_start();
	if( get_field('phone-home') ){
		$client_tel = get_field('phone-home'); $client_tel = "<a href='tel:" . $client_tel . "'>Τηλ. Οικίας <br>" . $client_tel . "</a>";
	} else {
    	$client_tel = "<span>Τηλ. Οικίας</span>";
	}
	echo $client_tel;
    return ob_get_clean();
}
add_shortcode( 'phone_home', 'show_phone_home' );

// Τηλέφωνο Εργασίας
function show_phone_work( $atts ){
	ob_start();
	if( get_field('phone-work') ){
		$client_telw = get_field('phone-work'); $client_telw = "<a href='tel:" . $client_telw . "'>Τηλ. Εργασίας <br>" . $client_telw . "</a>";
	} else {
    	$client_telw = "<span>Τηλ. Εργασίας</span>";
	}
	echo $client_telw;
    return ob_get_clean();
}
add_shortcode( 'phone_work', 'show_phone_work' );

// Τηλέφωνο Άλλο
function show_phone_other( $atts ){
	ob_start();
	if( get_field('phone-other') ){
		$client_telo = get_field('phone-other'); $client_telo = "<a href='tel:" . $client_telo . "'>Τηλ. Άλλο <br>" . $client_telo . "</a>";
	} else {
    	$client_telo = "<span>Τηλ. Άλλο</span>";
	}
	echo $client_telo;
    return ob_get_clean();
}
add_shortcode( 'phone_other', 'show_phone_other' );

// Κινητό Προσωπικό
function show_mobile_personal( $atts ){
	ob_start();
	if( get_field('mobile-personal') ){
		$client_mob = get_field('mobile-personal'); $client_mob = "<a href='tel:" . $client_mob . "'>Κινητό <br>" . $client_mob . "</a>";
	} else {
    	$client_mob = "<span>Κινητό</span>";
	}
	echo $client_mob;
    return ob_get_clean();
}
add_shortcode( 'mobile_personal', 'show_mobile_personal' );

// Τηλέφωνο Εργασίας
function show_mobile_work( $atts ){
	ob_start();
	if( get_field('mobile-work') ){
		$client_mobw = get_field('mobile-work'); $client_mobw = "<a href='tel:" . $client_mobw . "'>Κιν. Εργασίας <br>" . $client_mobw . "</a>";
	} else {
    	$client_mobw = "<span>Κιν. Εργασίας</span>";
	}
	echo $client_mobw;
    return ob_get_clean();
}
add_shortcode( 'mobile_work', 'show_mobile_work' );

// Κινητό Άλλο
function show_mobile_other( $atts ){
	ob_start();
	if( get_field('mobile-other') ){
		$client_mobo = get_field('mobile-other'); $client_mobo = "<a href='tel:" . $client_mobo . "'>Κιν. Άλλο <br>" . $client_mobo . "</a>";
	} else {
    	$client_mobo = "<span>Κιν. Άλλο</span>";
	}
	echo $client_mobo;
    return ob_get_clean();
}
add_shortcode( 'mobile_other', 'show_mobile_other' );

function show_client_info( $atts ){
	
	ob_start();
	if($_GET['s'] && !empty($_GET['s'])) { $stext = $_GET['s']; }

	$args= array(
		'post_type' => 'clients',
		'post_per_page' => -1,
		's' => $stext
	);
	$query = new WP_Query($args);

	echo "<table class='listclient'>";
	echo "<thead><tr><td>ΔΙΕΥΘΥΝΣΗ</td>";
	echo "<td>ΟΝΟΜΑ</td>";
	echo "<td>ΕΠΩΝΥΜΟ</td>";
	echo "<td>ΠΟΛΗ</td>";
	echo "<td>ΝΟΜΟΣ</td>";
	echo "<td>ΤΚ.</td>";
	echo "<td>ΤΗΛ. ΟΙΚΙΑΣ</td>";
	echo "<td>ΤΗΛ. ΕΡΓΑΣΙΑΣ</td>";
	echo "<td>ΤΗΛ. ΑΛΛΟ</td>";
	echo "<td>ΚΙΝ. ΠΡΟΣΩΠΙΚΟ</td>";
	echo "<td>ΚΙΝ. ΕΡΓΑΣΙΑΣ</td>";
	echo "<td>ΚΙΝ. ΑΛΛΟ</td><td></td></tr></thead>";
	
		while ($query -> have_posts()) : $query -> the_post();
			
			$client_permalink = get_the_permalink();
			$client_address = get_field("address");
			$client_city = get_field("city");
			$client_state = get_field("state");
			$client_pc = get_field("postal-code");
			$client_name = get_field('name');
			$client_lname = get_field('lastname');
			$client_telh = get_field('phone-home');
			$client_telw = get_field('phone-work');
			$client_telo = get_field('phone-other');
			$client_mobp = get_field('mobile-personal');
			$client_mobw = get_field('mobile-work');
			$client_mobo = get_field('mobile-other');

			echo "<tr><td class='clname'>" . $client_name ." </td>";
			echo "<td class='cllnam'>" . $client_lname ." </td>";
			echo "<td class='claddr'>" . $client_address ." </td>";
			echo "<td class='clcity'>" . $client_city ." </td>";
			echo "<td class='clstat'>" . $client_state ." </td>";
			echo "<td class='clpost'>" . $client_pc ." </td>";
			echo "<td class='cltelh'>" . $client_telh ." </td>";
			echo "<td class='cltelw'>" . $client_telw ." </td>";
			echo "<td class='cltelo'>" . $client_telo ." </td>";
			echo "<td class='clmobp'>" . $client_mobp ." </td>";
			echo "<td class='clmobw'>" . $client_mobw ." </td>";
			echo "<td class='clmobo'>" . $client_mobo ." </td><td class='viewclient' style='padding: 0 4px;'><a href='" . $client_permalink . "' title='Προβολή Πελάτη'><i class='fa-solid fa-eye'></i></a></td></tr>";
		endwhile; 
	echo "</table>";
	
	wp_reset_query();
	
/*
	if( get_field('phone-home') ): $client_tel = " | Τηλ." . get_field('phone-home'); endif;
	if( get_field('mobile-personal') ): $client_mob = " | Κιν." . get_field('mobile-personal'); endif;
*/	
	
	return ob_get_clean();
}
add_shortcode( 'client-info', 'show_client_info' );

/* Add custom functions - Th.M */
/* Remove Divi Project Type */
add_action( 'init', 'teo_remove_divi_project_post_type' );
if ( ! function_exists( 'teo_remove_divi_project_post_type' ) ) {
 function teo_remove_divi_project_post_type(){
 unregister_post_type( 'project' );
 unregister_taxonomy( 'project_category' );
 unregister_taxonomy( 'project_tag' );
 }
}

// Removes from admin menu
add_action( 'admin_menu', 'teo_remove_admin_menus' );
function teo_remove_admin_menus() {
    remove_menu_page( 'edit-comments.php' );
}

// Removes from post and pages
add_action('init', 'teo_remove_comment_support', 100);
function teo_remove_comment_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
}

// Removes from admin bar
add_action( 'wp_before_admin_bar_render', 'teo_remove_comments_admin_bar' );
function teo_remove_comments_admin_bar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}

/* Custom Post Type Πελάτης Title */
function teo_filter_title( $title, $post_id )
{
    if (get_post_type($post_id) == 'clients' ) {
        $city = get_field("city", $post_id);
        $address = get_field("address", $post_id);
		$client_name = get_field("name", $post_id);
		$client_lname = get_field("lastname", $post_id);
		$client_tel = "";
		$client_mob = "";
		if( get_field('phone-home') ): $client_tel = " - Τηλ." . get_field('phone-home'); endif;
		if( get_field('mobile-personal') ): $client_mob = " - Κιν." . get_field('mobile-personal'); endif;
		
		$fulladdress = $address . " - " . $city;
		$fullname = " - " . $client_name . " " . $client_lname;
        
		$title = $fulladdress . $fullname .  $client_tel . $client_mob ;
		
        return $title;
    } else {
        return $title;
    }
}
add_filter( 'the_title','teo_filter_title',10,2 );

add_action( 'pre_get_posts', 'extend_admin_search' );
function extend_admin_search( $query ) {
	$post_type = 'apodeikseis';
	$custom_fields = array("apd-address","apd-city","apd-nomos",);
    if( ! is_admin() )
    	return;
  	if ( $query->query['post_type'] != $post_type )
  		return;
    $search_term = $query->query_vars['s'];

    $query->query_vars['s'] = '';

    if ( $search_term != '' ) {
        $meta_query = array( 'relation' => 'OR' );
        foreach( $custom_fields as $custom_field ) {
            array_push( $meta_query, array(
                'key' => $custom_field,
                'value' => $search_term,
                'compare' => 'LIKE'
            ));
        }
        $query->set( 'meta_query', $meta_query );
    };
}

// Πλήρες Όνομα
function show_fullname( $atts ){
	ob_start();

	if( get_field('apd-name') ){ $name = get_field('apd-name');	} 
	if( get_field('apd-lastname') ){ $lastname = get_field('apd-lastname');	} 
	
	$fullname = $name . " " . $lastname;
	echo $fullname;
    return ob_get_clean();
}
add_shortcode( 'apd-fullname', 'show_fullname' );

// Πλήρες Διεύθυνση
function show_fulladdress( $atts ){
	ob_start();

	if( get_field('apd-address') ){ $address = get_field('apd-address');	} 
	if( get_field('apd-city') ){ $city = get_field('apd-city');	} 
	if( get_field('apd-nomos') ){ $nomos = get_field('apd-nomos');	} 
	if( get_field('apd-tk') ){ $tk = get_field('apd-tk');	} 

	$fulladdress = $address . ", " . $city . ", " . $nomos . ", TK." . $tk ;
	echo $fulladdress;
    return ob_get_clean();
}
add_shortcode( 'apd-fulladdress', 'show_fulladdress' );

// Edit Απόδειξη
function editapodeiksilink($atts) {
	ob_start();
//	echo "<a href='" . get_edit_post_link() . "'>ΕΠΕΞΕΡΓΑΣΙΑ ΑΠΟΔΕΙΞΗΣ</a>";
    $editlink = get_edit_post_link();
    echo "<a class='receiptbutton' href='". $editlink ."'><i class='fa-solid fa-edit'></i> ΕΠΕΞΕΡΓΑΣΙΑ ΑΠΟΔΕΙΞΗΣ</a>";
    return ob_get_clean();
}
add_shortcode( 'EditApodeiksi', 'editapodeiksilink' );

// Όλες οι Αποδείξεις
function olesapodeikseislink($atts) {
	ob_start();
    echo "<a class='allreceiptbutton' href='https://www.somateiohfaistos.gr/wp-admin/edit.php?post_type=apodeikseis'><i class='fa-solid fa-receipt'></i> ΟΛΕΣ ΟΙ ΑΠΟΔΕΙΞΕΙΣ</a>";
    return ob_get_clean();
}
add_shortcode( 'OlesApodeikseis', 'olesapodeikseislink' );
?>
