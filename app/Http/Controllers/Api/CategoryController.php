<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách danh mục chi tiêu (cho dropdown FE)
     */
    public function index()
    {
        return response()->json(Category::all());
    }
}
