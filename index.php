<?php
/*
Plugin Name: starrplugin
Plugin URI: http://wordpress.org/plugins/starrplugin
Description: "Ich bin gile."
Version: "0.1"
Author: Der Boss selber.
Author URI: http://ideen.net
*/
?>
    <style type="text/css">
        #map {
            margin: 0% 8% 8% 8%;
            width: 84%;
            height: 40%;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwz7_hMFXL29QyV5_EmfnvBHLtGL7q0aQ&callback=initMap"></script>

    <!--Alle Funktionen, die als Actions an verschiedenen Hooks durchgeführt werden sollen-->

    <?php
function echo_mapspace () {
    echo "<div id='map'></div>";
    $coordinates = createMarkerData(getCoordinatesFromDB());
    pass_coordinates_to_JS($coordinates);
    runJS("mapincluder", "/wp-content/plugins/starrplugin/includemap.js");
};
        
function pass_coordinates_to_JS ($coordinates) {
    ?>
    <script type="text/javascript">
        var locationCoordinates = <?php echo $coordinates; ?>;
    </script>
    <?php
};

function echo_on_edit_page() {
    runJS("includecoordform", "/wp-content/plugins/starrplugin/includecoordinateformulars.js");
};

function postCoordinates($ID, $post) { //TODO: Adresse speichern lassen!
    $address = create_address_array();
    if (address_not_set($address)) {
        return;
    };
    $description = $_POST["descriptionvalue"];
    $post_ref = $ID;
    $coordinates_array = geocode($address);
    $latitude = $coordinates_array[0];
    $longitude = $coordinates_array[1];
    if ($latitude != null && $longitude != null) {
        insertCoordinatesQuery($post_ref, $latitude, $longitude, $description);
    };
};

function process_and_insert_data($post_ref, $address, $description) { //TODO: Integrieren!
    $coordinates_array = geocode($address);
    $latitude = $coordinates_array[0];
    $longitude = $coordinates_array[1];
    if ($latitude != null && $longitude != null) {
        insertCoordinatesQuery($post_ref, $latitude, $longitude, $description);
    };
};

function create_address_array() {
    $housenumber = $_POST["housenumbervalue"];
    $street = $_POST["streetvalue"];
    $city = $_POST["cityvalue"];
    $postalcode = $_POST["postalcodevalue"];
    return array($housenumber, $street, $city, $postalcode);
};

function address_not_set($address) {
    for ($i = 1; $i < 4; $i++) {
        if (!isset($address[$i]) || $address[$i] === "") {
            return true;
        };
    };
    return false;
};

function updateCoordinates($ID, $post) {
    $latitude = $_POST["latitudevalue"];
    $longitude = $_POST["longitudevalue"];
    if (!is_nan($latitude) && !is_nan($longitude)) {
        updateCoordinatesQuery($ID, $latitude, $longitude);
    };
};

function runJS ($name, $url) {
    wp_register_script($name, $url);
    wp_enqueue_script($name);
};

function create_url($address_array) {
    return "https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address_array[0]) . "," . urlencode($address_array[1]) . "," . urlencode($address_array[2]) . "," . urlencode($address_array[3]) . "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U";
};

