<?php
//d($animal);
?>
<div class="wppf-row">
    <div class="wppf-col-33">
<?php
do_action('wppf_animal_gallery', $animal);
?>
    <p>
        <?php wppf()->adopt_button($animal); ?>
    </p>
<?php if ($animal['status'] == 'adopted'): ?>
    <p class="wppf-adopted"><?php _e('Adopted - Not Available', 'wppf'); ?></p>
<?php endif; ?>
    </div>
    <div class="wppf-col-67">
        <div class="wppf-animal-info">
            <table class="wppf-info-table">
                <tr>
                    <th>Name</th>
                    <td><?php echo esc_html($animal['name']); ?></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td><?php echo esc_html($animal['type']); ?></td>
                </tr>
                <tr>
                    <th>Breed</th>
                    <td><?php echo esc_html($animal['breed']); ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td><?php echo esc_html($animal['gender']); ?></td>
                </tr>
                <tr>
                    <th>Age</th>
                    <td><?php echo esc_html($animal['age']); ?></td>
                </tr>
                <tr>
                    <th>Color</th>
                    <td><?php echo esc_html($animal['color']); ?></td>
                </tr>
                <tr>
                    <th>Size</th>
                    <td><?php echo esc_html($animal['size']); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?php echo esc_html($animal['status']); ?></td>
                </tr>
                <tr>
                    <th>Attributes</th>
                    <td><?php echo wppf()->print_array($animal['attributes']); ?></td>
                </tr>
                <tr>
                    <th>Environment</th>
                    <td><?php echo wppf()->print_array($animal['environment']); ?></td>
                </tr>
<?php if ($animal['description']): ?>
                <tr>
                    <td colspan="2" class="wppf-description"><?php echo $animal['description']; ?></td>
                </tr>
<?php endif; ?>
            </table>
        </div>
    </div>
</div>