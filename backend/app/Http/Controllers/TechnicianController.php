<?php

namespace App\Http\Controllers;

class TechnicianController extends Controller
{
    public function create()
    {
        return view('pages.forms.technicians-create');
    }
}
