<?php
/*
Plugin Name: Eunomia Maps Plugin
Plugin URI: http://wordpress.org/plugins/eunomia
Description: "Ein simples Plugin, das Benutzern eine Karte mit Markern anzeigen soll, die zu entsprechenden Artikeln verlinken."
Version: "0.1"
Author: ausDensk
*/
?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwz7_hMFXL29QyV5_EmfnvBHLtGL7q0aQ"></script>

    <!--Alle Funktionen, die als Actions an verschiedenen Hooks durchgeführt werden sollen-->

    <?php

/*Erstellen der Tabellen bei Aktivierung des Plug-ins*/

function create_tables() {  
        create_coordinates_table();
        create_addresses_table();
};

function create_coordinates_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $create_sql = "CREATE TABLE eu_coordinates (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_reference bigint(20) UNSIGNED NOT NULL,
        lat float NOT NULL,
        lng float NOT NULL,
        description varchar(255),
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $wpdb->query($create_sql);
    $foreign_key_sql = "ALTER TABLE eu_coords ADD FOREIGN KEY (post_reference) REFERENCES wp_posts(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;";
    $wpdb->query($foreign_key_sql);
};

function create_addresses_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $create_sql = "CREATE TABLE eu_addresses (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_reference bigint(20) UNSIGNED NOT NULL,
        street varchar(255) NOT NULL,
        housenumber varchar(5),
        postalcode varchar(5) NOT NULL,
        city varchar(255) NOT NULL,
        PRIMARY KEY  (ID)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $wpdb->query($create_sql);
    $foreign_key_sql = "ALTER TABLE eu_addrs ADD FOREIGN KEY (post_reference) REFERENCES wp_posts(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;";
    $wpdb->query($foreign_key_sql);
};

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

function insert_coordinates_query($post_ref, $latitude, $longitude, $description) {
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

function insert_address_query($address, $post_ref) {
    global $wpdb;
    $wpdb->insert(
        "eu_addresses",
        array(
            "ID" => NULL,
            "post_reference" => $post_ref,
            "housenumber" => $address[0],
            "street" => $address[1],
            "postalcode" => $address[3],
            "city" => $address[2]
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

function retrieve_current_address($post_ref) {
    global $wpdb;
    $req = $wpdb->prepare("SELECT * FROM eu_addresses WHERE (post_reference=%d)", $post_ref);
    return $wpdb->get_row($req, ARRAY_N);
};

function retrieve_current_coordinates($post_ref) {
    global $wpdb;
    $req = $wpdb->prepare("SELECT * FROM eu_coordinates WHERE (post_reference=%d)", $post_ref);
    return $wpdb->get_row($req, OBJECT);
};

function get_coordinates_from_DB() {
    global $wpdb;
    $get_coordinates_req = "SELECT * FROM wp_posts JOIN eu_coordinates ON wp_posts.ID=eu_coordinates.post_reference";
    return $wpdb->get_results($get_coordinates_req);
};

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

function echo_mapspace() {
    if (home_url() == "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" || home_url() . "/" == "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") {
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
        return $post->description;
    };
};

function post_coordinates($ID, $post) {
    $address = create_address_array();
    if (address_not_set($address)) {
        return;
    };
    $description = $_POST["descriptionvalue"];
    process_and_insert_data($ID, $address, $description);
};

function echo_on_edit_page() {
    pass_coordinates_to_JS(json_encode(array())); //Wird benötigt, damit das JS keinen Fehler ausspuckt, weil locationCoordinates nicht definiert ist
    run_JS("includecoordform", "/wp-content/plugins/starrplugin/includecoordinateformulars.js");
};

function get_address_and_echo_on_edit_page() {
    $post_ref = $_GET["post"];
    $post_address = retrieve_current_address($post_ref);
    $post_description = retrieve_current_coordinates($post_ref)->description;
    if (isset($post_address)) {
        array_push($post_address, $post_description);
        pass_coordinates_to_JS(json_encode($post_address));
    } else {
        pass_coordinates_to_JS(json_encode(array()));
    }
    run_JS("includecoordform", "/wp-content/plugins/starrplugin/includecoordinateformulars.js");
};

function update_coordinates($ID, $post) {
    $new_address = create_address_array();
    $new_description = $_POST["descriptionvalue"];
    decide_on_action($ID, $new_address, $new_description);
};

/*Aufrufe der Funktionen - Webhooks*/

register_activation_hook(__FILE__, "create_tables");
add_action("get_footer", "echo_mapspace");
add_action("publish_post", "post_coordinates", 10, 2);
add_action('load-post-new.php', "echo_on_edit_page");
add_action('load-post.php', "get_address_and_echo_on_edit_page");
add_action("edit_post", "update_coordinates", 10, 2);
?>