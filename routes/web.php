<?php

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/users', [UserController::class,'index'])->name('users');

// Route::get('/export', [UserController::class,'export'])->name('export');

// Route::get('/without', [UserController::class, 'exportWithoutCursor'])->name('without');

// Route::get('/queue', [UserController::class, 'exportQueued']);


require app_path('Modules/UserExport/Routes/web.php');
