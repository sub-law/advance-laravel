<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Author;

class AuthorController extends Controller
{
    public function add()
    {
        return view('add');
    }

    public function create(Request $request)
    {
        $form = $request->all();
        Author::create($form);
        return redirect('/');
    }

    public function index()
    {
        $authors = Author::all();
        return view('index', ['authors' => $authors]);
    }
}
