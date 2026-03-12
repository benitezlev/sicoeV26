<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use Illuminate\Http\Request;

class DocenteController extends Controller
{

    public function index()
    {
        return view('admin.docentes.index');
    }

    public function create()
    {
        return view('admin.docentes.create');
    }

    public function show(Docente $docente)
    {
        return view('admin.docentes.show', compact('docente'));
    }

    public function edit(Docente $docente)
    {
        return view('admin.docentes.edit', compact('docente'));
    }


}
