<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\Schedule;

class UniversityController extends Controller
{
   public function login(Request $request)
{
    try {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 1️⃣ Login API
        $loginResponse = Http::post('https://quiztoxml.ucas.edu.ps/api/login', [
            'username' => $request->username,
            'password' => $request->password,
        ]);

        $loginData = $loginResponse->json();

        if (isset($loginData['message']) && $loginData['message'] == "كلمة المرور او اسم المستخدم خطا") {
            return response()->json(['message' => $loginData['message']], 401);
        }

        $token = $loginData['Token'];
        $user_id = (string) $loginData['data']['user_id'];
        $name = $loginData['data']['user_en_name'] ?? 'غير محدد';

        // 2️⃣ حفظ الطالب
        $student = Student::updateOrCreate(
            ['user_id' => $user_id],
            ['name' => $name, 'token' => $token]
        );

        // 3️⃣ Get Schedule API
        $scheduleResponse = Http::post('https://quiztoxml.ucas.edu.ps/api/get-table', [
            'user_id' => $user_id,
            'token'   => $token,
        ]);

        $scheduleData = $scheduleResponse->json();

        if (!isset($scheduleData['data'])) {
            return response()->json([
                'message' => 'فشل جلب الجدول',
                'response' => $scheduleData
            ], 500);
        }

        // حذف القديم
        Schedule::where('user_id', $user_id)->delete();

        $daysMap = [
            'S' => 'Saturday',
            'N' => 'Sunday',
            'M' => 'Monday',
            'T' => 'Tuesday',
            'W' => 'Wednesday',
            'R' => 'Thursday',
        ];

        $savedSchedule = [];

        // 4️⃣ تحويل البيانات صح
        foreach ($scheduleData['data'] as $course) {

            foreach ($daysMap as $key => $dayName) {

                if (!empty($course[$key])) {

                    [$from, $to] = explode('-', $course[$key]);

                    $savedSchedule[] = Schedule::create([
                        'user_id'     => $user_id,
                        'day'         => $dayName,
                        'time_from'   => $from,
                        'time_to'     => $to,
                        'course_name' => $course['subject_name'],
                        'course_code' => $course['subject_no'],
                        'room'        => $course['room_no'],
                        'instructor'  => $course['teacher_name'],
                        'section'     => $course['branch_no'],
                    ]);
                }
            }
        }

        return response()->json([
            'message'        => 'تم تسجيل الدخول وحفظ الجدول الدراسي بنجاح',
            'student'        => $student,
            'schedule_count' => count($savedSchedule),
            'schedule'       => $savedSchedule,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function getSchedule($user_id)
    {
        try {
            $student = Student::where('user_id', $user_id)->first();

            if (!$student) {
                return response()->json(['message' => 'الطالب غير موجود'], 404);
            }

            $schedule = Schedule::where('user_id', $user_id)->get();

            if ($schedule->isEmpty()) {
                return response()->json(['message' => 'لا يوجد جدول دراسي لهذا الطالب'], 404);
            }

            return response()->json([
                'student'  => $student,
                'schedule' => $schedule,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }

    public function getAllStudents()
    {
        try {
            $students = Student::with('schedules')->get();

            if ($students->isEmpty()) {
                return response()->json(['message' => 'لا يوجد طلاب مسجلين'], 404);
            }

            return response()->json([
                'count'    => $students->count(),
                'students' => $students,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500);
        }
    }
}