<?php

echo "jobangebote - index";

//echo "<pre>";
//print_r($data);
//echo "</pre>";


if(is_array($data)) {
    foreach ($data as $job) {
        echo "<p>Titel:" . htmlspecialchars($job['jobtitel']) . "</p>";
    }
} else {
    echo "<p>Aktuell keine Jobs vorhanden </p>";
}