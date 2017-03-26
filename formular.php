<?php 
function display_formular($post) {
    $post_address = retrieve_current_address($post->ID);
    $post_description = stripslashes_deep(retrieve_current_coordinates($post->ID)->description);
    
?>

<p>Straße:</p>
<p>
    <input type="text" id="streetid" name="streetvalue" class="form-input-tip" size="16" value="<?php echo $post_address['street']; ?>">
</p>

<p>Hausnummer:</p>
<p>
    <input type="text" id="housenumberid" name="housenumbervalue" class="form-input-tip" size="16" value="<?php echo $post_address['housenumber']; ?>">
</p>

<p>PLZ:</p>
<p>
    <input type="text" id="postalcodeid" name="postalcodevalue" class="form-input-tip" size="16" value="<?php echo $post_address['postalcode']; ?>">
</p>

<p>Stadt:</p>
<p>
    <input type="text" id="cityid" name="cityvalue" class="form-input-tip" size="16" value="<?php echo $post_address['city']; ?>">
</p>

<p>Kurze Beschreibung des Markers:</p>
<textarea rows="5" name="descriptionvalue" id="descriptionid" maxlength="255" style="width: 99%"><?php echo $post_description; ?></textarea>

<?php
};
?>