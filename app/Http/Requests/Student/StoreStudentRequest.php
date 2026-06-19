<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8',
            'department_id'   => 'required|exists:departments,id',
            'student_id'      => 'required|string|max:50|unique:students,student_id',
            'roll_number'     => 'required|string|max:50|unique:students,roll_number',
            'semester'        => 'required|integer|min:1|max:8',
            'batch'           => 'required|string|max:20',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
        ];
    }
}
