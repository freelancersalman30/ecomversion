@extends('backEnd.layouts.master')
@section('title', 'System Update & Version Control')

@section('content')
<div class="container-fluid">

    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Update</li>
                    </ol>
                </div>
                <h4 class="page-title">🚀 System Update & Git Version Control</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i> {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-block-helper me-2"></i> {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Terminal Output Log (if command executed) -->
    @if(session('update_output'))
        <div class="row">
            <div class="col-12">
                <div class="card border-primary border">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <span class="font-14 font-monospace"><i class="mdi mdi-console-line me-1 text-success"></i> Terminal Execution Output</span>
                        <span class="badge bg-secondary font-monospace">exit log</span>
                    </div>
                    <div class="card-body p-0">
                        <pre class="bg-dark text-light p-3 m-0 font-monospace" style="max-height: 320px; overflow-y: auto; font-size: 13px; line-height: 1.5; border-radius: 0 0 4px 4px;">{{ session('update_output') }}</pre>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Grid -->
    <div class="row">

        <!-- Left: Status & Actions -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark font-16">
                        <i class="mdi mdi-source-branch text-primary me-1"></i> Git Repository Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;" class="bg-light">Git Availability</th>
                                    <td>
                                        @if($isGitInstalled)
                                            <span class="badge bg-success"><i class="mdi mdi-check"></i> Installed</span>
                                            <span class="text-muted ms-2 font-12">({{ $gitVersion }})</span>
                                        @else
                                            <span class="badge bg-danger"><i class="mdi mdi-close"></i> Not Installed</span>
                                            <small class="text-danger d-block">Git command is not found in PATH on this server.</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Git Repo (.git)</th>
                                    <td>
                                        @if($isGitRepo)
                                            <span class="badge bg-success"><i class="mdi mdi-check"></i> Initialized</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="mdi mdi-alert"></i> Not Initialized</span>
                                            <small class="text-muted d-block">See setup guide on the right to initialize Git on your live server.</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Current Branch</th>
                                    <td>
                                        <span class="badge bg-info font-13"><i class="mdi mdi-git me-1"></i> {{ $currentBranch }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Remote Origin</th>
                                    <td class="font-monospace font-12 text-break">
                                        {{ $remoteUrl !== 'N/A' ? $remoteUrl : 'No remote configured yet' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Last Commit</th>
                                    <td class="font-12">
                                        {{ $lastCommit }}
                                    </td>
                                </tr>
                                @if(!empty($gitStatus))
                                <tr>
                                    <th class="bg-light text-warning">Uncommitted Changes</th>
                                    <td>
                                        <details>
                                            <summary class="text-warning font-12" style="cursor: pointer;">
                                                Files modified on server (Click to view)
                                            </summary>
                                            <pre class="bg-light p-2 mt-1 mb-0 font-monospace font-11">{{ $gitStatus }}</pre>
                                        </details>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <h5 class="text-dark font-15 mb-3"><i class="mdi mdi-cloud-download text-success me-1"></i> Pull Updates From Git</h5>

                    <form action="{{ route('admin.system.update.pull') }}" method="POST" id="gitPullForm" onsubmit="return confirmUpdate();">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label font-13">Branch to pull</label>
                                <input type="text" name="branch" class="form-control form-control-sm" value="{{ $currentBranch !== 'N/A' ? $currentBranch : 'main' }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="auto_stash" id="auto_stash" value="1" checked>
                                <label class="form-check-label font-13" for="auto_stash">
                                    <strong>Auto-stash local server changes</strong>
                                    <span class="text-muted d-block font-12">Safely preserves any untracked or local changes before pulling so merge conflicts don't block the pull.</span>
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="run_migrate" id="run_migrate" value="1" checked>
                                <label class="form-check-label font-13" for="run_migrate">
                                    <strong>Run Database Migrations</strong> (<code class="font-11">php artisan migrate --force</code>)
                                    <span class="text-muted d-block font-12">Automatically runs any new database tables or schema changes.</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="run_optimize" id="run_optimize" value="1" checked>
                                <label class="form-check-label font-13" for="run_optimize">
                                    <strong>Clear All Caches</strong> (<code class="font-11">php artisan optimize:clear</code>)
                                    <span class="text-muted d-block font-12">Clears compiled views, routes, config and application cache after updating.</span>
                                </label>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3" id="pullBtn">
                                <i class="mdi mdi-download me-1"></i> Pull Updates Now
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('migrateForm').submit();">
                                <i class="mdi mdi-database-sync me-1"></i> Migrations Only
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('cacheForm').submit();">
                                <i class="mdi mdi-broom me-1"></i> Clear Cache Only
                            </button>
                        </div>
                    </form>

                    <!-- Separate single-action forms -->
                    <form id="migrateForm" action="{{ route('admin.system.update.migrate') }}" method="POST" class="d-none">@csrf</form>
                    <form id="cacheForm" action="{{ route('admin.system.update.cache') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>
        </div>

        <!-- Right: Guide & Webhook -->
        <div class="col-lg-5">

            <!-- Setup Instructions Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark font-16">
                        <i class="mdi mdi-book-open-outline text-info me-1"></i> Version Control Workflow Guide
                    </h5>
                </div>
                <div class="card-body font-13">
                    <h6 class="text-primary font-14">1. Local PC (Development)</h6>
                    <p class="text-muted mb-2">When you finish editing code on your computer, push it to GitHub/GitLab:</p>
                    <pre class="bg-light p-2 rounded font-monospace font-11 mb-3">git add .
git commit -m "Updated POS and Order fixes"
git push origin main</pre>

                    <h6 class="text-primary font-14">2. Live Server Setup (cPanel - One Time Only)</h6>
                    <p class="text-muted mb-2">Connect your cPanel folder to your Git repository:</p>
                    <pre class="bg-light p-2 rounded font-monospace font-11 mb-3">cd public_html
git init
git remote add origin https://github.com/YOUR_USER/YOUR_REPO.git
git fetch
git checkout -f -B main origin/main</pre>

                    <h6 class="text-primary font-14">3. Update Live Anytime</h6>
                    <p class="text-muted mb-0">Whenever you push changes, simply come to this page and click <strong>"Pull Updates Now"</strong>. It will pull the code, run any new migrations, and clear the cache automatically!</p>
                </div>
            </div>

            <!-- Automated Webhook Card (Optional) -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 text-dark font-16">
                        <i class="mdi mdi-webhook text-warning me-1"></i> Auto-Deploy Webhook (Zero Click)
                    </h5>
                </div>
                <div class="card-body font-13">
                    <p class="text-muted">You can configure GitHub to automatically notify your server to pull changes whenever you push:</p>

                    <label class="form-label font-12 fw-bold">Webhook URL (add to GitHub Repo Settings &rarr; Webhooks):</label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control font-monospace font-11" id="webhookInput" value="{{ $webhookUrl }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhook()">Copy</button>
                    </div>

                    <label class="form-label font-12 fw-bold">Secret Token:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace font-11" id="secretInput" value="{{ $webhookSecret }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">Copy</button>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
function confirmUpdate() {
    return confirm("Are you sure you want to pull the latest updates from Git? This will update code files, run migrations, and clear cache.");
}

function copyWebhook() {
    var copyText = document.getElementById("webhookInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Webhook URL copied to clipboard!");
}

function copySecret() {
    var copyText = document.getElementById("secretInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Secret token copied to clipboard!");
}
</script>
@endsection
