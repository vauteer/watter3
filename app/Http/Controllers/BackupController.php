<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Http\Resources\BackupResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index():Response
    {
        return inertia('Backups/Index', [
            'backups' => BackupResource::collection(Backup::all()),
            'isDirty' => Backup::isDirty(),
        ]);
    }

    public function create(Request $request):Response
    {
        Backup::create();

        return $this->index($request);
    }

    public function restore(Request $request): RedirectResponse
    {
        $filename = $request->input('filename');

        Backup::restore($filename);

        return redirect()->route('tournaments')
            ->with('success', "Das Backup wurde wiederhergestellt.");
    }

    public function download(string $filename): BinaryFileResponse
    {
        return \response()->download(Backup::path($filename), $filename);
    }
}
