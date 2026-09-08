<?php

namespace App\Http\Controllers;

use App\Models\CertificationCategory;
use Illuminate\Http\JsonResponse;

class CertificationCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = CertificationCategory::with('activeTypes')->get();

        return response()->json([
            'data' => $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),
            'types' => $categories->flatMap(fn ($c) => $c->activeTypes->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'category_id' => $c->id,
            ])),
        ]);
    }
}
