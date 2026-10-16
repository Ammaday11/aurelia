<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BackupException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RestoreUploadedBackupRequest;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backups) {}

    public function index(): View
    {
        $this->authorize('manage-backups');

        $backups = $this->backups->list();

        return view('admin.backups.index', [
            'title' => 'Backups',
            'byType' => [
                'daily' => $backups->where('type', 'daily')->values(),
                'weekly' => $backups->where('type', 'weekly')->values(),
                'monthly' => $backups->where('type', 'monthly')->values(),
                'manual' => $backups->where('type', 'manual')->values(),
                'pre-restore' => $backups->where('type', 'pre-restore')->values(),
            ],
            'totalSize' => $backups->sum('size'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-backups');

        try {
            $backup = $this->backups->create('manual');

            return back()->with('success', "Backup created: {$backup->filename} ({$backup->humanSize()}).");
        } catch (BackupException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $this->authorize('manage-backups');

        try {
            return $this->backups->downloadResponse($filename);
        } catch (BackupException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->authorize('manage-backups');

        try {
            $this->backups->delete($filename);

            return back()->with('success', 'Backup deleted.');
        } catch (BackupException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function restore(string $filename): RedirectResponse
    {
        $this->authorize('manage-backups');

        try {
            $this->backups->restore($filename);

            return back()->with('success', "Restored from {$filename}. A safety backup of the previous state was taken first.");
        } catch (BackupException $e) {
            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }
    }

    public function restoreUpload(RestoreUploadedBackupRequest $request): RedirectResponse
    {
        try {
            $this->backups->restoreFromUpload($request->file('backup'));

            return back()->with('success', 'Restored from the uploaded backup. A safety backup of the previous state was taken first.');
        } catch (BackupException $e) {
            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }
    }
}
