<?php

echo "jobangebote - show";

//echo "<pre>";
//print_r($job);
//echo "</pre>";

?>

<h1>Jobangebot details</h1>
    <?php if ($job): ?>
        <p><strong>Titel:</strong> <?php echo htmlspecialchars($job['jobtitel']); ?></p>
        <p><strong>Beschreibung:</strong> <?php echo htmlspecialchars($job['beschreibung']); ?></p>
    <?php else: ?>
        <p>Jobangebot nicht gefunden.</p>
    <?php endif; ?>