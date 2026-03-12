<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlantelController extends Controller
{
    public function index()
    {
        return view('admin.plantel.index');
    }

    public function create()
    {
        return view('admin.plantel.create');
    }
}
