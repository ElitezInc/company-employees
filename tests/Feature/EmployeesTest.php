<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EmployeesTest extends TestCase
{
    public function testEmployeesProtection()
    {
        $response = $this->withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ])
            ->get('api/employees');

        $response->assertStatus(401)->assertJson(["message" => "Unauthenticated."]);
    }

    public function testEmployeeCreateRequiredFields()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);
        $response = $this->withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "Authorization" => 'Bearer ' . $token,
        ])
            ->post('api/employees');

        $response->assertStatus(400)
            ->assertJson([
                "first_name" => ["Employee first name is required"],
                "last_name" => ["Employee last name is required"],
            ]);
    }

    public function testEmployeeCreateFieldValidation()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->json(
            'POST',
            'api/employees',
            [
                "first_name" => "Employee name",
                "last_name" => "Employee surname",
                "company_id" => "wrong value",
                "age" => "wrong value",
                "salary" => "wrong value",
            ],
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "company_id" => ["The company id must be an integer."],
                "age" => ["The age must be an integer."],
                "salary" => ["The salary must be a number."],
            ]);
    }

    public function testEmployeeCreateWrongCompany()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->json(
            'POST',
            'api/employees',
            [
                "first_name" => "Employee name",
                "last_name" => "Employee surname",
                "company_id" => 999,
                "age" => 35,
                "salary" => 1050,
            ],
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "company_id" => ["The selected company id is invalid."],
            ]);
    }

    public function testEmployeeCreatedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $employeeData = [
            "first_name" => "Employee name",
            "last_name" => "Employee surname",
        ];

        $response = $this->json(
            'POST',
            'api/employees',
            $employeeData,
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Employee 'Employee name Employee surname' created successfully",
            ]);
    }

    public function testEmployeeListedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->get(
            'api/employees/1',
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "id" => 1,
                "first_name" => "Name 1",
                "last_name" => "Surname 1",
                "age" => 20,
                "salary" => 650,
            ]);
    }

    public function testEmployeeDetailsUpdatedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $employeeData = [
            "first_name" => "Modified name",
            "last_name" => "Modified surname",
        ];

        $response = $this->json(
            'PUT',
            'api/employees/1',
            $employeeData,
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Employee details updated",
            ]);
    }

    public function testEmployeeDeletedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->delete(
            'api/employees/1',
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Employee deleted successfully",
            ]);
    }

    public function testEmployeesFilterByCompany()
    {
        Employee::create([
            "first_name" => "Name 1",
            "last_name" => "Surname 1",
            "company_id" => 1,
        ]);

        Employee::create([
            "first_name" => "Name 2",
            "last_name" => "Surname 2",
            "company_id" => 1,
        ]);

        Employee::create([
            "first_name" => "Name 3",
            "last_name" => "Surname 3",
            "company_id" => 1,
        ]);

        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->get(
            'api/employees/filter/1',
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "*" => [
                    "id",
                    "first_name",
                    "last_name",
                    "company_id",
                    "email",
                    "age",
                    "salary",
                    "created_at",
                    "updated_at",
                ],
            ]);
    }
}
