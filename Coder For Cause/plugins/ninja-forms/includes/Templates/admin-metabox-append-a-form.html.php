<select name='ninja_form_select'>
    <option value="0"><?php echo $none_text;?></option>
    <?php foreach( $forms as $form ): ?>

        <?php $id = $form->get_id(); ?>

        <option value="<?php echo intval( $id ); ?>"<?php selected( $id, $form_id );?>>
            <?php echo esc_html( $form->get_setting( 'title' ) ); ?>
        </option>

    <?php endforeach; ?>
</select>