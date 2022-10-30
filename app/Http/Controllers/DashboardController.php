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
            'categoriesMoreLinks' => $categories,
            'totalCategories' => Category::count(),
            'totalLinks' => Link::count(),
            'totalCodes' => Code::count(),
        ];

        return response()->json($stats);
    }
}
