<?php

namespace App;

use App\Models\Fixture;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Backup
{
    const DATE_FORMAT = 'Y_m_d_H_i_s';

    public static function create(): string|array
    {
        if (!self::isDirty())
            return "Kein Backup gemacht. Keine Änderungen seit dem letzten Backup.";

        self::deleteOld(180, 10);

        $mysqldump = 'mysqldump';
        $gzip = 'gzip';

        $return = 0;
        $output = [];
        $filePath = self::path(self::makeFilename());

        $cmd = $mysqldump . ' --user=' . config('database.connections.mysql.username') .
            ' --password=' . config('database.connections.mysql.password') .
            ' ' . config('database.connections.mysql.database') .
            ' | ' . $gzip . ' -c > ' . $filePath;

        exec($cmd, $output, $return);

        if (file_exists($filePath)) {
            self::copyS3($filePath);

            return $filePath;
        } else {
            Log::error('Backup fehlgeschlagen: ' . $filePath);
            User::root()->sendMail('Backup fehlgeschlagen');

            return $output;
        }
    }

    public static function restore($filename, $backupBefore = true)
    {
        if ($backupBefore) {
            self::create();
        }

        $path = self::path($filename);

        if (file_exists($path))
        {
            shell_exec('gunzip ' . $path);
            $unzippedPath = substr($path, 0, strlen($path) - 3);
            $cmd = 'mysql --user=' . config('database.connections.mysql.username') .
                ' --password=' . config('database.connections.mysql.password') .
                ' '  . config('database.connections.mysql.database') .
                ' < ' . $unzippedPath;
            shell_exec($cmd);
            shell_exec('gzip ' . $unzippedPath);
        }

    }

    public static function prefix(): string
    {
        return config('database.connections.mysql.database') . '_';
    }

    public static function path(string $filename = ''): string
    {
        $filename = trim($filename, DIRECTORY_SEPARATOR);

        return storage_path('backups' . DIRECTORY_SEPARATOR . $filename);
    }

    public static function makeFilename(): string
    {
        return self::prefix() . Carbon::now()->format(self::DATE_FORMAT) . '.sql.gz';
    }

    private static function copyS3(string $path): void
    {
        if (!env('AWS_ENABLED', false))
            return;

        $destinationPath = env('AWS_ROOT', '') . DIRECTORY_SEPARATOR .
            pathinfo($path, PATHINFO_BASENAME);

        $fp = fopen($path, 'r');

        Storage::disk('s3')->put($destinationPath, $fp);

        fclose($fp);
    }

    public static function all()
    {
        $result = [];
        $id = 1;
        $now = Carbon::now();
        $pattern = '/' . self::prefix() .'(.*)\.sql/';

        foreach (glob(self::path('*.sql.gz')) as $filename) {
            preg_match($pattern, $filename, $matches);
            if (!$matches)
                continue;

            $date = Carbon::createFromFormat(self::DATE_FORMAT, $matches[1]);

            $result[] = [
                'id' => $id++,
                'timestamp' => $date->getTimestamp(),
                'date' => $date->format('Y-m-d H:i:s'),
                'filename' => basename($filename),
                'age' => $now->diffInMinutes($date),
            ];
        }

        return Arr::sort($result, function ($value) {
            return $value['age'];
        });
    }

    public static function deleteOld($days, $retain = 1): int
    {
        $minutes = $days * 24 * 60;
        $backups = self::all();
        $count = count($backups);
        $deleted = 0;

        while ($count > $retain &&  ($backups[$count-1]["age"] > $minutes)) {
            unlink(self::path(last($backups)['filename']));
            $deleted++;
            $count--;
        }

        return $deleted;
    }

    public static function isDirty()
    {
        $lastUpdate = Fixture::max('updated_at');
        if ($lastUpdate === null)
            return false;

        $backups = self::all();

        return (!$backups || $lastUpdate > head($backups)['date']);
    }

}
