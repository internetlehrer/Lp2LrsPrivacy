<#1>
<?php
$fields_data = array(
    'usr_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'ref_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
    ),
    'status' => array(
        'type' => 'integer',
        'notnull' => true
    ),
    'log_date' => array(
        'type' => 'timestamp',
        'notnull' => true,
    ),
);
$ilDB->createTable("uihk_xlpp_log", $fields_data);
$ilDB->addPrimaryKey("uihk_xlpp_log", array("usr_id", 'ref_id', 'log_date'));
?>


