<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class BackupController extends Controller
{
    public function index(): View
    {
        Gate::authorize('backup_view');

        $title = 'Backup Database & Storage';
        $connection = config('database.default', 'mysql');
        $databaseName = config("database.connections.{$connection}.database", '-');

        $tableCount = 0;
        try {
            $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tableCount = count($tables);
        } catch (\Throwable $e) {
            $tableCount = 0;
        }

        $storagePath = storage_path('app/public');
        $storageFileCount = 0;
        $storageTotalBytes = 0;

        if (is_dir($storagePath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($storagePath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                    $storageFileCount++;
                    $storageTotalBytes += $file->getSize();
                }
            }
        }

        $storageSizeFormatted = $this->formatBytes($storageTotalBytes);

        return view('pages.backup.index', compact(
            'title',
            'connection',
            'databaseName',
            'tableCount',
            'storageFileCount',
            'storageSizeFormatted'
        ));
    }

    /**
     * Unduh Backup Database (.sql)
     */
    public function exportDatabase(): StreamedResponse|RedirectResponse
    {
        Gate::authorize('backup_view');

        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $connection = config('database.default', 'mysql');
            $databaseName = config("database.connections.{$connection}.database", 'database');
            $safeDbName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $databaseName);
            $filename = "backup_db_{$safeDbName}_" . now()->format('Y-m-d_His') . '.sql';

            $pdo = DB::connection()->getPdo();
            $tablesResult = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

            $tables = [];
            foreach ($tablesResult as $row) {
                $values = array_values((array) $row);
                if (!empty($values[0])) {
                    $tables[] = $values[0];
                }
            }

            return response()->streamDownload(function () use ($pdo, $tables, $databaseName) {
                echo "-- =========================================================\n";
                echo "-- Database Backup : {$databaseName}\n";
                echo '-- Generated At    : ' . now()->format('Y-m-d H:i:s') . "\n";
                echo "-- =========================================================\n\n";
                echo "SET FOREIGN_KEY_CHECKS=0;\n";
                echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
                echo "SET time_zone = \"+00:00\";\n\n";

                foreach ($tables as $table) {
                    echo "-- ---------------------------------------------------------\n";
                    echo "-- Struktur & Data untuk tabel `{$table}`\n";
                    echo "-- ---------------------------------------------------------\n\n";
                    echo "DROP TABLE IF EXISTS `{$table}`;\n";

                    $createTableRow = DB::select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createTableRow)) {
                        $rowArr = (array) $createTableRow[0];
                        $createSql = $rowArr['Create Table'] ?? array_values($rowArr)[1] ?? '';
                        if ($createSql) {
                            echo $createSql . ";\n\n";
                        }
                    }

                    $stmt = $pdo->query("SELECT * FROM `{$table}`");
                    if ($stmt) {
                        $batch = [];
                        $columnsSql = null;

                        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                            if ($columnsSql === null) {
                                $cols = array_map(fn ($col) => "`{$col}`", array_keys($row));
                                $columnsSql = implode(', ', $cols);
                            }

                            $vals = [];
                            foreach ($row as $val) {
                                if ($val === null) {
                                    $vals[] = 'NULL';
                                } elseif (is_int($val) || is_float($val)) {
                                    $vals[] = $val;
                                } else {
                                    $vals[] = $pdo->quote((string) $val);
                                }
                            }
                            $batch[] = '(' . implode(', ', $vals) . ')';

                            if (count($batch) >= 100) {
                                echo "INSERT INTO `{$table}` ({$columnsSql}) VALUES\n" . implode(",\n", $batch) . ";\n";
                                $batch = [];
                            }
                        }

                        if (!empty($batch) && $columnsSql !== null) {
                            echo "INSERT INTO `{$table}` ({$columnsSql}) VALUES\n" . implode(",\n", $batch) . ";\n";
                        }
                        echo "\n";
                    }
                }

                echo "SET FOREIGN_KEY_CHECKS=1;\n";
            }, $filename, [
                'Content-Type' => 'application/sql; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            Alert::error('Gagal', 'Gagal membuat backup database: ' . $e->getMessage());
            return redirect()
                ->route('backup.index')
                ->with('error', 'Gagal membuat backup database: ' . $e->getMessage());
        }
    }

    /**
     * Import / Pulihkan Database dari berkas .sql atau .zip
     */
    public function importDatabase(Request $request): RedirectResponse
    {
        Gate::authorize('backup_view');

        $request->validate([
            'sql_file' => ['required', 'file', 'max:512000'],
        ], [
            'sql_file.required' => 'Silakan pilih berkas backup database (.sql atau .zip) terlebih dahulu.',
            'sql_file.file' => 'Berkas yang diunggah tidak valid.',
            'sql_file.max' => 'Ukuran berkas maksimal 500 MB.',
        ]);

        $file = $request->file('sql_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['sql', 'zip', 'txt'], true)) {
            Alert::error('Gagal', 'Format berkas harus berupa .sql atau .zip yang berisi berkas .sql.');
            return redirect()
                ->route('backup.index')
                ->with('error', 'Format berkas harus berupa .sql atau .zip yang berisi berkas .sql.');
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $sqlContent = '';

            if ($ext === 'zip') {
                $zip = new ZipArchive;
                if ($zip->open($file->getRealPath()) !== true) {
                    Alert::error('Gagal', 'Gagal membuka arsip ZIP database.');
                    return redirect()
                        ->route('backup.index')
                        ->with('error', 'Gagal membuka arsip ZIP database.');
                }

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if ($entryName && str_ends_with(strtolower($entryName), '.sql')) {
                        $sqlContent = (string) $zip->getFromIndex($i);
                        break;
                    }
                }
                $zip->close();

                if (trim($sqlContent) === '') {
                    Alert::error('Gagal', 'Tidak ditemukan berkas .sql di dalam arsip ZIP tersebut.');
                    return redirect()
                        ->route('backup.index')
                        ->with('error', 'Tidak ditemukan berkas .sql di dalam arsip ZIP tersebut.');
                }
            } else {
                $sqlContent = (string) file_get_contents($file->getRealPath());
            }

            // Hapus UTF-8 BOM jika ada
            $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', $sqlContent);

            if (trim($sqlContent) === '') {
                Alert::error('Gagal', 'Berkas SQL yang diunggah kosong.');
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Berkas SQL yang diunggah kosong.');
            }

            $statements = $this->splitSqlStatements($sqlContent);
            if (empty($statements)) {
                Alert::error('Gagal', 'Tidak ditemukan perintah SQL yang valid di dalam berkas.');
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Tidak ditemukan perintah SQL yang valid di dalam berkas.');
            }

            $pdo = DB::connection()->getPdo();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');

            $executedCount = 0;
            foreach ($statements as $statement) {
                $trimmed = trim($statement);
                if ($trimmed === '') {
                    continue;
                }
                $pdo->exec($trimmed);
                $executedCount++;
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

            Alert::success('Berhasil', "Database berhasil dipulihkan! ({$executedCount} perintah SQL berhasil dijalankan).");
            return redirect()
                ->route('backup.index')
                ->with('success', "Database berhasil dipulihkan! ({$executedCount} perintah SQL berhasil dijalankan).");
        } catch (\Throwable $e) {
            try {
                DB::connection()->getPdo()->exec('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $ignore) {
            }

            Alert::error('Gagal', 'Gagal memulihkan database: ' . $e->getMessage());
            return redirect()
                ->route('backup.index')
                ->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Backup Storage Public (.zip)
     */
    public function exportStorage(): BinaryFileResponse|RedirectResponse
    {
        Gate::authorize('backup_view');

        if (!class_exists(ZipArchive::class)) {
            Alert::error('Gagal', 'Ekstensi PHP ZipArchive tidak aktif di server ini.');
            return redirect()
                ->route('backup.index')
                ->with('error', 'Ekstensi PHP ZipArchive tidak aktif di server ini.');
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $storagePublicPath = storage_path('app/public');
            if (!is_dir($storagePublicPath)) {
                mkdir($storagePublicPath, 0755, true);
            }

            $tempDir = storage_path('app/temp_backups');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filename = 'backup_storage_public_' . now()->format('Y-m-d_His') . '.zip';
            $zipFilePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

            $zip = new ZipArchive;
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                Alert::error('Gagal', 'Gagal membuat berkas arsip ZIP storage.');
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Gagal membuat berkas arsip ZIP storage.');
            }

            $realBasePath = realpath($storagePublicPath);
            $addedCount = 0;

            if ($realBasePath !== false) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($realBasePath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $item) {
                    $filePath = $item->getRealPath();
                    if ($filePath === false) {
                        continue;
                    }

                    $relativePath = substr($filePath, strlen($realBasePath) + 1);
                    $relativePath = str_replace('\\', '/', $relativePath);

                    if ($relativePath === '.gitignore' || str_starts_with($relativePath, '.')) {
                        continue;
                    }

                    if ($item->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } elseif ($item->isFile()) {
                        $zip->addFile($filePath, $relativePath);
                        $addedCount++;
                    }
                }
            }

            if ($addedCount === 0) {
                $zip->addFromString('README.txt', 'Backup storage/app/public pada ' . now()->format('Y-m-d H:i:s'));
            }

            $zip->close();

            return response()->download($zipFilePath, $filename, [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Alert::error('Gagal', 'Gagal membuat backup storage ZIP: ' . $e->getMessage());
            return redirect()
                ->route('backup.index')
                ->with('error', 'Gagal membuat backup storage ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Import / Pulihkan Storage Public dari berkas .zip
     */
    public function importStorage(Request $request): RedirectResponse
    {
        Gate::authorize('backup_view');

        $request->validate([
            'storage_zip' => ['required', 'file', 'max:512000'],
        ], [
            'storage_zip.required' => 'Silakan pilih berkas arsip ZIP storage terlebih dahulu.',
            'storage_zip.file' => 'Berkas yang diunggah tidak valid.',
            'storage_zip.max' => 'Ukuran berkas ZIP maksimal 500 MB.',
        ]);

        $file = $request->file('storage_zip');
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext !== 'zip') {
            Alert::error('Gagal', 'Format berkas harus berupa arsip .zip.');
            return redirect()
                ->route('backup.index')
                ->with('error', 'Format berkas harus berupa arsip .zip.');
        }

        if (!class_exists(ZipArchive::class)) {
            Alert::error('Gagal', 'Ekstensi PHP ZipArchive tidak aktif di server ini.');
            return redirect()
                ->route('backup.index')
                ->with('error', 'Ekstensi PHP ZipArchive tidak aktif di server ini.');
        }

        try {
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            $storagePublicPath = storage_path('app/public');
            if (!is_dir($storagePublicPath)) {
                mkdir($storagePublicPath, 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($file->getRealPath()) !== true) {
                Alert::error('Gagal', 'Gagal membuka berkas ZIP. Pastikan berkas ZIP tidak rusak.');
                return redirect()
                    ->route('backup.index')
                    ->with('error', 'Gagal membuka berkas ZIP. Pastikan berkas ZIP tidak rusak.');
            }

            $restoredFiles = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (!$entryName) {
                    continue;
                }

                $normalized = str_replace('\\', '/', $entryName);
                $normalized = ltrim($normalized, '/');

                // Cegah directory traversal & skip file sistem/sampah
                if (
                    $normalized === '' ||
                    str_contains($normalized, '../') ||
                    str_contains($normalized, '..\\') ||
                    str_starts_with($normalized, '__MACOSX/') ||
                    basename($normalized) === '.gitignore' ||
                    basename($normalized) === '.DS_Store' ||
                    $normalized === 'README.txt'
                ) {
                    continue;
                }

                // Jika di dalam ZIP terdapat prefix folder 'storage/app/public/' atau 'public/'
                if (str_starts_with($normalized, 'storage/app/public/')) {
                    $normalized = substr($normalized, strlen('storage/app/public/'));
                } elseif (str_starts_with($normalized, 'public/')) {
                    $normalized = substr($normalized, strlen('public/'));
                }

                if ($normalized === '') {
                    continue;
                }

                $targetPath = $storagePublicPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);

                // Jika entry adalah direktori
                if (str_ends_with($entryName, '/')) {
                    if (!is_dir($targetPath)) {
                        mkdir($targetPath, 0755, true);
                    }
                    continue;
                }

                $parentDir = dirname($targetPath);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0755, true);
                }

                $stream = $zip->getStream($entryName);
                if ($stream !== false) {
                    $outStream = fopen($targetPath, 'wb');
                    if ($outStream !== false) {
                        stream_copy_to_stream($stream, $outStream);
                        fclose($outStream);
                        $restoredFiles++;
                    }
                    fclose($stream);
                }
            }

            $zip->close();

            // Pastikan symbolic link public/storage tersedia
            if (!file_exists(public_path('storage'))) {
                try {
                    Artisan::call('storage:link');
                } catch (\Throwable $ignore) {
                }
            }

            Alert::success('Berhasil', "Backup storage berhasil dipulihkan! ({$restoredFiles} berkas berhasil diekstrak ke storage/app/public).");
            return redirect()
                ->route('backup.index')
                ->with('success', "Backup storage berhasil dipulihkan! ({$restoredFiles} berkas berhasil diekstrak ke storage/app/public).");
        } catch (\Throwable $e) {
            Alert::error('Gagal', 'Gagal memulihkan storage: ' . $e->getMessage());
            return redirect()
                ->route('backup.index')
                ->with('error', 'Gagal memulihkan storage: ' . $e->getMessage());
        }
    }

    /**
     * Memisahkan perintah SQL dengan aman tanpa memotong tanda titik koma (;) di dalam string/kutipan.
     *
     * @return array<int, string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $length = strlen($sql);
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;
        $inLineComment = false;
        $inBlockComment = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

            // Akhiri line comment saat newline
            if ($inLineComment) {
                if ($char === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            // Akhiri block comment saat */
            if ($inBlockComment) {
                if ($char === '*' && $next === '/') {
                    $inBlockComment = false;
                    $i++;
                }
                continue;
            }

            // Deteksi komentar jika sedang tidak berada di dalam kutipan
            if (!$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                // Komentar -- (diikuti spasi, tab, atau newline)
                if ($char === '-' && $next === '-') {
                    $afterNext = ($i + 2 < $length) ? $sql[$i + 2] : "\n";
                    if ($afterNext === ' ' || $afterNext === "\t" || $afterNext === "\r" || $afterNext === "\n") {
                        $inLineComment = true;
                        $i++;
                        continue;
                    }
                }

                // Komentar #
                if ($char === '#') {
                    $inLineComment = true;
                    continue;
                }

                // Block comment /* ... */ (kecuali MySQL conditional /*! ... */)
                if ($char === '/' && $next === '*') {
                    $third = ($i + 2 < $length) ? $sql[$i + 2] : '';
                    if ($third !== '!') {
                        $inBlockComment = true;
                        $i++;
                        continue;
                    }
                }
            }

            // Handle karakter escape (\) di dalam kutipan
            if (($inSingleQuote || $inDoubleQuote) && $char === '\\') {
                $current .= $char;
                if ($i + 1 < $length) {
                    $current .= $sql[$i + 1];
                    $i++;
                }
                continue;
            }

            if ($char === "'" && !$inDoubleQuote && !$inBacktick) {
                $inSingleQuote = !$inSingleQuote;
            } elseif ($char === '"' && !$inSingleQuote && !$inBacktick) {
                $inDoubleQuote = !$inDoubleQuote;
            } elseif ($char === '`' && !$inSingleQuote && !$inDoubleQuote) {
                $inBacktick = !$inBacktick;
            }

            // Titik koma di luar kutipan adalah pemisah statement SQL
            if ($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }
}

