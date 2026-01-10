<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Student;

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
                'group_id'     => 'required|exists:groups,id',
                'room_id'      => 'required|exists:rooms,id',
                'date'         => 'required|date',
                'build'     => 'required|string',
                'subject_name' => 'required|string',
                'lesson_type'  => 'required|string',
                'time_at'      => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $firstError = collect($errors)->flatten()->first() ?? 'Validatsiya xatosi';

                return response()->json([
                    'success' => false,
                    'data' => [
                        'message'     => $firstError,
                        'errors'      => $errors,
                        'first_error' => $firstError,
                    ]
                ], 422);
            }

            // Transaction: lesson yaratish + attendance larni ham bitta atomik operatsiyada bajaramiz
            $result = DB::transaction(function () use ($request, $teacher) {

                $room = Room::find($request->room_id);
                $fakultet = $room->fakultet;

                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('lessons', 'public');
                }

                $details = [
                    'fakultet'     => $fakultet,
                    'subject_name' => $request->subject_name,
                    'lesson_type'  => $request->lesson_type,
                    'time_at'      => $request->time_at,
                ];

                $lesson = new Lesson();
                $lesson->teacher_id = $teacher->id;
                $lesson->group_id = $request->group_id;
                $lesson->room_id = $request->room_id;
                $lesson->date = $request->date;
                $lesson->image = $imagePath;
                $lesson->details = $details;
                $lesson->save();

                // Guruhdagi talabalarni olish
                $students = Student::where('group_id', $request->group_id)->get(['id']);

                // Agar talabalar bo'lsa - bulk insert attendance yozuvlarini yaratish
                if ($students->isNotEmpty()) {
                    $now = now();
                    $insert = [];
                    foreach ($students as $s) {
                        $insert[] = [
                            'student_id' => $s->id,
                            'lesson_id'  => $lesson->id,
                            'came'       => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    Attendance::insert($insert);
                }

                $lesson->load(['group', 'room', 'teacher']);
                $lesson->load(['attendances.student']);

                return $lesson;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Dars muvaffaqiyatli qo\'shildi',
                    'lesson' => $result
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
            \Log::error('Lesson store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

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

            $lesson = Lesson::with(['group', 'room', 'teacher', 'attendances', 'attendances.student',])
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

    public function update(Request $request, $id)
    {
        \Log::info('==== NEW REQUEST ====');
        \Log::info('METHOD: ' . $request->method());
        \Log::info('HEADERS', $request->headers->all());
        \Log::info('BODY', $request->all());
        \Log::info('FILES', $_FILES);
        \Log::info('HAS IMAGE', ['image' => $request->hasFile('image')]);
        try {
            $teacher = JWTAuth::parseToken()->authenticate();

            // Darsni topish
            $lesson = Lesson::find($id);

            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Dars topilmadi'
                    ]
                ], 404);
            }

            // Faqat o'z darsini update qilishi mumkin
            if ($lesson->teacher_id != $teacher->id) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Sizda bu darsni tahrirlash huquqi yo\'q'
                    ]
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'attendances' => 'required|array',
                'attendances.*.attendance_id' => 'required|exists:attendances,id',
                'attendances.*.came' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $firstError = collect($errors)->flatten()->first() ?? 'Validatsiya xatosi';

                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => $firstError,
                        'errors' => $errors,
                    ]
                ], 422);
            }

            // Transaction bilan saqlash
            $result = DB::transaction(function () use ($request, $lesson) {
                
                // 1. Rasmni yangilash (agar yangi rasm bo'lsa)
                if ($request->hasFile('image')) {
                    // Eski rasmni o'chirish
                    if ($lesson->image && \Storage::disk('public')->exists($lesson->image)) {
                        \Storage::disk('public')->delete($lesson->image);
                    }

                    // Yangi rasmni saqlash
                    $imagePath = $request->file('image')->store('lessons', 'public');
                    $lesson->image = $imagePath;
                    $lesson->save();
                }

                // 2. Davomatlarni yangilash
                $attendances = $request->attendances;
                
                foreach ($attendances as $attendanceData) {
                    $attendance = Attendance::where('id', $attendanceData['attendance_id'])
                        ->where('lesson_id', $lesson->id)
                        ->first();

                    if ($attendance) {
                        $attendance->came = $attendanceData['came'];
                        $attendance->save();
                    }
                }

                // 3. Yangilangan darsni qaytarish
                $lesson->load(['group', 'room', 'teacher', 'attendances.student']);

                return $lesson;
            });

            // Statistikani hisoblash
            $total = $result->attendances()->count();
            $came = $result->attendances()->where('came', true)->count();
            $notCame = $result->attendances()->where('came', false)->count();
            $percentage = $total > 0 ? round($came / $total * 100) : 0;

            $lessonData = $result->toArray();
            $lessonData['statistics'] = [
                'total' => $total,
                'came' => $came,
                'not_came' => $notCame,
                'percentage' => $percentage
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Davomat muvaffaqiyatli saqlandi',
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
            \Log::error('Lesson update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

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