<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    /**
     * Daftar semua departemen (publik, untuk form registrasi).
     */
    public function index(): JsonResponse
    {
        $departments = Department::orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $departments]);
    }
}
