<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
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
        return Company::all();
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
            'name' => 'required|string|unique:companies',
            'email' => 'string|email',
            'website' => 'string',
        ], [
            'name.required' => __('Company name is required'),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        Company::create([
            'name' => $request->name,
            'email' => $request->email,
            'website' => $request->website,
        ]);

        return response()->json([
            'message' => 'Company named \'' . $request->name . '\' created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $found = Company::where('id', '=', $id)->first();

        if (isset($found)) {
            return response()->json($found);
        }
        else {
            return response()->json(['message' => 'Company not found'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:companies',
            'email' => 'string|email',
            'website' => 'string',
        ], [
            'name.required' => __('Company name is required'),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $found = Company::where('id', '=', $id)->first();

        if (isset($found)) {
            $found->name = $request->name;
            $found->email = $request->email;
            $found->website = $request->website;

            $found->save();
            return response()->json(['message' => 'Company details updated']);
        }
        else {
            return response()->json(['message' => 'Company not found'], 400);
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
        $found = Company::where('id', '=', $id)->first();

        if (isset($found)) {
            $found->delete();
            return response()->json(['message' => 'Company deleted successfully']);
        }
        else {
            return response()->json(['message' => 'Company not found'], 400);
        }
    }

    /**
     * Get average salary, age, min/max age, salary of employees.
     *
     * @param  integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function information($id) {
        $found = Company::where('id', '=', $id)->first();

        if (isset($found)) {
            $employees = Employee::where('company_id', '=', $id)->get();

            return response()->json([
                'average_salary' => round($employees->avg('salary'), 2),
                'average_age' => round($employees->avg('age'), 2),
                'max_salary' => $employees->max('salary'),
                'min_salary' => $employees->min('salary'),
                'max_age' => $employees->max('age'),
                'min_age' => $employees->min('age'),
            ]);
        }
        else {
            return response()->json(['message' => 'Company not found'], 400);
        }
    }
}
