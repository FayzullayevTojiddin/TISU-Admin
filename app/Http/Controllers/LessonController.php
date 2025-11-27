<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Group;
use App\Models\Room;
use App\FakultetEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LessonController extends Controller
{
    public function getLessons(Request $request)
    {
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Validatsiya xatosi',
                        'errors' => $validator->errors()
                    ]
                ], 422);
            }

            $lessons = Lesson::where('teacher_id', $teacher->id)
                ->whereDate('date', $request->date)
                ->with(['group', 'room', 'attendances'])
                ->orderBy('date', 'desc')
                ->get();

            if ($lessons->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'message' => 'Ushbu sanada darslar topilmadi',
                        'lessons' => []
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Darslar muvaffaqiyatli olindi',
                    'lessons' => $lessons,
                    'total' => $lessons->count()
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Token topilmadi yoki yaroqsiz',
                    'error' => $e->getMessage()
                ]
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Xatolik yuz berdi',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'group_id' => 'required|exists:groups,id',
                'room_id' => 'required|exists:rooms,id',
                'date' => 'required|date_format:Y-m-d',
                'image' => 'nullable|image|max:2048',
                'fakultet' => 'required|string|in:' . implode(',', array_keys(FakultetEnum::options())),
                'subject_name' => 'nullable|string|max:255',
                'time_at' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Validatsiya xatosi',
                        'errors' => $validator->errors()
                    ]
                ], 422);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('lessons', 'public');
            }

            $details = [
                'fakultet' => $request->fakultet,
                'subject_name' => $request->subject_name,
                'time_at' => $request->time_at,
            ];

            $lesson = new Lesson();
            $lesson->teacher_id = $teacher->id;
            $lesson->group_id = $request->group_id;
            $lesson->room_id = $request->room_id;
            $lesson->date = $request->date;
            $lesson->image = $imagePath;
            $lesson->details = $details;
            $lesson->save();

            $lesson->load(['group', 'room', 'teacher']);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Dars muvaffaqiyatli qo\'shildi',
                    'lesson' => $lesson
                ]
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Token topilmadi yoki yaroqsiz',
                    'error' => $e->getMessage()
                ]
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Xatolik yuz berdi',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            $lesson = Lesson::with(['group', 'room', 'teacher', 'attendances'])
                ->find($id);

            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Dars topilmadi'
                    ]
                ], 404);
            }

            if ($lesson->teacher_id != $teacher->id) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Sizda bu darsga kirish huquqi yo\'q'
                    ]
                ], 403);
            }

            $total = $lesson->attendances()->count();
            $came = $lesson->attendances()->where('came', true)->count();
            $notCame = $lesson->attendances()->where('came', false)->count();
            $percentage = $total > 0 ? round($came / $total * 100) : 0;

            $lessonData = $lesson->toArray();
            $lessonData['statistics'] = [
                'total' => $total,
                'came' => $came,
                'not_came' => $notCame,
                'percentage' => $percentage
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Dars ma\'lumotlari',
                    'lesson' => $lessonData
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Token topilmadi yoki yaroqsiz',
                    'error' => $e->getMessage()
                ]
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Xatolik yuz berdi',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }
}