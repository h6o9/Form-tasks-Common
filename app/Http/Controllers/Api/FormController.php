<?php

namespace App\Http\Controllers\Api;

use App\Models\FormHasField;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FormController extends Controller
{
    //	
	public function index()
{
    $forms = FormHasField::select(
            'id',
            'company_id',
            'form_name',
            'form_step_type',
            'form_field_id',
            'label',
            'parameter',
            'placeholder',
            'steps',
            'options',
            'created_at'
        )
        ->with([
            'company:id,name', // eager load company name
            'formField:id,field_type'  // eager load field type name
        ])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($form) {
            // Convert JSON string to array for options
            $form->options = $form->options ? json_decode($form->options, true) : [];

            // Replace IDs with names
            $form->company_name = $form->company->name ?? null;
            $form->field_type = $form->formField->field_type ?? null;

            // Remove extra relations and IDs
            unset($form->company_id, $form->form_field_id, $form->company, $form->formField);

            return $form;
        });

    return response()->json([
        'status' => 'success',
        'count' => $forms->count(),
        'data' => $forms
    ], 200);
}



public function receiveData(Request $request)
{ 
	// return $request;
    // ✅ Validation
    $validated = $request->validate([
        'company_id' => 'required|integer',
        'user_id' => 'required|integer',
        'form_name' => 'required|string|max:255',

        'images' => 'nullable',
        'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',

        'file' => 'nullable|mimes:pdf,doc,docx,xlsx,txt,zip,js|max:10240',
    ]);

    $imagePaths = [];
    $filePath = null;

    // ✅ Normalize single image → array
    if ($request->hasFile('images')) {
        $images = $request->file('images');

        // if only one image uploaded (not an array)
        if (!is_array($images)) {
            $images = [$images];
        }

        $destinationPath = public_path('admin/assets/images');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        foreach ($images as $image) {
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            $imagePaths[] = 'public/admin/assets/images/' . $imageName;
        }
    }

    // ✅ Save single file
    if ($request->hasFile('file')) {
        $destinationPath = public_path('admin/assets/files');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
        $request->file('file')->move($destinationPath, $fileName);
        $filePath = 'public/admin/assets/files/' . $fileName;
    }

    // ✅ Prepare files JSON (combine images + file)
    $filesData = [];
    if (!empty($imagePaths)) $filesData['images'] = $imagePaths;
    if (!empty($filePath)) $filesData['file'] = $filePath;

    // ✅ Save in DB
    $formResponse = FormResponse::create([
        'company_id' => $validated['company_id'],
        'user_id' => $validated['user_id'],
        'form_name' => $validated['form_name'],

        'responses' => json_encode([
            'form_data' => $request->except(['images', 'file']),
        ]),
        'files' => !empty($filesData) ? json_encode($filesData) : null,
    ]);

    // ✅ Optional URLs
    $imageUrls = collect($imagePaths)->map(fn($path) => asset(str_replace('public/', '', $path)));
    $fileUrl = $filePath ? asset(str_replace('public/', '', $filePath)) : null;

    return response()->json([
        'status' => 'success',
        'message' => 'Data saved successfully',
        'data' => [
            'company_id' => $formResponse->company_id,
            'user_id' => $formResponse->user_id,
            'form_name' => $formResponse->form_name,
            'responses' => json_decode($formResponse->responses),
            'files' => json_decode($formResponse->files),
            'urls' => [
                'images' => $imageUrls,
                'file' => $fileUrl,
            ]
        ]
    ]);
}



public function getFormResponses()
{
    // ✅ Fetch all form responses from DB (latest first)
    $responses = FormResponse::orderBy('id', 'desc')->get();

    // ✅ Decode JSON fields (responses, files)
    $responses->transform(function ($item) {
        return [
            'id' => $item->id,
            'company_id' => $item->company_id,
            'user_id' => $item->user_id,
            'form_name' => $item->form_name,
            'responses' => json_decode($item->responses, true),
            'files' => json_decode($item->files, true),
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    });

    // ✅ Return success response
    return response()->json([
        'status' => 'success',
        'count' => $responses->count(),
        'data' => $responses
    ]);
}


public function getCompanies()
{
	$companies = \App\Models\Company::select('id', 'name')->get();

	return response()->json([
		'status' => 'success',
		'count' => $companies->count(),
		'data' => $companies
	]);

}


public function getCompaniesForForm($id)
{
	$formName = \App\Models\FormHasField::where('company_id', $id)
    ->select('form_no', 'form_name')
    ->groupBy('form_no', 'form_name')
    ->get();


	return response()->json([
		'status' => 'success',
		'count' => $formName ? 1 : 0,
		'data' => $formName ? [$formName] : [],
	]);

}

public function getCompaniesFormDetails($id)
{
	$formDetails = \App\Models\FormHasField::where('form_no', $id)->get();
	return response()->json([
		'status' => 'success',
		'count' => $formDetails->count(),
		'data' => $formDetails,
	]);
}


}
