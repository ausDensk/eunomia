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
    ?> <!--$coordinates wird eigentlich von JS gebraucht, um die Marker anzuzeigen-->
    <script type="text/javascript">
        var locationCoordinates = <?php echo $coordinates; ?>;
    </script>
    <?php
        runJS("mapincluder", "/wp-content/plugins/starrplugin/includemap.js");
    };
        
        function echo_on_edit_page() {
            runJS("includecoordform", "/wp-content/plugins/starrplugin/includecoordinateformulars.js");
        };
    function postCoordinates($ID, $post) {
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

function getCoordinatesFromDB () {
    global $wpdb;
    $get_coordinates_req = "SELECT * FROM wp_posts JOIN eu_coordinates ON wp_posts.ID=eu_coordinates.post_reference";
    // Mögliche Verbesserung: Nur bestimmte Attribute herausziehen!
    return $wpdb->get_results($get_coordinates_req);
};

function createMarkerData ($coordArray) {
    $result = array();
    for ($i = 0; $i < count($coordArray); $i++) {
            array_push($result, 
                       array(
                           $coordArray[$i]->latitude, 
                           $coordArray[$i]->longitude, 
                           get_permalink($coordArray[$i]), 
                           $coordArray[$i]->post_title, 
                           $coordArray[$i]->post_status
                       )
            );
            //Mögliche Verbesserung: Objekt statt Array -> übersichtlicher!
        };
    return json_encode($result);
};

//Add all actions

add_action("get_footer", "echo_mapspace");
add_action("publish_post", "postCoordinates");
add_action('admin_footer', "echo_on_edit_page");
?>