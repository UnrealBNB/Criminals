<?php $this->extends('layouts.admin') ?>

<?php $this->section('admin_content') ?>
    <div class="admin-rules">
        <h1>Edit Game Rules</h1>

        <form method="POST" action="/admin/settings/rules">
            <?= $this->csrf() ?>

            <div class="form-group">
                <label for="rules">Game Rules (HTML allowed):</label>
                <textarea id="rules"
                          name="rules"
                          rows="20"
                          class="large-textarea"><?= $this->e($rules) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Rules</button>
            <a href="/admin/settings" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
<?php $this->endSection() ?>