function geocode($address_array){
    $url = create_url($address_array);
    $context = create_context();
    $resp_unparsed = file_get_contents($url, false, $context);
    $resp = json_decode($resp_unparsed, true);
    $latitude = $resp['results'][0]['geometry']['location']['lat'];
    $longitude = $resp['results'][0]['geometry']['location']['lng'];
    $formatted_address = $resp['results'][0]['formatted_address'];
    $coordinates = array(
        $latitude, 
        $longitude
    );
    return $coordinates; 
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

function insertCoordinatesQuery($post_ref, $latitude, $longitude, $description) {
    global $wpdb;
    $wpdb->insert(
        "eu_coordinates",
        array(
            "ID" => NULL,
            "post_reference" => $post_ref,
            "lat" => $latitude,
            "lng" => $longitude,
            "description" => $description
        ),
        array(
            '%d',
            '%d',
            '%s',
            '%f',
            '%s'
        )
    );
};

function update_coordinates_query($post_ref, $latitude, $longitude, $description) {
    global $wpdb;
    $wpdb->update(
        "eu_coordinates",
            array(
            'lat' => $latitude,
            'lng' => $longitude,
            'description' => $description
            ),
            array(
            'post_reference' => $post_ref
            )
        );
};

function getCoordinatesFromDB () {
    global $wpdb;
    $get_coordinates_req = "SELECT * FROM wp_posts JOIN eu_coordinates ON wp_posts.ID=eu_coordinates.post_reference";
    // Mögliche Verbesserung: Nur bestimmte Attribute herausziehen!
    return $wpdb->get_results($get_coordinates_req);
};

function createMarkerData ($coordArray) {
    $result = array();
    for ($i = 0; $i < count($coordArray); $i++) {
            $title_for_window = assign_description_or_title($coordArray[$i]);
            array_push($result, 
                       array(
                           $coordArray[$i]->lat, 
                           $coordArray[$i]->lng, 
                           get_permalink($coordArray[$i]), 
                           $title_for_window, 
                           $coordArray[$i]->post_status
                       )
            );
        };
    return json_encode($result);
};

function assign_description_or_title($post) {
    if (!isset($post->description) || $post->description == "") {
        return $post->post_title;
    } else {
        return $post->description;
    };
};

function update_coordinates($ID, $post) {
    $new_address = create_address_array();
    if (address_not_set($new_address)) {
        return;
    };
    $new_description = $_POST["descriptionvalue"];
    $new_coordinates = geocode($new_address);
    $latitude = $new_coordinates[0];
    $longitude = $new_coordinates[1];
    update_coordinates_query($ID, $latitude, $longitude, $new_description);
    update_address_query($new_address, $ID);
};

function process_and_update_data($post_ref, $address, $description) { //TODO: Integrieren!
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

function decide_on_action($ID, $address, $description){
    if (!address_not_set($address)) {
        if (check_for_marker($ID)) {
            process_and_update_data($ID, $address, $description);
        } else {
            process_and_insert_data($ID, $address, $description);
        };
    } else {
        if (check_for_marker($ID)) {
            process_and_delete_data($ID);
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

function retrieve_current_address($post_ref) {
    global $wpdb;
    $req = "SELECT * FROM eu_addresses WHERE (post_reference=" . $post_ref . ")";
    return $wpdb->get_row($req, OBJECT);
};

function retrieve_current_coordinates($post_ref) {
    global $wpdb;
    $req = "SELECT * FROM eu_coordinates WHERE (post_reference=" . $post_ref . ")";
    return $wpdb->get_row($req, OBJECT);
};

function insert_address_query($address, $post_ref) {
    global $wpdb;
    $wpdb->insert(
        "eu_addresses",
        array(
            "ID" => NULL,
            "post_reference" => $post_ref,
            "housenumber" => $address[0],
            "street" => $address[1],
            "postalcode" => $address[2],
            "city" => $address[3]
        )
    );
};

function update_address_query($address, $post_ref) {
    global $wpdb;
    $wpdb->update(
        "eu_addresses",
        array(
            "housenumber" => $address[0],
            "street" => $address[1],
            "postalcode" => $address[2],
            "city" => $address[3]
        ),
        array(
            "post_reference" => $post_ref
        )
    );
};

function delete_query($table, $post_ref) {
    global $wpdb;
    $wpdb->delete(
        $table,
        array(
            "post_reference" => $post_ref
        )
    );
};

//Add all actions

add_action("get_footer", "echo_mapspace");
add_action("publish_post", "postCoordinates", 10, 2);
add_action('admin_footer', "echo_on_edit_page");
?>