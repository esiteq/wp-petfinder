<?php do_action('wppf_before_search_form'); ?>
<form method="get" class="wppf-search-form">
    <input type="hidden" name="view" value="<?php echo esc_attr(wppf()->get_view()); ?>" />
    <div class="wppf-row">
        <div class="wppf-col-100">
            <label for="wppf-animal-location"><?php _e('Location', 'wppf'); ?></label>
            <input type="text" name="location" placeholder="<?php _e('city, state; latitude,longitude; or postal code', 'wppf'); ?>" value="<?php echo esc_attr($_GET['location']); ?>" />
        </div>
    </div>
    <div class="wppf-row">
        <div class="wppf-col-33">
            <label for="wppf-animal-type"><?php _e('Type', 'wppf'); ?></label>
            <select id="wppf-animal-type" name="type">
<?php
wppf()->animal_type_options();
?>
            </select>
        </div>
        <div class="wppf-col-33">
            <label for="wppf-animal-gender"><?php _e('Gender', 'wppf'); ?></label>
            <select id="wppf-animal-gender" name="gender">
<?php
wppf()->animal_gender_options();
?>
            </select>
        </div>
        <div class="wppf-col-33">
            <label for="wppf-search-btn">&nbsp;</label>
            <input id="wppf-search-btn" type="submit" value="<?php _e('Search', 'wppf'); ?> &rsaquo;" />
        </div>
    </div>
    <div class="wppf-clear"></div>
</form>
<?php do_action('wppf_after_search_form'); ?>