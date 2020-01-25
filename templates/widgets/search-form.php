<?php
do_action('wppf_before_search_form');
$hide = wppf()->comma_separated($hide);
$breeds = [''=>__('Any', 'wppf')];
?>
<form method="get" class="wppf-widget-search-form"<?php echo $action; ?>>
<?php
if (!in_array('name', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->winput(['id'=>'wppf-name', 'name'=>'animal_name', 'title'=>__('Animal Name:', 'wppf'), 'value'=>$_GET['animal_name']]);
if (!in_array('type', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->wselect(['id'=>'wppf-type', 'name'=>'type', 'title'=>__('Animal Type:', 'wppf'), 'value'=>$_GET['type'], 'choices'=>wppf()->get_animal_types_array(true)], 'wppf-animal-types');
if (!in_array('breed', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->wselect(['id'=>'wppf-breed', 'name'=>'breed', 'title'=>__('Breed:', 'wppf'), 'value'=>$_GET['breed'], 'choices'=>$breeds]);
if (!in_array('location', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->winput( ['id'=>'wppf->location', 'name'=>'location', 'title'=>__('Location:', 'wppf'), 'value'=>$_GET['location']]);
if (!in_array('gender', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->wselect(['id'=>'wppf-gender', 'name'=>'gender', 'title'=>__('Gender:', 'wppf'), 'value'=>$_GET['gender'], 'choices'=>wppf()->get_animal_gender_array()]);
if (!in_array('size', $hide))
    // we don't need to sanitize $_GET here because winput() and wselect() will do it
    wppf()->wselect(['id'=>'wppf-size', 'name'=>'size', 'title'=>__('Size:', 'wppf'), 'value'=>$_GET['size'], 'choices'=>wppf()->get_animal_sizes_array(true)]);
?>
<p>
    <input id="wppf-widget-search-btn" type="submit" value="<?php _e('Search', 'wppf'); ?> &rsaquo;" />
</p>
</form>
<script>
jQuery(document).ready(function($)
{
    var animal_breeds = <?php echo json_encode(wppf()->get_animal_breeds_array()); ?>;
    console.log(animal_breeds);
    $('.wppf-animal-types').change(function(e)
    {
        var type = $(this).val().toLowerCase();
        var breeds = animal_breeds[type];
        var options = '<option value=""><?php _e('Any', 'wppf'); ?></options>';
        //options += '<option value="-"><?php _e('off', 'wppf'); ?></options>';
        if (typeof breeds != 'undefined')
        {
            $.each(breeds, function(i,v)
            {
                options += '<option value="'+v+'">'+v+'</option>';
            });
        }
        $('select.wppf-animal-breeds').html(options);
    });
});
</script>
<?php do_action('wppf_after_search_form'); ?>