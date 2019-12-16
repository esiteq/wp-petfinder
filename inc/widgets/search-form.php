<?php
class WPPF_Search_Form extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
			'wppf-search-form',
			__('WPPF Search Form', 'wppf'),
			[
                'description'   => __('Animal Search Form', 'wppf')
            ]
		);
	}
	public function widget($args, $instance)
    {
		$title_unfiltered = (!empty($instance['title'])) ? sanitize_text_field($instance['title']) : '';
		$title_filtered   = apply_filters('widget_title', $title_unfiltered, $instance, $this->id_base);
		$title            = !empty($title_filtered)? $args['before_title'] . $title_filtered . $args['after_title'] : '';
		$shelter_id       = (!empty($instance['shelter_id'])) ? sanitize_text_field($instance['shelter_id']) : '';
        $name             = (!empty($instance['name'])) ? sanitize_text_field($instance['name']) : '';
        $location         = (!empty($instance['location'])) ? sanitize_text_field($instance['location']) : '';
        $type             = (!empty($instance['type'])) ? sanitize_text_field($instance['type']) : '';
        $gender           = (!empty($instance['gender'])) ? sanitize_text_field($instance['gender']) : '';
        $breed            = (!empty($instance['breed'])) ? sanitize_text_field($instance['breed']) : '';
        $size             = (!empty($instance['size'])) ? sanitize_text_field($instance['size']) : '';
        $hide             = (!empty($instance['hide'])) ? sanitize_text_field($instance['hide']) : '';
		echo $args['before_widget'];
		if ($title) echo $title;

        $results_page = intval(wppf()->get_option('results_page', 0));
        $action = '';
        if ($results_page)
        {
            $action = ' action="'. get_permalink($results_page). '"';
        }
        include wppf()->locate_template('widgets/search-form.php');

		echo $args['after_widget'];
	}
    //
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title']        = sanitize_text_field($new_instance['title']);
        $instance['shelter_id']   = sanitize_text_field($new_instance['shelter_id']);
        $instance['name']         = sanitize_text_field($new_instance['name']);
        $instance['location']     = sanitize_text_field($new_instance['location']);
        $instance['type']         = sanitize_text_field($new_instance['type']);
        $instance['gender']       = sanitize_text_field($new_instance['gender']);
        $instance['breed']        = sanitize_text_field($new_instance['breed']);
        $instance['size']         = sanitize_text_field($new_instance['size']);
        $instance['hide']         = sanitize_text_field($new_instance['hide']);
        return $instance;
	}
    //
    public function form($instance)
    {
        $types      = wppf()->get_animal_types_array(true);
        //$types = array_merge($types, ['-'=>__('off', 'wppf')]);
        $sizes      = wppf()->get_animal_sizes_array(true);
        //$sizes = array_merge($sizes, ['-'=>__('off', 'wppf')]);
        $type       = !empty($instance['title']) ? strtolower($instance['type']) : '';
        if ($type)
        {
            $breeds = wppf()->get_animal_breeds($type);
        }
        else
        {
            $breeds = [''=>__('Any', 'wppf')];
        }
        $instance['title']      = !empty($instance['title'])      ? esc_attr($instance['title']) : '';
        $instance['shelter_id'] = !empty($instance['shelter_id']) ? esc_attr($instance['shelter_id']) : '';
        wppf()->winput(['id'=>$this->get_field_id('title'), 'name'=>$this->get_field_name('title'), 'title'=>__('Title:', 'wppf'), 'value'=>$instance['title']]);
        wppf()->winput(['id'=>$this->get_field_id('shelter_id'), 'name'=>$this->get_field_name('shelter_id'), 'title'=>__('Shelter ID (optional):', 'wppf'), 'value'=>$instance['shelter_id']]);
        wppf()->winput(['id'=>$this->get_field_id('name'), 'name'=>$this->get_field_name('name'), 'title'=>__('Animal Name:', 'wppf'), 'value'=>$instance['name']]);
        wppf()->winput(['id'=>$this->get_field_id('location'), 'name'=>$this->get_field_name('location'), 'title'=>__('Location:', 'wppf'), 'value'=>$instance['location']]);
        wppf()->wselect(['id'=>$this->get_field_id('type'), 'name'=>$this->get_field_name('type'), 'title'=>__('Type:', 'wppf'), 'value'=>$instance['type'], 'choices'=>$types], 'wppf-animal-types');
        wppf()->wselect(['id'=>$this->get_field_id('gender'), 'name'=>$this->get_field_name('gender'), 'title'=>__('Gender:', 'wppf'), 'value'=>$instance['gender'], 'choices'=>wppf()->get_animal_gender_array()]);
        wppf()->wselect(['id'=>$this->get_field_id('breed'), 'name'=>$this->get_field_name('breed'), 'title'=>__('Breed:', 'wppf'), 'value'=>$instance['breed'], 'choices'=>$breeds], 'wppf-animal-breeds');
        wppf()->wselect(['id'=>$this->get_field_id('size'), 'name'=>$this->get_field_name('size'), 'title'=>__('Size:', 'wppf'), 'value'=>$instance['size'], 'choices'=>$sizes]);
        wppf()->winput(['id'=>$this->get_field_id('hide'), 'name'=>$this->get_field_name('hide'), 'title'=>__('Hide fields (comma separated):', 'wppf'), 'value'=>$instance['hide']]);
?>
<p style="margin-top: 0px;">Fields: <i>name, location, type, breed, size</i></p>
<?php
        if (!isset($GLOBALS['wppf_animal_breeds'])):
?>
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
<?php
        $GLOBALS['wppf_animal_breeds'] = true;
        endif;
	}
}
?>