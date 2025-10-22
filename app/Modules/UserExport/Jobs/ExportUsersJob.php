<?php

namespace App\Modules\UserExport\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Imtigger\LaravelJobStatus\Trackable;

class ExportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public string $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->prepareStatus(); // enable progress tracking
    }

    public function handle(): void
    {
        try {
            $exportDir = storage_path('exports');
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0777, true); // create directory if missing
            }

            $filePath = $exportDir . DIRECTORY_SEPARATOR . $this->fileName;

            // open CSV file
            $handle = fopen($filePath, 'w');
            if (!$handle) {
                throw new \Exception("Cannot open file for writing: {$filePath}");
            }

            // write CSV header
            fputcsv($handle, [
                'ID', 'Name', 'Email', 'Username', 'Phone', 'Address',
                'Birthdate', 'Gender', 'Is Active', 'Email Verified At', 'Created At'
            ]);

            $total = User::count();
            $this->setProgressMax($total);
            $count = 0;
            $startTime = microtime(true);

            foreach (User::cursor() as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->username,
                    $user->phone,
                    $user->address,
                    optional($user->birthdate)->format('Y-m-d'),
                    $user->gender,
                    $user->is_active ? 'Yes' : 'No',
                    $user->email_verified_at,
                    $user->created_at,
                ]);

                $count++;
                $this->setProgressNow($count);

                // calculate time metrics
                $timeUsed = microtime(true) - $startTime;
                $timeRemaining = $count > 0 ? ($timeUsed / $count) * ($total - $count) : 0;

                $this->setOutput([
                    'file' => $this->fileName,
                    'time_used' => round($timeUsed, 2),
                    'time_remaining' => round($timeRemaining, 2),
                ]);
            }

            fclose($handle);

            // mark job done
            $this->setOutput([
                'file' => $this->fileName,
                'time_used' => round(microtime(true) - $startTime, 2),
                'time_remaining' => 0,
            ]);

        } catch (\Throwable $e) {
            \Log::error('ExportUsersJob failed: ' . $e->getMessage());
            throw $e; // let queue handle failure
        }
    }
}
