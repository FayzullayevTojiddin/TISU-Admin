<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Room;
use App\FakultetEnum;

class SearchController extends Controller
{
    /**
     * Fakultetlar ro'yxati (enum dan olinadi)
     * GET /teacher/search/fakultets
     */
    public function fakultets()
    {
        return response()->json([
            'success' => true,
            'data' => FakultetEnum::options(),
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
        $fakultet = $request->query('fakultet');

        $query = Room::query();

        if ($fakultet) {
            $query->where('fakultet', $fakultet);
        }

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $list = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'fakultet', 'status']);

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

    /**
     * Fanlar ro'yxati (statik array, searchable)
     * GET /teacher/search/subjects?q=...
     */
    public function subjects(Request $request)
    {
        $q = $request->query('q');

        $subjects = [
            'Ingliz Tili',
            'Hisob',
            'Dasturlash',
            'Akademik yozuv',
            'Fizika',
            // keyin siz qo'shishingiz mumkin
        ];

        if ($q) {
            $subjects = array_values(array_filter($subjects, fn($s) => stripos($s, $q) !== false));
        }

        // map to id/name style (id - indeks)
        $data = array_values(array_map(fn($s, $i) => ['id' => $i + 1, 'name' => $s], $subjects, array_keys($subjects)));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Darslar (paralar) ro'yxati - 12 para (array)
     * GET /teacher/search/paras
     */
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
            'Ma\'ruza',
            'Amaliy',
            'Labaratoriya',
            'Seminar',
            // keyin qo'shishingiz mumkin
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