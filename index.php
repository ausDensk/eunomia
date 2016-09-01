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

    function postCoordinates($ID, $post) {
        $address = create_address_array();
        $coordinates_array = geocode($address);
        $latitude = coordinates_array[0];
        $longitude = coordinates_array[1];
        $description = coordinates_array[2];
        $post_ref = $ID;
        if ($latitude != null && $longitude != null) {
            insertCoordinatesQuery($post_ref, $latitude, $longitude, $description);
        };
    };

function create_address_array() {
    $housenumber = $_POST["housenumbervalue"];
    $street = $_POST["streetvalue"];
    $city = $_POST["cityvalue"];
    $postalcode = (string)$_POST["postalcodevalue"];
    return array($housenumber, $street, $city, $postalcode);
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

function geocode($address_array){
    $url = "https://maps.google.com/maps/api/geocode/json?address=" . address_array[0] . "," . address_array[1] . "," . address_array[2] . "," . address_array[3] . "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U";
    $resp_unparsed = file_get_contents($url);
    $resp = json_decode($resp_unparsed, true);
        $latitude = $resp['results'][0]['geometry']['location']['lat'];
        $longitude = $resp['results'][0]['geometry']['location']['lng'];
        $formatted_address = $resp['results'][0]['formatted_address'];
            $coordinates = array(
                    $latitude, 
                    $longitude,
                    $url
                );    
            return $coordinates; 
}

function insertCoordinatesQuery($post_ref, $latitude, $longitude, $description) {
    global $wpdb;
    $wpdb->insert(
        "eu_coordinates",
        array(
            "ID" => NULL,
            "post_reference" => $post_ref,
            "longitude" => $longitude,
            "latitude" => $latitude,
            "description" => $description
        )
    );
};

function updateCoordinatesQuery($post_ref, $latitude, $longitude) {
    global $wpdb;
    $wpdb->update(
        "eu_coordinates",
            array(
            'longitude' => $longitude,
            'latitude' => $latitude
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
            array_push($result, 
                       array(
                           $coordArray[$i]->latitude, 
                           $coordArray[$i]->longitude, 
                           get_permalink($coordArray[$i]), 
                           $coordArray[$i]->post_title, 
                           $coordArray[$i]->post_status
                       )
            );
        };
    return json_encode($result);
};

//Add all actions

add_action("get_footer", "echo_mapspace");
add_action("publish_post", "postCoordinates");
add_action('admin_footer', "echo_on_edit_page");
add_action("edit_post", "updateCoordinates");
?>