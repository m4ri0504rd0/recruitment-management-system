<?php

echo "jobangebote - index";

//echo "<pre>";
//print_r($jobs);
//echo "</pre>";

if (is_array($jobs)) {
    foreach ($jobs as $job) {
        echo "<p>Titel: " . htmlspecialchars($job['jobtitel']) . "</p>";
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '">Details</a> ';
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '/edit">Bearbeiten</a> ';
        echo '<a href="/jobangebote/' . htmlspecialchars($job['id']) . '/delete">Löschen</a>';
    }
} else {
    echo "<p>Aktuell keine Jobs vorhanden </p>";
}

echo '<hr> <a href="/jobangebote/create">Create</a>';