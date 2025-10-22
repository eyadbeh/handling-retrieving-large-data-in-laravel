<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="p-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Export Users</h2>

        <button id="startExport" class="btn btn-primary">Start Export</button>

        <div id="progressSection" class="mt-4" style="display:none;">
            <div class="progress">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">
                    0%</div>
            </div>
            <p id="timeInfo" class="mt-2"></p>
        </div>

        <div id="download" class="mt-4"></div>

        <hr class="my-5">

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Active</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->gender }}</td>
                        <td>{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $users->links() }}
    </div>

    <script>
        let jobId = null;
        let fileName = null;
        let checkInterval = null;

        $('#startExport').click(function () {
            $('#startExport').prop('disabled', true);
            $('#progressSection').show();
            $('#download').html('');
            $('#progressBar').css('width', '0%').text('0%');
            $('#timeInfo').text('Starting export...');

            $.post('{{ route('users.export.start') }}', { _token: '{{ csrf_token() }}' }, function (response) {
                jobId = response.jobId;
                fileName = response.fileName;
                $('#timeInfo').text('Export running...');
                checkInterval = setInterval(checkProgress, 1000);
            });
        });

        function checkProgress() {
            $.get('{{ route('users.export.progress') }}', { jobId: jobId }, function (data) {
                if (data.progress !== undefined) {
                    $('#progressBar').css('width', data.progress + '%').text(data.progress + '%');
                    $('#timeInfo').text(`Time used: ${data.time_used}s | Estimated remaining: ${data.time_remaining}s`);
                }

                if (data.status === 'finished' || data.progress >= 100) {
                    clearInterval(checkInterval);
                    $('#startExport').prop('disabled', false);
                    $('#timeInfo').text('Export complete! Downloading file...');
                    autoDownloadFile();
                }
            }).fail(() => {
                clearInterval(checkInterval);
                $('#timeInfo').text('Error checking progress.');
            });
        }

        // Auto-download when file ready
        function autoDownloadFile() {
            $.get('{{ route("users.export.status") }}', { file: fileName }, function (res) {
                if (res.exists) {
                    // Trigger download
                    const a = document.createElement('a');
                    a.href = res.downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    $('#timeInfo').text('File downloaded successfully!');
                } else {
                    // retry after 1s if file not yet ready
                    setTimeout(autoDownloadFile, 1000);
                }
            });
        }
    </script>
</body>

</html>