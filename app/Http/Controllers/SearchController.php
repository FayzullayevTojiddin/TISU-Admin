<?php

namespace App\Http\Controllers;

use App\BuildEnum;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Room;
use App\LessonType;

class SearchController extends Controller
{
    public function builds()
    {
        return response()->json([
            'success' => true,
            'data' => BuildEnum::options(),
        ]);
    }

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
        $build = $request->query('build');
        $q     = $request->query('q');

        $query = Room::query();

        if ($build) {
            $query->where('build', $build);
        }

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $data = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'fakultet', 'status']);

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
    
    public function lessonTypes(Request $request)
    {
        $q = $request->query('q');

        $types = LessonType::list();

        if ($q) {
            $q = mb_strtolower($q);
            $types = array_filter($types, function ($name) use ($q) {
                return mb_stripos($name, $q) !== false;
            });
        }

        $data = array_values(array_map(function ($id, $name) {
            return [
                'id' => (int) $id,
                'name' => $name,
            ];
        }, array_keys($types), $types));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}