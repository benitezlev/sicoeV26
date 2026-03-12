<?php

namespace App\Http\Controllers;

use App\Models\Importacion;
use Illuminate\Http\Request;

class ImportacionController extends Controller
{
    public function index()
    {
        $importaciones = Importacion::with('usuario')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('importaciones.index', compact('importaciones'));
    }

}
