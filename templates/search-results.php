<?php
do_action('wppf_before_search_results');

/*
 * Grid view
 */
if (wppf()->is_grid()):
?>
<div class="dw">
<?php
foreach ($animals as $animal)
{
?>
    <div class="dw-panel wppf-animal-box">
        <div class="dw-panel__content">
            <div class="wppf-animal-box-inner">
                <a href="<?php echo wppf()->get_animal_permalink($animal['id']); ?>" target="_self">
                    <img src="<?php echo $animal['thumbnail']; ?>" width="100%" />
<?php
//do_action('wppf_animal_gallery', $animal);
?>
                    <h3 class="wppf-animal-name"><?php echo esc_html($animal['name']); ?></h3>
                </a>
            </div>
        </div>
    </div>
<?php
}
?>
</div>
<?php
/*
 * List view
 */
else:

foreach ($animals as $animal)
{
?>
<div class="wppf-animal-box">
    <div class="wppf-list-left">
<?php
do_action('wppf_animal_gallery', $animal);
?>
    </div>
    <div class="wppf-list-right">
        <h3 class="wppf-animal-name"><a href="<?php echo wppf()->get_animal_permalink($animal['id']); ?>" target="_self"><?php echo esc_html($animal['name']); ?></a></h3>
        <div style="margin-bottom: 10px;"><?php echo esc_html($animal['description']); ?></div>
        <div style="width: 33%; float:left;">
            <p style="text-align: left;">Breed: <?php echo esc_html($animal['breed']); ?></p>
        </div>
        <div style="width: 33%; float:left;">
            <p style="text-align:center;">Gender: <?php echo esc_html($animal['gender']); ?></p>
        </div>
        <div style="width: 33%; float:right;">
            <p style="text-align: right;">Age: <?php echo esc_html($animal['age']); ?></p>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<?php
}
endif; // list view

do_action('wppf_after_search_results');
?>