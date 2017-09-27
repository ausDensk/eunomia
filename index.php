<?php
/*
Plugin Name: Eunomia Maps Plugin
Plugin URI: http://wordpress.org/plugins/eunomia
Description: "Ein Plugin, das es erlaubt, auf der Startseite eine Karte mit Markern anzuzeigen, die einzelne Artikel verlinken."
Version: "0.2"
Author: starrvinc
Author URI: http://ideen.net
*/

/*Erstellen der Tabellen bei Aktivierung des Plug-ins*/

/*Funktionsübergreifend benutzte Funktionen*/

function pass_coordinates_to_JS ($coordinates) {
    ?>
    <script type="text/javascript">
        var locationCoordinates = <?php echo $coordinates; ?>;
    </script>
    <?php
};

function run_JS ($name, $url) {
    wp_register_script($name, $url);
    wp_enqueue_script($name);
};

function create_address_array() {
    $housenumber = $_POST["housenumbervalue"];
    $street = $_POST["streetvalue"];
    $city = $_POST["cityvalue"];
    $postalcode = $_POST["postalcodevalue"];
    return sanitize_all_inputs(array($housenumber, $street, $city, $postalcode));
};

function sanitize_all_inputs($address_array) {
    return array_map(function ($component) {
        return esc_html($component);
    }, $address_array);
};

function address_not_set($address) {
    for ($i = 1; $i < 4; $i++) {
        if (!isset($address[$i]) || $address[$i] === "") {
            return true;
        };
    };
    return false;
};

function geocode($address_array){
    $url = create_url($address_array);
    $context = create_context();
    $resp_unparsed = file_get_contents($url, false, $context);
    $resp = json_decode($resp_unparsed, true);
    $latitude = $resp['results'][0]['geometry']['location']['lat'];
    $longitude = $resp['results'][0]['geometry']['location']['lng'];
    $coordinates = array(
        $latitude, 
        $longitude
    );
    return $coordinates; 
};

function create_url($address_array) {
    return "https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address_array[0]) . "," . urlencode($address_array[1]) . "," . urlencode($address_array[2]) . "," . urlencode($address_array[3]) . "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U";
};

function create_context () {
    $options = array(
        'http'=>array(
            'method'=> "GET",
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
        )
    );
    return stream_context_create($options);
};

/*Datenbankabfragen*/

/*Verarbeitung der Formulardaten*/

function decide_on_action($ID, $address, $description){
    if (!address_not_set($address)) {
        if (check_for_marker($ID)) {
            process_and_update_data($ID, $address, $description);
        } else {
            process_and_insert_data($ID, $address, $description);
        };
    } else {
        if (check_for_marker($ID)) {
		    process_and_delete_data( $ID );
	    };
    };
};

function check_for_marker($post_ref) {
    $current_marker = retrieve_current_coordinates($post_ref);
    if ($current_marker == NULL) {
        return false;
    };
    return true;
};

function process_and_insert_data($post_ref, $address, $description) {
    $coordinates_array = geocode($address);
    $latitude = $coordinates_array[0];
    $longitude = $coordinates_array[1];
    if ($latitude != null && $longitude != null) {
        insert_coordinates_query($post_ref, $latitude, $longitude, $description);
        insert_address_query($address, $post_ref);
    };
};

function process_and_update_data($post_ref, $address, $description) {
    $new_coordinates = geocode($address);
    $latitude = $new_coordinates[0];
    $longitude = $new_coordinates[1];
    update_coordinates_query($post_ref, $latitude, $longitude, $description);
    update_address_query($address, $post_ref);
};

function process_and_delete_data($post_ref) {
    delete_query("eu_coordinates", $post_ref);
    delete_query("eu_addresses", $post_ref);
};

/*Webhook-Funktionen und alles zusätzliche*/

function init_plugin() {
    create_tables();
};

function echo_mapspace() {
    if (home_url() == "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" || home_url() . "/" == "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") {
        run_JS("gmaps", "https://maps.googleapis.com/maps/api/js?key=AIzaSyDwz7_hMFXL29QyV5_EmfnvBHLtGL7q0aQ");
        $coordinates = create_marker_data(get_coordinates_from_DB());
        pass_coordinates_to_JS($coordinates);
        run_JS("mapincluder", "/wp-content/plugins/starrplugin/includemap.js");
    }; 
};

function create_marker_data($coord_array) {
    $result = array();
    for ($i = 0; $i < count($coord_array); $i++) {
            $title_for_window = assign_description_or_title($coord_array[$i]);
            array_push($result,
                       array(
                           $coord_array[$i]->lat, 
                           $coord_array[$i]->lng, 
                           get_permalink($coord_array[$i]), 
                           $title_for_window, 
                           $coord_array[$i]->post_status
                       )
            );
        };
    return json_encode($result);
};

function assign_description_or_title($post) {
    if (!isset($post->description) || $post->description == "") {
        return $post->post_title;
    } else {
        return stripslashes_deep($post->description);
    }
};

function new_echo_on_edit_page() {
    add_meta_box("coordinates", "Koordinaten", "display_formular", "post", "advanced", "high");
}

function post_coordinates($ID) {
    $new_address = create_address_array();
    $new_description = esc_html($_POST["descriptionvalue"]);
    if (isset($_POST["postscreen_input"])) {
	    decide_on_action($ID, $new_address, $new_description);
    };
};

/*Aufrufe der Funktionen - Webhooks*/

require("dbqueries.php");
require("formular.php");

register_activation_hook(__FILE__, "init_plugin");
add_action("wp_enqueue_scripts", "echo_mapspace");
add_action('add_meta_boxes', 'new_echo_on_edit_page');
add_action("edit_post", "post_coordinates", 10, 2);

?>