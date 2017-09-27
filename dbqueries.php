<?php
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
    $foreign_key_sql = "ALTER TABLE eu_coordinates ADD FOREIGN KEY (post_reference) REFERENCES wp_posts(id) 
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
    $foreign_key_sql = "ALTER TABLE eu_addresses ADD FOREIGN KEY (post_reference) REFERENCES wp_posts(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;";
    $wpdb->query($foreign_key_sql);
};

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
            "postalcode" => $address[3],
            "city" => $address[2]
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
    return $wpdb->get_row($req, ARRAY_A);
};

function retrieve_current_coordinates($post_ref) {
    global $wpdb;
    $req = $wpdb->prepare("SELECT * FROM eu_coordinates WHERE (post_reference=%d)", $post_ref);
    return $wpdb->get_row($req, OBJECT);
};

function retrieve_current_post($ID) {
	global $wpdb;
	$req = $wpdb->prepare("SELECT * FROM wp_posts WHERE (ID=%d)", $ID);
	return $wpdb->get_row($req, OBJECT);
};

function get_coordinates_from_DB() {
    global $wpdb;
    $get_coordinates_req = "SELECT * FROM wp_posts JOIN eu_coordinates ON wp_posts.ID=eu_coordinates.post_reference WHERE wp_posts.post_status='publish'";
    return $wpdb->get_results($get_coordinates_req);
};
?>