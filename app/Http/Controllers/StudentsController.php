<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    // GET /students -> listing
    public function index()
    {
        $students = Student::orderBy('id', 'DESC')->paginate(10);

        return response()->json($students, 200);
    }

    // POST /students -> create new record
    public function store(Request $request)
    {
        $this->validate($request, [
            'nim'        => 'required|string|max:20|unique:students,nim',
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:100|unique:students,email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'major'      => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'status'     => 'in:active,inactive,graduated',
        ]);

        $student = Student::create($request->all());

        return response()->json($student, 201);
    }

    // GET /students/{id} -> get single record
    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'student not found'], 404);
        }

        return response()->json($student, 200);
    }

    // PUT /students/{id} -> update single record
    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'student not found'], 404);
        }

        $this->validate($request, [
            'nim'        => 'string|max:20|unique:students,nim,' . $id,
            'name'       => 'string|max:100',
            'email'      => 'email|max:100|unique:students,email,' . $id,
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'major'      => 'string|max:100',
            'birth_date' => 'nullable|date',
            'status'     => 'in:active,inactive,graduated',
        ]);

        $student->fill($request->all());
        $student->save();

        return response()->json($student, 200);
    }

    // DELETE /students/{id} -> delete record
    public function destroy($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'student not found'], 404);
        }

        $student->delete();

        return response()->json([
            'message'    => 'deleted successfully',
            'student_id' => $id,
        ], 200);
    }
}