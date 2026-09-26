<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('history.index', ['records' => $request->user()->borrowRecords()->with('book')->latest('borrow_date')->paginate(15)]);
    }
}
