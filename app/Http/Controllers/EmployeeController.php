<?php

namespace App\Http\Controllers;

use App\Mail\AppMail;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Employee::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company_id' => 'integer|exists:companies,id',
            'email' => 'email',
            'age' => 'integer',
            'salary' => 'numeric',
        ], [
            'first_name.required' => __('Employee first name is required'),
            'last_name.required' => __('Employee last name is required'),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_id' => $request->company_id,
            'email' => $request->email,
            'age' => $request->age,
            'salary' => $request->salary,
        ]);

        $details = [
            'title' => 'Mail from Company-employees',
            'body' => 'New employee, named ' . $request->first_name . ' ' . $request->last_name . ' added to your company.',
        ];

        $company = Company::where('id', '=', $request->company_id)->first();

        if (isset($company->email)) {
            Mail::to($company->email)->send(new AppMail($details));
        }

        return response()->json([
            'message' => 'Employee \'' . $request->first_name . ' ' . $request->last_name . '\' created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $found = Employee::where('id', '=', $id)->first();

        if (isset($found)) {
            return response()->json($found);
        }
        else {
            return response()->json(['message' => 'Employee not found'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company_id' => 'integer|exists:companies,id',
            'email' => 'email',
            'age' => 'integer',
            'salary' => 'numeric',
        ], [
            'first_name.required' => __('Employee first name is required'),
            'last_name.required' => __('Employee last name is required'),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $found = Employee::where('id', '=', $id)->first();

        if (isset($found)) {
            $found->first_name = $request->first_name;
            $found->last_name = $request->last_name;
            $found->company_id = $request->company_id;
            $found->email = $request->email;
            $found->age = $request->age;
            $found->salary = $request->salary;

            $found->save();
            return response()->json(['message' => 'Employee details updated']);
        }
        else {
            return response()->json(['message' => 'Employee not found'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $found = Employee::where('id', '=', $id)->first();

        if (isset($found)) {
            $found->delete();
            return response()->json(['message' => 'Employee deleted successfully']);
        }
        else {
            return response()->json(['message' => 'Employee not found'], 400);
        }
    }

    /**
     * Filter employees by Company.
     *
     * @param integer $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter($companyId) {
        $foundCompany = Company::where('id', '=', $companyId)->first();

        if (isset($foundCompany)) {
            return response()->json($foundCompany->employees);
        }
        else return response()->json(['message' => 'Specified company not found'], 400);
    }
}
