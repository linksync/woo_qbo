<?php if (!defined('ABSPATH')) exit('Access is Denied');

class LS_Notice_Message_Builder
{
    public static function  notice($message, $class = 'error')
    {
        ?>
        <div class="<?php echo $class; ?> notice">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

}