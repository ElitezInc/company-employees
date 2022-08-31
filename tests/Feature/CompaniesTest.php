<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CompaniesTest extends TestCase
{
    public function testCompaniesProtection()
    {
        $response = $this->withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ])
            ->get('api/companies');

        $response->assertStatus(401)->assertJson(["message" => "Unauthenticated."]);
    }

    public function testCompanyCreateRequiredFields()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->withHeaders([
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "Authorization" => 'Bearer ' . $token,
        ])
            ->post('api/companies');

        $response->assertStatus(400)
            ->assertJson([
                "name" => ["Company name is required"],
            ]);
    }

    public function testCompanyCreateFieldValidation()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->json(
            'POST',
            'api/companies',
            [
                "name" => 1,
                "email" => "wrong value",
                "website" => 2,
            ],
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "name" => ["The name must be a string."],
                "email" => ["The email must be a valid email address."],
                "website" => ["The website must be a string."],
            ]);
    }

    public function testCompanyCreateDuplicateName()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        Company::create([
            "name" => "test company",
        ]);

        $response = $this->json(
            'POST',
            'api/companies',
            [
                "name" => "test company",
            ],
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                "name" => ["The name has already been taken."],
            ]);
    }

    public function testCompanyCreatedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->json(
            'POST',
            'api/companies',
            ["name" => "Company"],
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Company named 'Company' created successfully",
            ]);
    }

    public function testCompanyListedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->get(
            'api/companies/2',
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "id" => 2,
                "name" => "Company",
            ]);
    }

    public function testCompanyDetailsUpdatedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $companyData = [
            "name" => "Modified name",
            "email" => "company@email.com",
            "website" => "www.website.com",
        ];

        $response = $this->json(
            'PUT',
            'api/companies/2',
            $companyData,
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Company details updated",
            ]);
    }

    public function testCompanyDeletedSuccessfully()
    {
        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->delete(
            'api/companies/2',
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Company deleted successfully",
            ]);
    }

    public function testCompanyInformationRetrievedSuccessfully()
    {
        $company = Company::create(["name" => "Company"]);

        Employee::create([
            "first_name" => "Name 1",
            "last_name" => "Surname 1",
            "company_id" => $company->id,
            "age" => 20,
            "salary" => 650,
        ]);

        Employee::create([
            "first_name" => "Name 2",
            "last_name" => "Surname 2",
            "company_id" => $company->id,
            "age" => 30,
            "salary" => 1050,
        ]);

        Employee::create([
            "first_name" => "Name 3",
            "last_name" => "Surname 3",
            "company_id" => $company->id,
            "age" => 25,
            "salary" => 813,
        ]);

        $token = Auth::attempt(["email" => "admin@admin.com", "password" => "password"]);

        $response = $this->get(
            'api/companies/information/' . $company->id,
            [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => 'Bearer ' . $token,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "average_salary",
                "average_age",
                "max_salary",
                "max_salary",
                "max_age",
                "min_age",
            ]);
    }
}
