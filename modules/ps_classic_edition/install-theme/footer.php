<?php
include_once _PS_INSTALL_PATH_ . '/theme/views/footer.php';
?>
<div style="text-align: center;">
    <?php echo $this->translator->trans('Any questions? We’re here to help. Visit the [1]Help Center[/1] or [2]contact us[/2].', [
        '[1]' => '<a href="https://help-center.prestashop.com/" target="_blank" rel="noopener noreferrer">',
        '[/1]' => '</a>',
        '[2]' => '<a href="https://care-center.prestashop.com/contact-us" target="_blank" rel="noopener noreferrer">',
        '[/2]' => '</a>',
    ], 'Install'); ?>
</div>