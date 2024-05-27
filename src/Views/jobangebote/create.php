<?php

echo "jobangebote - create";

?>

<h1><?php echo isset($data['id']) ? 'Jobangebot bearbeiten' : 'Jobangebot erstellen'; ?></h1>

<!--<form action="/jobangebote/create" method="post">-->
<form action="/jobangebote/<?php echo isset($data['id']) ? 'update' : 'create'; ?>" method="post">

    <!-- Hidden field -->
    <?php if (isset($data['id'])): ?>
        <input type="hidden"
               name="id"
               value="<?php echo htmlspecialchars($data['id']); ?>"
        >
    <?php endif; ?>

    <!--  NUMBER  with Form Data Persistence -->
    <div class="form-group">
        <label for="abteilung_id">Abteilung_id:</label>
        <input type="number"
               id="abteilung_id"
               name="abteilung_id"
               value="<?php echo htmlspecialchars($data['abteilung_id'] ?? ''); ?>"
        />


        <!-- Error msg -->
        <?php if (isset($errors['abteilung_id'])): ?>
            <span id="error-abteilung_id" style="color: red;">
                <?php echo htmlspecialchars($errors['abteilung_id']); ?>
            </span>
        <?php endif; ?>
    </div>

    <!--  TEXT with Form Data Persistence  -->
    <div class="form-group">
        <label for="jobtitel">Jobtitel:</label>
        <input type="text"
               id="jobtitel"
               name="jobtitel"
               value="<?php echo htmlspecialchars($data['jobtitel'] ?? ''); ?>"
        />

        <!-- Error msg -->
        <?php if (isset($errors['jobtitel'])): ?>
            <span id="error-jobtitel" style="color: red;">
                <?php echo htmlspecialchars($errors['jobtitel']); ?>
            </span>
        <?php endif; ?>
    </div>


    <!--  TEXTAREA  with Form Data Persistence -->
    <div class="form-group">
        <label for="beschreibung">Beschreibung:</label>
        <textarea id="beschreibung"
                  name="beschreibung"
                  rows="4"
                  cols="50">

            <?php echo htmlspecialchars($data['beschreibung'] ?? ''); ?>

        </textarea>

        <!-- Error msg -->
        <?php if (isset($errors['beschreibung'])): ?>
            <span id="error-beschreibung" style="color: red;">
                <?php echo htmlspecialchars($errors['beschreibung']); ?>
            </span>
        <?php endif; ?>
    </div>

    <!--  SUBMIT  -->
    <button type="submit">
        <?php echo isset($data['id']) ? 'Aktualisieren' : 'Erstellen'; ?>
    </button>

</form>
