<?php

use App\Modules\UserExport\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/users', [UserController::class,'index'])->name('users');

// Route::get('/export', [UserController::class,'export'])->name('export');

// Route::get('/without', [UserController::class, 'exportWithoutCursor'])->name('without');

// Route::get('/queue', [UserController::class, 'exportQueued']);


Route::get('/users/export', [UserController::class, 'exportView'])->name('users.export.view');
Route::post('/users/export/start', [UserController::class, 'startExport'])->name('users.export.start');
Route::get('/users/export/progress', [UserController::class, 'checkExportProgress'])->name('users.export.progress');
Route::get('/users/export/status', [UserController::class, 'checkExportStatus'])->name('users.export.status');
Route::get('/users/export/download/{file}', [UserController::class, 'downloadExport'])->name('download.export');
