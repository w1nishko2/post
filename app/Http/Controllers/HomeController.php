<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Получаем ботов текущего пользователя с оптимизацией запросов
        $bots = auth()->user()->telegramBots()
            ->withCount(['products', 'categories', 'orders'])
            ->with(['products' => function($query) {
                $query->select('id', 'telegram_bot_id', 'name', 'price', 'is_active')
                      ->where('is_active', true)
                      ->latest()
                      ->limit(3);
            }])
            ->latest()
            ->get();
        
        return view('home', compact('bots'));
    }
}
