<?php
if ($format === 'json') {
    echo json_encode($liste);
}
else {
    ?><select><?php
    foreach($liste as $option) {
        ?><option name="<?=$option?>"><?=$option?></option><?php
    }
    ?></select><?php
}

?>
