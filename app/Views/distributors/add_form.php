<?= $this->extend('layouts/main') ?> <?= $this->section('content') ?>
<div class="container mt-5">
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-3">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <h2><?= esc($title) ?></h2>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-3">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= form_open(isset($distributor['id']) ? 'distributors/update/' . esc($distributor['id']) : 'distributors/store') ?>

    <?php if (isset($distributor['id'])): ?>
        <input type="hidden" name="id" value="<?= esc($distributor['id']) ?>">
    <?php endif; ?>
    <div class="row">



        <div class="mb-3 col-4">
            <label for="agency_name" class="form-label">Agency Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="agency_name" name="agency_name" value="<?= old('agency_name', $distributor['agency_name'] ?? '') ?>" required>
        </div>

        <div class="mb-3 col-4">
            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="owner_name" name="owner_name" value="<?= old('owner_name', $distributor['owner_name'] ?? '') ?>" required>
        </div>

        <div class="mb-3 col-4">
            <label for="owner_phone" class="form-label">Owner Phone <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="owner_phone" name="owner_phone" value="<?= old('owner_phone', $distributor['owner_phone'] ?? '') ?>" required>
        </div>

        <div class="mb-3 col-4">
            <label for="agent_name" class="form-label">Agent Name</label>
            <input type="text" class="form-control" id="agent_name" name="agent_name" value="<?= old('agent_name', $distributor['agent_name'] ?? '') ?>">
        </div>

        <div class="mb-3 col-4">
            <label for="agent_phone" class="form-label">Agent Phone</label>
            <input type="text" class="form-control" id="agent_phone" name="agent_phone" value="<?= old('agent_phone', $distributor['agent_phone'] ?? '') ?>">
        </div>

        

        <div class="mb-3 col-4">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="status" name="status" required>
                <?php foreach ($statusOptions as $option): ?>
                    <option value="<?= esc($option) ?>" <?= (old('status', $distributor['status'] ?? '') == $option) ? 'selected' : '' ?>>
                        <?= esc($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>



        <div class="mb-3 col-4">
            <label for="agency_gst_number" class="form-label">Agency GST Number</label>
            <input type="text" class="form-control" id="agency_gst_number" name="agency_gst_number" value="<?= old('agency_gst_number', $distributor['agency_gst_number'] ?? '') ?>">
        </div>

        <div class="mb-3 col-4">
            <label for="gmail" class="form-label">Gmail</label>
            <input type="email" class="form-control" id="gmail" name="gmail" value="<?= old('gmail', $distributor['gmail'] ?? '') ?>">
        </div>

        <div class="mb-3 col-4">
            <label for="agency_address" class="form-label">Agency Address <span class="text-danger">*</span></label>
            <textarea class="form-control" id="agency_address" name="agency_address" rows="3" required><?= old('agency_address', $distributor['agency_address'] ?? '') ?></textarea>
        </div>

        <div class="mb-3 col-4">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"><?= old('notes', $distributor['notes'] ?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-success"><?= isset($distributor['id']) ? 'Update Distributor' : 'Add Distributor' ?></button>
    <a href="<?= base_url('distributors') ?>" class="btn btn-secondary">Cancel</a>

    <?= form_close() ?>
</div>
<?= $this->endSection() ?>