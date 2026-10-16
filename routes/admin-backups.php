<?php

use App\Http\Controllers\Admin\BackupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backup Routes
|--------------------------------------------------------------------------
|
| Required inside the admin.* route group in routes/admin.php — kept in
| its own file since this group needs several non-resourceful actions
| (download, restore, restore-upload) alongside the usual list/create/
| delete ones. See App\Services\BackupService for what a backup contains.
|
*/

Route::prefix('backups')->name('backups.')->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::post('/', [BackupController::class, 'store'])->name('store');
    Route::post('/restore-upload', [BackupController::class, 'restoreUpload'])->name('restore-upload');
    Route::get('/{filename}/download', [BackupController::class, 'download'])->name('download');
    Route::post('/{filename}/restore', [BackupController::class, 'restore'])->name('restore');
    Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
});
