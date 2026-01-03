<?php

namespace App\Http\Controllers;
use App\Models\Section;

use Illuminate\Http\Request;

class SectionController extends Controller
{
    function index()
    {
        $sections = Section::all();
        return response()->json($sections);
    }
}
