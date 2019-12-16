<?php
$animal_id = isset($animal['id']) ? intval($animal['id']) : 0;
?>
<div class="slider-container">
    <div class="slider-carousel">
        <div class="slider">
<?php foreach ($animal['photos'] as $photo): ?>
            <div class="slide-panel">
                <img class="slide-img" src="<?php echo esc_attr($photo['full']); ?>" alt="<?php echo esc_attr($animal['name']); ?>" />
                <div class="slide-box">
                    <h2 class="slide-text">Slide 1</h2>
                </div>
                <div class="slide-overlay"></div>
            </div>
<?php endforeach; ?>
        </div>

        <div class="slider-controls">
            <span class="slider-arrow prev-slide"><i class="material-icons">keyboard_arrow_left</i></span>
            <span class="slider-arrow next-slide"><i class="material-icons">keyboard_arrow_right</i></span>
            <div class="slideshow-toggle">
                <i class="material-icons play-slideshow">play_arrow</i>
                <i class="material-icons pause-slideshow">pause</i>
            </div>
            <ul class="slide-selector">
                <li class="slide-selected"></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
    </div>
</div>

<div class="slider-modal">
    <span class="slider-close">&times;</span>
    <div class="modal-slide"></div>
</div>