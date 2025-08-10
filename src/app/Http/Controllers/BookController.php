<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        //DB::enableQueryLog();
        $items = Book::with('author')->get();
       // dd(DB::getQueryLog());
        return view('book.index', ['items' => $items]);
    }
    public function add()
    {
        return view('book.add');
    }
    public function create(Request $request)
    {
        $this->validate($request, Book::$rules);
        $form = $request->all();
        Book::create($form);
        return redirect('/book');
    }
}
