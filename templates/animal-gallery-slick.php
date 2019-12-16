<?php
$animal_id = isset($animal['id']) ? intval($animal['id']) : 0;
?>
<div class="animal-gallery-slick" id="animal-gallery-<?php echo $animal_id; ?>">
<?php
if (wppf()->has_gallery($animal))
{
    foreach ($animal['photos'] as $photo)
    {
?>
    <div>
<?php /*
        <a class="animal-gallery-slick-thumb" href="<?php echo esc_attr($photo['full']); ?>"><img src="<?php echo esc_attr($photo['full']); ?>" /></a>
*/ ?>
        <img src="<?php echo esc_attr($photo['full']); ?>" width="100%" />
    </div>
<?php
    }
}
?>
</div>
<script>
jQuery(document).ready(function($)
{
    $('div#animal-gallery-<?php echo $animal_id; ?>').slick(
    {
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        dots: true,
        autoplay: false,
        infinite: true,
        adaptiveHeight: true
    });
});
</script>