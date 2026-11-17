<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class SystemUpdateController extends Controller
{
    /**
     * Show the system update dashboard
     */
    public function index()
    {
        $gitBinary = $this->getGitBinary();
        $isGitInstalled = false;
        $gitVersion = 'Not Available';
        $isGitRepo = file_exists(base_path('.git'));
        $currentBranch = 'N/A';
        $lastCommit = 'N/A';
        $remoteUrl = 'N/A';
        $gitStatus = '';
        $pendingMigrations = 0;

        // Check git version
        $vProcess = new Process([$gitBinary, '--version'], base_path());
        $vProcess->run();
        if ($vProcess->isSuccessful()) {
            $isGitInstalled = true;
            $gitVersion = trim($vProcess->getOutput());
        }

        if ($isGitInstalled && $isGitRepo) {
            // Current branch
            $branchProcess = new Process([$gitBinary, 'branch', '--show-current'], base_path());
            $branchProcess->run();
            $currentBranch = trim($branchProcess->getOutput());
            if (empty($currentBranch)) {
                $revProcess = new Process([$gitBinary, 'rev-parse', '--abbrev-ref', 'HEAD'], base_path());
                $revProcess->run();
                $currentBranch = trim($revProcess->getOutput()) ?: 'main';
            }

            // Last commit
            $commitProcess = new Process([$gitBinary, 'log', '-1', '--format=%h - %s (%ci by %an)'], base_path());
            $commitProcess->run();
            if ($commitProcess->isSuccessful()) {
                $lastCommit = trim($commitProcess->getOutput());
            }

            // Remote URL
            $remoteProcess = new Process([$gitBinary, 'config', '--get', 'remote.origin.url'], base_path());
            $remoteProcess->run();
            if ($remoteProcess->isSuccessful()) {
                $remoteUrl = trim($remoteProcess->getOutput());
            }

            // Git status
            $statusProcess = new Process([$gitBinary, 'status', '--short'], base_path());
            $statusProcess->run();
            if ($statusProcess->isSuccessful()) {
                $gitStatus = trim($statusProcess->getOutput());
            }
        }

        // Webhook secret from env or config
        $webhookSecret = env('GIT_WEBHOOK_SECRET', md5(config('app.key') . 'git-update'));
        $webhookUrl = url('api/git-webhook?secret=' . $webhookSecret);

        return view('backEnd.system_update.index', compact(
            'isGitInstalled',
            'gitVersion',
            'isGitRepo',
            'currentBranch',
            'lastCommit',
            'remoteUrl',
            'gitStatus',
            'webhookUrl',
            'webhookSecret'
        ));
    }

    /**
     * Perform Git Pull and optionally run migrations and clear cache
     */
    public function pull(Request $request)
    {
        $gitBinary = $this->getGitBinary();
        $branch = $request->input('branch');
        $runMigrate = $request->boolean('run_migrate', true);
        $runOptimize = $request->boolean('run_optimize', true);
        $autoStash = $request->boolean('auto_stash', true);

        $output = [];
        $hasError = false;

        if (!file_exists(base_path('.git'))) {
            return redirect()->back()->with('error', 'Git repository (.git) is not initialized in this folder.');
        }

        // Detect branch if not provided
        if (empty($branch)) {
            $branchProcess = new Process([$gitBinary, 'branch', '--show-current'], base_path());
            $branchProcess->run();
            $branch = trim($branchProcess->getOutput()) ?: 'main';
        }

        $output[] = "=== SYSTEM UPDATE INITIATED ===";
        $output[] = "Time: " . date('Y-m-d H:i:s');
        $output[] = "Target Branch: " . $branch;
        $output[] = "Working Directory: " . base_path();
        $output[] = "";

        // 1. Auto-stash if requested
        if ($autoStash) {
            $output[] = "--- Step 1: Checking for local uncommitted changes ---";
            $stashProcess = new Process([$gitBinary, 'stash'], base_path());
            $stashProcess->run();
            $output[] = trim($stashProcess->getOutput() ?: $stashProcess->getErrorOutput()) ?: 'No local changes to stash.';
            $output[] = "";
        }

        // 2. Git Fetch & Pull
        $output[] = "--- Step 2: Running Git Pull ---";
        $pullProcess = new Process([$gitBinary, 'pull', 'origin', $branch], base_path(), null, null, 120);
        $pullProcess->run();

        $pullOutput = trim($pullProcess->getOutput());
        $pullError  = trim($pullProcess->getErrorOutput());

        if (!empty($pullOutput)) {
            $output[] = $pullOutput;
        }
        if (!empty($pullError)) {
            $output[] = $pullError;
        }

        if (!$pullProcess->isSuccessful()) {
            $hasError = true;
            $output[] = "[FAILED] Git pull encountered an error (exit code: " . $pullProcess->getExitCode() . ")";
        } else {
            $output[] = "[SUCCESS] Git pull completed successfully!";
        }
        $output[] = "";

        // 3. Database Migration
        if ($runMigrate) {
            $output[] = "--- Step 3: Running Database Migrations ---";
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output[] = Artisan::output() ?: "Nothing to migrate.";
                $output[] = "[SUCCESS] Migrations executed.";
            } catch (\Throwable $e) {
                $hasError = true;
                $output[] = "[ERROR] Migration failed: " . $e->getMessage();
            }
            $output[] = "";
        }

        // 4. Cache and Optimization
        if ($runOptimize) {
            $output[] = "--- Step 4: Clearing Caches & Rebuilding Bootstrap ---";
            try {
                Artisan::call('optimize:clear');
                $output[] = Artisan::output();
                $output[] = "[SUCCESS] Caches cleared successfully.";
            } catch (\Throwable $e) {
                $output[] = "[WARNING] Cache clear error: " . $e->getMessage();
            }
            $output[] = "";
        }

        $output[] = "=== UPDATE FINISHED ===";

        $logText = implode(PHP_EOL, $output);

        if ($hasError) {
            return redirect()->back()
                ->with('error', 'Update completed with warnings or errors. Check the log below.')
                ->with('update_output', $logText);
        }

        return redirect()->back()
            ->with('success', '✅ Application updated successfully from Git!')
            ->with('update_output', $logText);
    }

    /**
     * Run migrations only
     */
    public function migrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $msg = Artisan::output() ?: "Database is already up to date.";
            return redirect()->back()->with('success', '✅ Migrations finished: ' . $msg)->with('update_output', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', '❌ Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('optimize:clear');
            $msg = Artisan::output() ?: "Caches cleared.";
            return redirect()->back()->with('success', '✅ All caches cleared!')->with('update_output', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', '❌ Cache error: ' . $e->getMessage());
        }
    }

    /**
     * Webhook endpoint for GitHub/GitLab automated deployments
     */
    public function webhook(Request $request)
    {
        $expectedSecret = env('GIT_WEBHOOK_SECRET', md5(config('app.key') . 'git-update'));
        $providedSecret = $request->query('secret') ?: $request->header('X-Hub-Signature-256') ?: $request->header('X-Gitlab-Token');

        if (empty($providedSecret) || $providedSecret !== $expectedSecret) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized webhook token'], 401);
        }

        $gitBinary = $this->getGitBinary();
        $branchProcess = new Process([$gitBinary, 'branch', '--show-current'], base_path());
        $branchProcess->run();
        $branch = trim($branchProcess->getOutput()) ?: 'main';

        $pullProcess = new Process([$gitBinary, 'pull', 'origin', $branch], base_path(), null, null, 120);
        $pullProcess->run();

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('optimize:clear');

        return response()->json([
            'status'  => $pullProcess->isSuccessful() ? 'success' : 'failed',
            'branch'  => $branch,
            'git_out' => trim($pullProcess->getOutput() . ' ' . $pullProcess->getErrorOutput()),
            'message' => 'Webhook pull and cache clear executed.',
        ]);
    }

    /**
     * Find the Git executable binary on the server
     */
    protected function getGitBinary(): string
    {
        if (env('GIT_PATH')) {
            return env('GIT_PATH');
        }

        $commonPaths = [
            'git',
            '/usr/bin/git',
            '/usr/local/bin/git',
            '/usr/local/cpanel/3rdparty/bin/git',
        ];

        foreach ($commonPaths as $path) {
            try {
                $process = new Process([$path, '--version']);
                $process->run();
                if ($process->isSuccessful()) {
                    return $path;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return 'git';
    }
}
