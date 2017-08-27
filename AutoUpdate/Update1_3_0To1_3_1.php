<?php

// This syntax is what MySQL used to correct default dates of 0000-00-00
$sSQL = "ALTER TABLE `event_types` CHANGE `type_defrecurDOY` `type_defrecurDOY` DATE NOT NULL DEFAULT '2017-01-01'";
?>
