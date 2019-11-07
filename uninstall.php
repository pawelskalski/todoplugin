<?php

global $wpdb;
$table_name = $wpdb->prefix . 'pskalski_todo';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);


