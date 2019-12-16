        <div class="animal-gallery">
            <img class="animal-thumb" id="img-<?php echo esc_attr($animal['id']); ?>" src="<?php echo esc_attr($animal['image']); ?>" width="100%" />
            <div id="adc-<?php echo esc_attr($animal['id']); ?>" data-id="<?php echo esc_attr($animal['id']); ?>" class="animal-dots-container">
<?php
        foreach ($animal['photos'] as $photo)
        {
?>
                <a href="#" class="animal-dot svg-shadow" data-target="img-<?php echo esc_attr($animal['id']); ?>" data-image="<?php echo esc_attr($photo['full']); ?>"></a>
<?php
        }
?>
            </div>
        </div>