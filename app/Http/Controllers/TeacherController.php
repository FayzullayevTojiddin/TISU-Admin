<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class TeacherController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
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
            
            $teacher = Teacher::where('login', $request->login)->first();

            if (!$teacher || !Hash::check($request->password, $teacher->password)) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Login yoki parol noto\'g\'ri'
                    ]
                ], 401);
            }

            if ($teacher->status != 1) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Sizning hisobingiz faol emas'
                    ]
                ], 403);
            }

            $token = JWTAuth::fromUser($teacher);

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Muvaffaqiyatli kirdingiz',
                    'token' => $token,
                    'teacher' => [
                        'id' => $teacher->id,
                        'full_name' => $teacher->full_name,
                        'login' => $teacher->login,
                    ]
                ]
            ], 200);

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

    /**
     * Logout funksiyasi - JWT tokenni bekor qiladi
     */
    public function logout(Request $request)
    {
        try {
            // Tokenni bekor qilish
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Muvaffaqiyatli chiqish amalga oshirildi'
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'data' => [
                    'message' => 'Token bekor qilinmadi',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Parolni o'zgartirish funksiyasi
     */
    public function changePassword(Request $request)
    {
        try {
            // Validatsiya
            $validator = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
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

            // Autentifikatsiya qilingan foydalanuvchini olish
            $teacher = JWTAuth::parseToken()->authenticate();

            // Eski parolni tekshirish
            if (!Hash::check($request->old_password, $teacher->password)) {
                return response()->json([
                    'success' => false,
                    'data' => [
                        'message' => 'Eski parol noto\'g\'ri'
                    ]
                ], 401);
            }

            // Yangi parolni saqlash
            $teacher->password = $request->new_password;
            $teacher->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Parol muvaffaqiyatli o\'zgartirildi'
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

    /**
     * O'qituvchi ma'lumotlarini olish funksiyasi
     */
    public function getProfile(Request $request)
    {
        try {
            // Autentifikatsiya qilingan foydalanuvchini olish
            $teacher = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher' => [
                        'id' => $teacher->id,
                        'full_name' => $teacher->full_name,
                        'login' => $teacher->login,
                        'status' => $teacher->status,
                        'created_at' => $teacher->created_at,
                        'updated_at' => $teacher->updated_at,
                    ]
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