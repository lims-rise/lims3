<?php

namespace App\Http\Controllers;

use App\Support\Lab;
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function switch(Request $request)
    {
        $lab = (string) $request->input('lab');
        abort_unless(isset(Lab::LABS[$lab]), 422);

        session(['lab' => $lab]);

        return back();
    }
}