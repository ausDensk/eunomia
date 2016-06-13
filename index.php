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
    <?php
    function echo_mapspace () {
        echo "<div id='map'></div>";
        $get_coordinates_req = "SELECT * FROM wp_posts JOIN eu_coordinates ON wp_posts.ID=eu_coordinates.post_reference";
        // Mögliche Verbesserung: Nur bestimmte Attribute herausziehen!
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $coords = dbDelta( $get_coordinates_req);
        $results = $wpdb->get_results($get_coordinates_req );
        $coordinates = array();
    ?> <!--$coordinates wird eigentlich von JS gebraucht, um die Marker anzuzeigen-->
    <script type="text/javascript">
        var locationCoordinates = 
        <?php 
            for ($i = 0; $i < count($results); $i++) {
            array_push($coordinates, array($results[$i]->latitude, $results[$i]->longitude, get_permalink($results[$i]), $results[$i]->post_title, $results[$i]->post_status));
        };
            echo json_encode($coordinates);
        ?>;
    </script>
    <?php
        wp_register_script("mapincluder", "/wp-content/plugins/starrplugin/includemap.js");
        wp_enqueue_script("mapincluder");
        runJS("mapincluder", "/wp-content/plugins/starrplugin/includemap.js");
    };
        
        function echo_on_edit_page() {
        /* $form = "<div style='border: 1px solid black; width: 90%; height: 50%' id='postDiv' >
    <h3>Koordinaten</h3>
    <p>Hausnummer: </p><input id='housenumber'><br>
    <p>Straße: </p><input id='street'><br>
    <p>PLZ: </p><input id='postalcode'><br>
    <p>Stadt: </p><input id='city'><br>
</div>";
    echo $form;*/
            runJS("includecoordform", "/wp-content/plugins/starrplugin/includecoordinateformulars.js");
        };
    function postCoordinates($ID, $post) { 
        global $wpdb;
        $latitude = $_POST["latitudevalue"];
        $longitude = $_POST["longitudevalue"];
        $post_ref = $ID;
        if ($latitude != NaN && $longitude != NaN) {
            insertCoordinatesQuery($post_ref, $latitude, $longitude);
        };
    };
function updateCoordinates($post) {
    global $wpdb;
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $post_id = $post->ID;
    if (!is_nan($latitude) && !is_nan($longitude)) {
        $wpdb->update(
        "eu_coordinates",
            array(
            'longitude' => $longitude,
            'latitude' => $latitude
            ),
            array(
            'post_id' => $post->post_id
            )
        );
        echo "Zumindest ist hier was passiert.";
    };
};
function alerrto ($post) {
    global $wpdb;
    $wpdb->insert("eu_coordinates", array("ID" => NULL, "post_reference" => $post->ID, "longitude" => $_POST["longitude"], "latitude" => $_POST["latitude"]));
}

//Additional functions

function runJS ($name, $url) {
    wp_register_script($name, $url);
    wp_enqueue_script($name);
};

function insertCoordinatesQuery($post_ref, $latitude, $longitude) {
    global $wpdb;
    $wpdb->insert(
        "eu_coordinates",
        array(
            "ID" => NULL,
            "post_reference" => $post_ref,
            "longitude" => $latitude,
            "latitude" => $longitude
        )
    );
};

//Add all actions

add_action("get_footer", "echo_mapspace");
add_action("publish_post", "postCoordinates");
add_action('admin_footer', "echo_on_edit_page");
?>