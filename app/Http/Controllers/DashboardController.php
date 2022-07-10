<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Code;
use App\Models\Link;

class DashboardController extends Controller
{
    public function getStats()
    {
        $categories = Category::withCount('links')->orderBy('links_count', 'desc')->get()->take(10);

        $stats = [
            'categories_more_links' => $categories,
            'total_categories' => Category::count(),
            'total_links' => Link::count(),
            'total_codes' => Code::count(),
        ];

        return response()->json($stats);
    }
}
