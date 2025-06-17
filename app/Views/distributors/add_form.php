    <?= $this->extend('layouts/main') ?>
    <?= $this->section('content') ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?= esc($title) ?></h2>
            <a href="<?= base_url('distributors') ?>" class="btn btn-secondary">Back to Distributors List</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php
        $errors = session()->getFlashdata('errors');
        if (!empty($errors)):
        ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="<?= base_url('distributors/' . ($distributor ? 'update/' . esc($distributor['id']) : 'store')) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="agency_name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                            <input type="text" name="agency_name" id="agency_name"
                                class="form-control <?= (!empty($errors['agency_name'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('agency_name', $distributor['agency_name'] ?? '') ?>" required>
                            <?php if (!empty($errors['agency_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['agency_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" name="owner_name" id="owner_name"
                                class="form-control <?= (!empty($errors['owner_name'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('owner_name', $distributor['owner_name'] ?? '') ?>" required>
                            <?php if (!empty($errors['owner_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['owner_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="owner_phone" class="form-label">Owner Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="owner_phone" id="owner_phone"
                                class="form-control <?= (!empty($errors['owner_phone'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('owner_phone', $distributor['owner_phone'] ?? '') ?>" required>
                            <?php if (!empty($errors['owner_phone'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['owner_phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="agent_name" class="form-label">Agent Name</label>
                            <input type="text" name="agent_name" id="agent_name"
                                class="form-control <?= (!empty($errors['agent_name'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('agent_name', $distributor['agent_name'] ?? '') ?>">
                            <?php if (!empty($errors['agent_name'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['agent_name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="agent_phone" class="form-label">Agent Phone</label>
                            <input type="tel" name="agent_phone" id="agent_phone"
                                class="form-control <?= (!empty($errors['agent_phone'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('agent_phone', $distributor['agent_phone'] ?? '') ?>">
                            <?php if (!empty($errors['agent_phone'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['agent_phone']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="agency_gst_number" class="form-label">Agency GST Number</label>
                            <input type="text" name="agency_gst_number" id="agency_gst_number"
                                class="form-control <?= (!empty($errors['agency_gst_number'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('agency_gst_number', $distributor['agency_gst_number'] ?? '') ?>">
                            <?php if (!empty($errors['agency_gst_number'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['agency_gst_number']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gmail" class="form-label">Gmail</label>
                            <input type="email" name="gmail" id="gmail"
                                class="form-control <?= (!empty($errors['gmail'])) ? 'is-invalid' : '' ?>"
                                value="<?= old('gmail', $distributor['gmail'] ?? '') ?>">
                            <?php if (!empty($errors['gmail'])): ?>
                                <div class="invalid-feedback">
                                    <?= esc($errors['gmail']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="agency_address" class="form-label">Agency Address <span class="text-danger">*</span></label>
                        <textarea name="agency_address" id="agency_address"
                                class="form-control <?= (!empty($errors['agency_address'])) ? 'is-invalid' : '' ?>"
                                rows="3" required><?= old('agency_address', $distributor['agency_address'] ?? '') ?></textarea>
                        <?php if (!empty($errors['agency_address'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['agency_address']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status"
                                class="form-control <?= (!empty($errors['status'])) ? 'is-invalid' : '' ?>" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statusOptions as $option): ?>
                                <option value="<?= esc($option) ?>"
                                    <?= set_select('status', $option, ($distributor && $distributor['status'] === $option)) ?>>
                                    <?= esc($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['status'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['status']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes"
                                class="form-control <?= (!empty($errors['notes'])) ? 'is-invalid' : '' ?>"
                                rows="3"><?= old('notes', $distributor['notes'] ?? '') ?></textarea>
                        <?php if (!empty($errors['notes'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary"><?= ($distributor ? 'Update' : 'Add') ?> Distributor</button>
                </form>
            </div>
        </div>
    </div>

    <?= $this->endSection() ?>