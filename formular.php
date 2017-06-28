<?php 
function display_formular($post) {
    $post_address = retrieve_current_address($post->ID);
    $post_description = stripslashes_deep(retrieve_current_coordinates($post->ID)->description);
    
?>

<button class="button" onclick="deleteAllEntries()">Löschen</button>

<script type="text/javascript">
    function deleteAllEntries() {
        var idArr = ["street", "housenumber", "postalcode", "city"];
        for (var i in idArr) {
            var currentInput = document.querySelector("#" + idArr[i] + "id");
            console.log(currentInput);
            currentInput.value = "";
        };
        var descriptionArea = document.querySelector("#descriptionid");
        console.log(descriptionArea.value);
        descriptionArea.value = "";
    }
</script>

    <!--<input style="display: none" value="Boddentruppen" name="postscreen_input" />  Used to check whether post was updated from the post.php-screen or from another screen-->

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