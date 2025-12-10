<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormName;
use App\Models\FormField;
use App\Models\FormHasField;
use App\Models\Company;

class CustomFormsController extends Controller
{
    //
		public function createView()
	{
		// ✅ Find the form by ID
		$form = FormName::all();

		// ✅ Get all fields related to this form from pivot table
		$fields = FormField::all();

		$companies = Company::all();

		// ✅ Pass form and fields to blade
		return view('admin.forms.create', compact('form', 'fields', 'companies'));
	}
public function store(Request $request)
{
    // Optional validation
    $request->validate([
        'company_id' => 'required|exists:companies,id',
        'form_name'  => 'required|string|max:255',
        'form_type'  => 'required|in:single,multi',
        'fields'     => 'required|array|min:1',
    ]);

    // Random unique form number
   do {
    $formNo = 'FORM-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
	} while (\App\Models\FormHasField::where('form_no', $formNo)->exists());


    // Loop through each field
    foreach ($request->fields as $index => $fieldData) {
        // Skip incomplete fields
        if (empty($fieldData['field_type']) || empty($fieldData['label'])) {
            continue;
        }

        // Parameter fix (since it comes separately)
        $parameter = $request->parameter[$index] ?? null;

        // Get the FormField ID by type
        $formField = \App\Models\FormField::where('field_type', $fieldData['field_type'])->first();

        if ($formField) {
            \App\Models\FormHasField::create([
                'company_id'        => $request->company_id,
                'form_name'         => $request->form_name,
                'form_step_type'    => $request->form_type,
                'form_field_id'     => $formField->id,
                'label'             => $fieldData['label'],
                'parameter'         => $parameter,
                'placeholder'       => $fieldData['placeholder'] ?? null,
                'steps'             => $fieldData['steps'] ?? null,
                'options'           => !empty($fieldData['options']) ? json_encode($fieldData['options'], JSON_UNESCAPED_UNICODE) : null,
                'allowed_extension' => !empty($fieldData['extensions']) ? json_encode(explode(',', $fieldData['extensions']), JSON_UNESCAPED_UNICODE) : null,
                'form_no'           => $formNo,
            ]);
        }
    }

    return redirect()
        ->route('admin.companies.index')
        ->with('success', 'Form fields saved successfully!');
}



public function index()
{
    // Pehle har unique form_no ka latest record ka id nikal lo
    $latestFormIds = \App\Models\FormHasField::selectRaw('MAX(id) as id')
        ->groupBy('form_no')
        ->pluck('id');

    // Ab un IDs ke poore record le lo (company relation ke sath)
    $forms = \App\Models\FormHasField::with('company')
        ->whereIn('id', $latestFormIds)
        ->orderByDesc('id')
        ->get();

    return view('admin.companies.index', compact('forms'));
}



public function destroy($id)
{
    // Step 1: Find record by id
    $form = FormHasField::find($id);

    if (!$form) {
        return response()->json([
            'success' => false,
            'message' => 'Record not found',
        ]);
    }

    // Step 2: Get the form_no from this record
    $formNo = $form->form_no;

    // Step 3: Delete all records with same form_no
    $deletedCount = FormHasField::where('form_no', $formNo)->delete();

    return response()->json([
        'success' => true,
        'message' => "All records with Form No: {$formNo} deleted successfully ({$deletedCount} entries removed)."
    ]);
}


public function show($form_no)
{
    $formFields = \App\Models\FormHasField::with('company')
        ->where('form_no', $form_no)
        ->get();

    if ($formFields->isEmpty()) {
        abort(404, 'Form not found');
    }

    $formInfo = [
        'form_no' => $form_no,
        'form_name' => $formFields->first()->form_name ?? '',
        'company' => $formFields->first()->company ?? null,
        'form_step_type' => $formFields->first()->form_step_type ?? '',
    ];

    return view('admin.companies.formfeild_details', compact('formInfo', 'formFields'));
}



}
