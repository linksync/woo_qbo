<?php if ( ! defined( 'ABSPATH' ) ) exit('Access is Denied');

$product_syncing_form = LS_QBO_Product_Form::instance();
$product_syncing_form->accounts_error_message();
$product_syncing_form->require_syncing_error_message();

 if (isset($_POST['clearlog'])) {
    $empty = LSC_Log::instance()->truncate_table();
    if ($empty) {
        $response = "Logs Clear successfully!";
    } else {
        $response = "Error:Unable to Clear Logs Details";
    }
    ?><script>
        jQuery('#response').removeClass('error').addClass('updated').html("<?php echo $response; ?>").fadeIn().delay(3000).fadeOut(4000);
    </script><?php
 }

if (isset($_POST['send_log'])) {
    $fileName = LS_PLUGIN_DIR. 'classes/raw-log.txt';
    $data = file_get_contents($fileName);
    $encoded_data = base64_encode($data);
    $result = array(
        "attachment" => $encoded_data
    );
    $json = json_encode($result);
    $apicall_result = LS_QBO()->api()->send_log($json);

    if (isset($apicall_result['result']) && $apicall_result['result'] == 'success') {
        $response = 'Logs Sent Successfully !';
    } else {
        $response = "Error:Unable to Send Logs Details";
    }
    ?><script>
        jQuery('#response').removeClass('error').addClass('updated').html("<?php echo $response; ?>").fadeIn().delay(3000).fadeOut(4000);
    </script><?php
}
?>
<div>
    <div style='float: left;margin-bottom: 10px;'>
        <form method='POST'>
            <input type='submit' class='button' title=' Use this button to upload your log file to linksync. You should only need to do this if requested by linksync support staff.' style='color:blue'  name='send_log' value='Send log to linksync'>
        </form>
    </div>
    <div style='float: right;margin-bottom: 10px;'>
        <form method='POST'>
            <input type='submit' class='button' style='color:red' name='clearlog' value='Clear Logs'>
        </form>
    </div>
    <?php
    if (isset($_GET['check']) && $_GET['check'] == 'all') {
        echo LSC_Log::printallLogs();
    } else {
        echo LSC_Log::getLogs();
        echo "<a href='?page=linksync&setting=logs&check=all'><br>
                <center>
                    <input type='button' class='button' style='color:#0074a2' name='allLogs' value='Show all'>
            </a>";
    }
    ?>
</div>