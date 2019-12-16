<?php
class WPPF_Animals_From_Shelter extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
			'wppf-animals-from-shelter',
			__('WPPF Animals from Shelter', 'wppf'),
			[
                'description'   => __('Displays animals from specific shelter', 'wppf')
            ]
		);
	}
	public function widget($args, $instance)
    {
		$title_unfiltered = (!empty($instance['title'])) ? sanitize_text_field($instance['title']) : '';
		$title_filtered   = apply_filters('widget_title', $title_unfiltered, $instance, $this->id_base);
		$title            = !empty($title_filtered)? $args['before_title'] . $title_filtered . $args['after_title'] : '';
		$shelter_id       = (!empty($instance['shelter_id'])) ? sanitize_text_field($instance['shelter_id']) : '';
		$number           = (!empty($instance['number'])) ? intval($instance['number']) : 3;
        $type             = (!empty($instance['type'])) ? sanitize_text_field($instance['type']) : '';
        $ids              = (!empty($instance['ids'])) ? sanitize_text_field($instance['ids']) : '';
        $template         = (!empty($instance['template'])) ? sanitize_text_field($instance['template']) : '';
        if ($number  > 20)
        {
            $number = 20;
        }
        $ids = explode(',', $ids);
        foreach ($ids as $key => $id)
        {
            $ids[$key] = intval(trim($id));
        }
        foreach ($ids as $key => $id)
        {
            if ($id === 0)
            {
                unset($ids[$key]);
            }
        }
		// Displays widget output.
		echo $args['before_widget'];
		if ($title) echo $title;
        /*
		echo '<p>Shelter ID: ', $shelter_id, '</p>';
		echo '<p>Number: ', $number, '</p>';
		echo '<p>Type: ', $type, '</p>';
        */
        if (count($ids) == 0)
        {
            $tmp = wppf()->get_animals(['type' => $type, 'organization' => $shelter_id, 'limit' => $number * 3]);
            $animals = [];
            // we will skip animals without photos because its a widget
            foreach ($tmp as $animal)
            {
                if (is_array($animal['photos']))
                {
                    $animals[] = $animal;
                    if (count($animals) == $number) break;
                }
            }
        }
        else
        {
            foreach ($ids as $id)
            {
                $animal = wppf()->get_animal($id);
                if (is_array($animal))
                {
                    $animals[] = $animal;
                }
            }
        }
        if (is_array($animals))
        {
            if (count($animals) > 0)
            {
                foreach ($animals as $animal)
                {
                    $name = $animal['name'];
                    if (is_array($animal['photos']))
                    {
                        $photo = current($animal['photos']);
                        $image = $photo['full'];
                    }
                    $url = $animal['url'];
                    if ($template == 'default')
                    {
?>
<p style="text-align: center;">
    <a href="<?php echo $url; ?>" target="blank" title="<?php echo esc_attr($animal[name]); ?>">
        <img src="<?php echo $image; ?>" width="100%" /><br />
        <strong><?php echo esc_html($animal[name]); ?></strong>
    </a>
</p>
<?php
                    }
                    else
                    {
                        include WPPF_DIR. '/templates/'. $template;
                    }
                }
            }
            else
            {
?>
<p>No animals found. Please check widget parameters.</p>
<?php
            }
        }

		echo $args['after_widget'];
	}
    //
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title']        = sanitize_text_field($new_instance['title']);
        $instance['shelter_id']   = sanitize_text_field($new_instance['shelter_id']);
        $instance['number']       = sanitize_text_field($new_instance['number']);
        $instance['type']         = sanitize_text_field($new_instance['type']);
        $instance['ids']          = sanitize_text_field($new_instance['ids']);
        $instance['template']     = sanitize_text_field($new_instance['template']);
        return $instance;
	}
    //
    public function form($instance)
    {
        $types = wppf()->get_animal_types_array();
        $instance['title']      = !empty($instance['title'])      ? esc_attr($instance['title']) : '';
        $instance['shelter_id'] = !empty($instance['shelter_id']) ? esc_attr($instance['shelter_id']) : '';
        $instance['number']     = !empty($instance['number'])     ? esc_attr($instance['number']) : '';
        $instance['type']       = !empty($instance['type'])       ? esc_attr($instance['type']) : '';
        $instance['ids']        = !empty($instance['ids'])        ? esc_attr($instance['ids']) : '';
        $instance['template']   = !empty($instance['template'])   ? esc_attr($instance['template']) : '';
        wppf()->winput(['id'=>$this->get_field_id('title'), 'name'=>$this->get_field_name('title'), 'title'=>__('Title:', 'wppf'), 'value'=>$instance['title']]);
        wppf()->winput(['id'=>$this->get_field_id('shelter_id'), 'name'=>$this->get_field_name('shelter_id'), 'title'=>__('Shelter ID:', 'wppf'), 'value'=>$instance['shelter_id']]);
        wppf()->winput(['id'=>$this->get_field_id('number'), 'name'=>$this->get_field_name('number'), 'title'=>__('Number of animals:', 'wppf'), 'value'=>$instance['number']]);
        wppf()->wselect(['id'=>$this->get_field_id('type'), 'name'=>$this->get_field_name('type'), 'title'=>__('Animal Type:', 'wppf'), 'value'=>$instance['type'], 'choices'=>$types]);
        wppf()->winput(['id'=>$this->get_field_id('ids'), 'name'=>$this->get_field_name('ids'), 'title'=>__('IDs (comma separated):', 'wppf'), 'value'=>$instance['ids'], 'tooltip'=>__('Type animal IDs separated with comma. If this field is filled, Number and Type will be ignored', 'wppf')]);
        wppf()->wselect(['id'=>$this->get_field_id('template'), 'name'=>$this->get_field_name('template'), 'title'=>__('Template:', 'wppf'), 'value'=>$instance['template'], 'choices'=>wppf()->get_template_list(__file__)]);
	}
}
?>