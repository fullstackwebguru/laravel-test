<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::with('category')->orderBy('created_at', 'desc')->get();
        return view('welcome', ['articles' => $articles]);
    }
}
