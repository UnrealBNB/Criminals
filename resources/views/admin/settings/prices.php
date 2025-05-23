<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-prices">
        <h1>Edit Prize Settings</h1>

        <form method="POST" action="/admin/settings/prices">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="prices">Prize Information (HTML allowed):</label>
                <textarea id="prices"
                          name="prices"
                          rows="15"
                          class="large-textarea"><?= $this->e($prices) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Prizes</button>
            <a href="/admin/settings" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>