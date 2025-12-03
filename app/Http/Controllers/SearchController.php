<?php

namespace App\Http\Controllers;

use App\BuildEnum;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Room;

class SearchController extends Controller
{
    /**
     * Binolar ro'yxati (enum dan olinadi)
     * GET /teacher/search/builds
     */
    public function builds()
    {
        return response()->json([
            'success' => true,
            'data' => BuildEnum::options(),
        ]);
    }

    /**
     * Guruhlar ro'yxati (searchable)
     * GET /teacher/search/groups?q=...
     */
    public function groups(Request $request)
    {
        $q = $request->query('q');

        $query = Group::query();

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $list = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);

        $data = $list->map(fn($g) => ['id' => $g->id, 'name' => $g->name])->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function rooms(Request $request)
    {
        $q = $request->query('q');
        $build = $request->query('build');

        $query = Room::query();

        if ($build) {
            $query->where('build', $build);
        }

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $list = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);

        $data = $list->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'fakultet' => $r->fakultet,
            'status' => $r->status,
        ])->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function subjects(Request $request)
    {
        $q = $request->query('q');

        $subjects = [
            'Ingliz Tili',
            'Hisob',
            'Dasturlash',
            'Akademik yozuv',
            'Fizika',
        ];

        if ($q) {
            $subjects = array_values(array_filter($subjects, fn($s) => stripos($s, $q) !== false));
        }

        $data = array_values(array_map(fn($s, $i) => ['id' => $i + 1, 'name' => $s], $subjects, array_keys($subjects)));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function paras()
    {
        $paras = [
            '08:30-09:20 (1-para)',
            '09:30-10:20 (2-para)',
            '10:30-11:20 (3-para)',
            '11:30-12:20 (4-para)',
            '12:30-13:20 (5-para)',
            '13:30-14:20 (6-para)',
            '14:30-15:20 (7-para)',
            '15:30-16:20 (8-para)',
            '16:30-17:20 (9-para)',
            '17:30-18:20 (10-para)',
            '18:30-19:20 (11-para)',
            '19:30-20:20 (12-para)',
        ];

        $data = array_values(array_map(fn($s, $i) => ['id' => $i + 1, 'time' => $s], $paras, array_keys($paras)));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Dars turi ro'yxati (statik) - searchable mumkin
     * GET /teacher/search/lesson-types?q=...
     */
    public function lessonTypes(Request $request)
    {
        $q = $request->query('q');

        $types = [
            'Ma`ruza',
            'Amaliy',
            'Labaratoriya',
            'Seminar',
        ];

        if ($q) {
            $types = array_values(array_filter($types, fn($t) => stripos($t, $q) !== false));
        }

        $data = array_values(array_map(fn($s, $i) => ['id' => $i + 1, 'name' => $s], $types, array_keys($types)));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}