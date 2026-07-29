<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varient Installation Wizard</title>
    <link rel="shortcut icon" type="image/png" href="assets/icon.png"/>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<div class="installer-card">
    <div class="sidebar">
        <div class="brand-area">
            <div class="brand-logo">
                <img src="assets/icon.png" width="40" height="40" class="rounded">
            </div>
            <div>
                <h5 class="m-0 fw-bold">Varient&nbsp;<span style="font-size: 14px; color: #52525b">v3.0</span></h5>
                <small class="text-muted" style="font-size: 0.75rem;">Installer</small>
            </div>
        </div>

        <div class="nav flex-column nav-pills" id="wizardSteps">
            <div class="step-item active" data-step="1">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
                <span class="step-text">Welcome</span>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <span class="step-text">License</span>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                </div>
                <span class="step-text">Requirements</span>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <span class="step-text">Permissions</span>
            </div>
            <div class="step-item" data-step="5">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
                <span class="step-text">Database</span>
            </div>
            <div class="step-item" data-step="6">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span class="step-text">Settings</span>
            </div>
            <div class="step-item" data-step="7">
                <div class="step-icon-wrapper">
                    <svg class="step-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <span class="step-text">Finish</span>
            </div>
        </div>
    </div>

    <div class="content-area">

        <div id="step-1" class="step-content active">
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="mb-4 p-4">
                    <img src="assets/icon.png" width="100" height="100" class="rounded">
                </div>
                <h1 class="fw-bold text-dark">Welcome to Varient</h1>
                <p class="description px-5">Thank you for purchasing Varient. This wizard will guide you through the installation process. It should only take a few minutes.</p>
                <div class="mt-4">
                    <button class="btn btn-primary btn-lg px-5" id="btnStart" onclick="startInstallation()">Start Installation</button>
                </div>
            </div>
        </div>

        <div id="step-2" class="step-content">
            <h2>License Verification</h2>

            <p class="description">
                Enter your license key to begin installation.
                If you don't have one, create it from the <b><a>Licenses</a></b> section in the Helpdesk.
            </p>

            <div class="mb-4">
                <label class="form-label">License Key</label>
                <textarea class="form-control mb-1" id="licenseKey" rows="4" placeholder="Enter License Key" style="height: 100px; resize: none"></textarea>
                <div class="mb-1">
                    <small class="text-muted">* Please enter your license key, not your purchase code.</small>
                </div>
            </div>

            <div class="alert alert-danger d-none" id="licenseError"></div>

            <div class="actions">
                <button class="btn btn-outline" onclick="prevStep(1)">Back</button>
                <button class="btn btn-primary" id="btnVerify" onclick="verifyLicense()">
                    <span class="btn-text">Verify & Continue</span>
                </button>
            </div>
        </div>

        <div id="step-3" class="step-content">
            <h2>System Requirements</h2>
            <p class="description">We are checking your server configuration to ensure Varient runs smoothly.</p>

            <div id="reqList" class="status-list-container">
            </div>

            <div id="reqError" class="alert alert-danger d-none mt-3"></div>

            <div class="actions">
                <button class="btn btn-outline" onclick="prevStep(2)">Back</button>
                <button class="btn btn-primary" id="btnCheckReq" onclick="checkRequirements()">Check Requirements</button>
            </div>
        </div>

        <div id="step-4" class="step-content">
            <h2>Folder Permissions</h2>
            <p class="description">Please ensure the following folders are writable (CHMOD 775 or 777).</p>

            <div id="permList" class="status-list-container">
            </div>
            <div id="permError" class="alert alert-danger d-none mt-3"></div>

            <div class="actions">
                <button class="btn btn-outline" onclick="prevStep(3)">Back</button>
                <button class="btn btn-primary" id="btnCheckPerm" onclick="checkPermissions()">Check Permissions</button>
            </div>
        </div>

        <div id="step-5" class="step-content">
            <h2>Database Connection</h2>
            <p class="description">Enter your MySQL database credentials. The installer will create the necessary tables.</p>

            <form id="dbForm">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Host</label>
                        <input type="text" class="form-control" name="db_host" value="localhost" placeholder="Enter your database host" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_name" placeholder="Enter your database name" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="db_user" placeholder="Enter your database username" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="text" class="form-control" name="db_pass" placeholder="Enter your database password">
                    </div>
                </div>
            </form>

            <div id="dbError" class="alert alert-danger d-none mt-3"></div>

            <div class="actions">
                <button class="btn btn-outline" onclick="prevStep(4)">Back</button>
                <button class="btn btn-primary" id="btnTestDb" onclick="testDatabase()">
                    <span class="btn-text">Next</span>
                </button>
            </div>
        </div>

        <div id="step-6" class="step-content">
            <h2>Admin & Settings</h2>
            <p class="description">Create the primary administrator account and configure basic settings.</p>

            <form id="adminForm">
                <div class="row g-3">

                    <?php $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
                    $baseUrl .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
                    $baseUrl = str_replace('install/', '', $baseUrl); ?>
                    <div class="col-12">
                        <label class="form-label">Base URL</label>
                        <input type="text" class="form-control" name="base_url" value="<?= $baseUrl; ?>">
                        <small class="text-muted">Examples: <span style='font-family: "Helvetica Neue", Helvetica, Arial, sans-serif'>https://abc.com, https://abc.com/blog, https://test.abc.com</span></small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Timezone</label>
                        <select name="timezone" class="form-select" required>
                            <option value="" selected>Select</option>
                            <?php
                            $zones = timezone_identifiers_list();
                            foreach ($zones as $zone) {
                                echo "<option value='$zone'>$zone</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admin Username</label>
                        <input type="text" class="form-control" name="admin_username" placeholder="admin" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admin Email</label>
                        <input type="email" class="form-control" name="admin_email" placeholder="admin@example.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Admin Password</label>
                        <input type="text" class="form-control" name="admin_password" placeholder="Enter admin password" required>
                    </div>
                </div>
            </form>

            <div id="installError" class="alert alert-danger d-none mt-3"></div>

            <div class="actions">
                <button class="btn btn-outline" onclick="prevStep(5)">Back</button>
                <button class="btn btn-primary" id="btnInstall" onclick="finalInstall()">
                    <span class="btn-text">Finish Installation</span>
                </button>
            </div>
        </div>

        <div id="step-7" class="step-content">
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center">
                <div class="mb-4 text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h2 class="fw-bold">Installation Complete!</h2>
                <p class="description px-5">Varient has been successfully installed. For security reasons, please delete the <strong>install</strong> folder.</p>

                <div class="mt-4">
                    <a href="../" class="btn btn-primary btn-lg px-5">Go to Home</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // State Management
    let currentStep = 1;
    const totalSteps = 7;
    let licenseBypassed = false;

    // Original Icons
    const icons = {
        1: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline>',
        2: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
        3: '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line>',
        4: '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>',
        5: '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>',
        6: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
        7: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
    };

    const checkIcon = '<polyline points="20 6 9 17 4 12"></polyline>';

    function updateUI() {
        // Update Sidebar
        document.querySelectorAll('.step-item').forEach(el => {
            const stepNum = parseInt(el.getAttribute('data-step'));
            const iconSvg = el.querySelector('.step-icon');

            el.classList.remove('active', 'completed');

            if (stepNum === currentStep) {
                el.classList.add('active');
                if (icons[stepNum]) iconSvg.innerHTML = icons[stepNum];
            } else if (stepNum < currentStep) {
                el.classList.add('completed');
                iconSvg.innerHTML = checkIcon;
            } else {
                if (icons[stepNum]) iconSvg.innerHTML = icons[stepNum];
            }
        });

        // Update Content
        document.querySelectorAll('.step-content').forEach(el => {
            el.classList.remove('active');
        });
        document.getElementById(`step-${currentStep}`).classList.add('active');

        // Triggers
        if (currentStep === 3) checkRequirements();
        if (currentStep === 4) checkPermissions();
    }

    function nextStep(targetStep) {
        if (targetStep > totalSteps) return;
        currentStep = targetStep;
        updateUI();
    }

    function prevStep(targetStep) {
        if (targetStep < 1) return;

        // Skip Step 2 if license was auto-verified (Localhost or manual file)
        if (licenseBypassed && targetStep === 2) {
            targetStep = 1;
        }

        currentStep = targetStep;
        updateUI();
    }

    // AJAX Helper
    async function postData(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        for (const key in data) {
            formData.append(key, data[key]);
        }

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            return await response.json();
        } catch (error) {
            return {status: 'error', message: 'Connection error: ' + error.message};
        }
    }

    // Button state helper
    function btnLoad(btnId, isLoading, text = 'Processing...') {
        const btn = document.getElementById(btnId);
        if (!btn) return;

        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerText;
            if (text) btn.innerText = text;
        } else {
            btn.disabled = false;
            if (btn.dataset.originalText) btn.innerText = btn.dataset.originalText;
        }
    }

    // Step 1
    function startInstallation() {
        const btn = document.getElementById('btnStart');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Checking Environment...';

        // Auto-check for localhost or manual license file
        postData('verify_license', {license_key: 'auto-check'})
            .then(res => {
                btn.disabled = false;
                btn.innerText = originalText;
                if (res.status === 'success') {
                    licenseBypassed = true;
                    nextStep(3); // Skip Step 2
                } else {
                    licenseBypassed = false;
                    nextStep(2); // Show Step 2
                }
            });
    }

    // Step 2
    async function verifyLicense() {
        const key = document.getElementById('licenseKey').value.trim();
        const errorBox = document.getElementById('licenseError');

        if (!key) {
            errorBox.textContent = "Please enter your license key.";
            errorBox.classList.remove('d-none');
            document.getElementById('licenseKey').focus();
            return;
        }

        btnLoad('btnVerify', true, 'Verifying...');
        errorBox.classList.add('d-none');

        const result = await postData('verify_license', {license_key: key});

        btnLoad('btnVerify', false);

        if (result.status === 'success') {
            nextStep(3);
        } else {
            errorBox.textContent = result.message;
            errorBox.classList.remove('d-none');
        }
    }

    // Step 3
    async function checkRequirements() {
        const list = document.getElementById('reqList');
        const btn = document.getElementById('btnCheckReq');
        const errorBox = document.getElementById('reqError');

        list.innerHTML = `
            <div class="list-loader">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--primary-color);">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

        btn.disabled = true;
        errorBox.classList.add('d-none');

        const [result] = await Promise.all([
            postData('check_requirements'),
            new Promise(resolve => setTimeout(resolve, 1000))
        ]);

        btn.disabled = false;
        list.innerHTML = '';

        if (result.data && Array.isArray(result.data)) {
            result.data.forEach(req => {
                const badge = req.status
                    ? '<span class="status-badge badge-success">Passed</span>'
                    : '<span class="status-badge badge-error">Failed</span>';

                const icon = req.status
                    ? '<svg class="text-success me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                    : '<svg class="text-danger me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

                list.innerHTML += `
                    <div class="status-item">
                        <div class="d-flex align-items-center">
                            ${icon}
                            <div>
                                <span class="fw-bold d-block">${req.name}</span>
                                <small class="text-muted">Current: ${req.current}</small>
                            </div>
                        </div>
                        ${badge}
                    </div>`;
            });
        }

        if (result.status === 'success') {
            btn.textContent = "Continue";
            btn.onclick = () => nextStep(4);
        } else {
            errorBox.textContent = result.message;
            errorBox.classList.remove('d-none');
            btn.textContent = "Check Again";
            btn.onclick = () => checkRequirements();
        }
    }

    // Step 4
    async function checkPermissions() {
        const list = document.getElementById('permList');
        const btn = document.getElementById('btnCheckPerm');
        const errorBox = document.getElementById('permError');

        list.innerHTML = `
            <div class="list-loader">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--primary-color);">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;

        btn.disabled = true;
        errorBox.classList.add('d-none');

        const [result] = await Promise.all([
            postData('check_permissions'),
            new Promise(resolve => setTimeout(resolve, 1000))
        ]);

        btn.disabled = false;
        list.innerHTML = '';

        if (result.data && result.data.length > 0) {
            result.data.forEach(item => {
                const badge = item.writable
                    ? '<span class="status-badge badge-success">Writable</span>'
                    : '<span class="status-badge badge-error">Not Writable</span>';

                const icon = item.writable
                    ? '<svg class="text-success me-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>'
                    : '<svg class="text-danger me-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

                list.innerHTML += `
                    <div class="status-item">
                        <div class="d-flex align-items-center">
                            ${icon}
                            <span>${item.path}</span>
                        </div>
                        ${badge}
                    </div>`;
            });
        }

        if (result.status === 'success') {
            btn.textContent = "Continue";
            btn.onclick = () => nextStep(5);
        } else {
            errorBox.textContent = "Some files/folders are not writable. Please fix them and try again.";
            errorBox.classList.remove('d-none');
            btn.textContent = "Check Again";
            btn.onclick = () => checkPermissions();
        }
    }

    // Step 5
    async function testDatabase() {
        const form = document.getElementById('dbForm');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const errorBox = document.getElementById('dbError');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        errorBox.classList.remove('alert-success');
        errorBox.classList.add('alert-danger', 'd-none');
        btnLoad('btnTestDb', true, 'Connecting...');

        const [testResult] = await Promise.all([
            postData('test_db', data),
            new Promise(resolve => setTimeout(resolve, 600))
        ]);

        if (testResult.status === 'success') {
            btnLoad('btnTestDb', true, 'Importing SQL...');

            const [importResult] = await Promise.all([
                postData('install_db', data),
                new Promise(resolve => setTimeout(resolve, 800))
            ]);

            btnLoad('btnTestDb', false);

            if (importResult.status === 'success') {
                errorBox.classList.remove('alert-danger');
                errorBox.classList.add('alert-success');
                errorBox.innerHTML = "<strong>Success!</strong> Database configured successfully.";
                errorBox.classList.remove('d-none');

                const btn = document.getElementById('btnTestDb');
                btn.onclick = () => nextStep(6);
                btn.innerHTML = '<span class="btn-text">Next Step</span>';
            } else {
                errorBox.textContent = "Connection OK, but Import Failed: " + importResult.message;
                errorBox.classList.remove('d-none');
            }
        } else {
            btnLoad('btnTestDb', false, 'Next');
            errorBox.textContent = testResult.message;
            errorBox.classList.remove('d-none');
        }
    }

    // Reset UI if DB credentials change
    document.querySelectorAll('#dbForm input').forEach(input => {
        input.addEventListener('input', () => {
            const btn = document.getElementById('btnTestDb');
            btn.onclick = () => testDatabase();
            btn.innerHTML = '<span class="btn-text">Next</span>';

            const errorBox = document.getElementById('dbError');
            errorBox.classList.add('d-none');
            errorBox.classList.remove('alert-success');
            errorBox.classList.add('alert-danger');
        });
    });

    // Step 6
    async function finalInstall() {
        const form = document.getElementById('adminForm');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const errorBox = document.getElementById('installError');
        const data = Object.fromEntries(new FormData(form).entries());

        btnLoad('btnInstall', true, 'Installing...');
        errorBox.classList.add('d-none');

        const result = await postData('create_admin', data);

        btnLoad('btnInstall', false);

        if (result.status === 'success') {
            nextStep(7);
        } else {
            errorBox.textContent = result.message;
            errorBox.classList.remove('d-none');
        }
    }

    updateUI();
</script>

</body>
</html>