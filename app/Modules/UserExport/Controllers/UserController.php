<?php

namespace App\Modules\UserExport\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserExport\Jobs\ExportUsersJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Imtigger\LaravelJobStatus\JobStatus;

class UserController extends Controller
{
    public function exportView()
    {
        $users = User::latest()->paginate(10);
        return view('UserExport::export', compact('users'));
    }

    public function startExport()
    {
        $fileName = 'users_export_' . time() . '.csv';
        $job = new ExportUsersJob($fileName);
        dispatch($job);

        return response()->json([
            'status' => 'started',
            'jobId' => $job->getJobStatusId(),
            'fileName' => $fileName,
        ]);
    }

    public function checkExportProgress(Request $request)
    {
        $jobId = $request->query('jobId');
        $status = JobStatus::find($jobId);

        if (!$status) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return response()->json([
            'status' => $status->status,
            'progress' => $status->progress_percentage ?? 0,
            'time_used' => $status->output['time_used'] ?? 0,
            'time_remaining' => $status->output['time_remaining'] ?? 0,
            'output' => $status->output,
        ]);
    }

    public function checkExportStatus(Request $request)
    {
        $fileName = $request->query('file');
        $path = storage_path("exports/{$fileName}");

        return response()->json([
            'exists' => file_exists($path),
            'downloadUrl' => file_exists($path)
                ? route('download.export', ['file' => $fileName])
                : null,
        ]);
    }

    public function downloadExport($file)
    {
        $path = storage_path("exports/{$file}");
        abort_unless(file_exists($path), 404);
        return response()->download($path);
    }
}
