<?php

echo "jobangebote - show";

//echo "<pre>";
//print_r($job);
//echo "</pre>";

?>

<h1>Jobangebot löschen</h1>
<p>Möchten Sie das folgende Jobangebot wirklich löschen?</p>
<p>
    <strong>Titel:</strong> <?php echo htmlspecialchars($data['jobtitel'] ?? ''); ?>
</p>
<p>
    <strong>Beschreibung:</strong> <?php echo htmlspecialchars($data['beschreibung'] ?? ''); ?>
</p>
<form action="/jobangebote/delete" method="post">
    <input type="hidden"
           name="id"
           value="<?php echo htmlspecialchars($data['id'] ?? ''); ?>"
    >
    <button type="submit">Löschen</button>
    <a href="/">Abbrechen</a>
</form>
