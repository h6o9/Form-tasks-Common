@extends('admin.layout.app')
@section('title', 'View Company Form Field')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-primary mb-3">Back</a>

            <div class="card">
                <div class="card-header">
                    <h4>Company: {{ $formInfo['company']->name ?? 'N/A' }}</h4>
                </div>
                <div class="card-body">

                    <div class="border rounded p-3 mb-4">
                        <div class="form-group">
                            <label>Form Name</label>
                            <input type="text" class="form-control" value="{{ $formInfo['form_name'] }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Form No</label>
                            <input type="text" class="form-control" value="{{ $formInfo['form_no'] }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Form Step Type</label>
                            <input type="text" class="form-control" value="{{ $formInfo['form_step_type'] }}" readonly>
                        </div>
                    </div>

                    {{-- ✅ Loop through each field --}}
                    @foreach ($formFields as $index => $field)
                        <div class="border rounded p-3 mb-5 shadow-sm">
                            <h5 class=" mb-3">
                                Field {{ $index + 1 }} — {{ $field->label ?? 'Untitled Field' }}
                            </h5>

                            {{-- Field Type --}}
                            <div class="form-group">
                                <label>Field Type</label>
                                <input type="text" class="form-control" 
                                    value="{{ optional($field->formField)->field_type ?? $field->form_field_id ?? '-' }}" 
                                    readonly>
                            </div>

                            {{-- Parameter --}}
                            <div class="form-group">
                                <label>Parameter</label>
                                <input type="text" class="form-control" value="{{ $field->parameter ?? '-' }}" readonly>
                            </div>

                            {{-- Placeholder --}}
                            <div class="form-group">
                                <label>Placeholder</label>
                                <input type="text" class="form-control" value="{{ $field->placeholder ?? '-' }}" readonly>
                            </div>

                            {{-- Steps --}}
                            <div class="form-group">
                                <label>Steps</label>
                                <input type="text" class="form-control" value="{{ $field->steps ?? '-' }}" readonly>
                            </div>

                            {{-- Options --}}
                            @php
                                $options = [];
                                if (!empty($field->options)) {
                                    $decoded = json_decode($field->options, true);
                                    $options = is_array($decoded) ? $decoded : [];
                                }
                            @endphp

                            @if(count($options) > 0)
                                <div class="form-group">
                                    <label>Options</label>
                                    @foreach($options as $optIndex => $opt)
                                        <input type="text" 
                                            class="form-control mb-2" 
                                            value="{{ $opt }}" 
                                            readonly>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Allowed Extensions --}}
                            @php
                                $extensions = [];
                                if (!empty($field->allowed_extension)) {
                                    $decoded = json_decode($field->allowed_extension, true);
                                    $extensions = is_array($decoded) ? $decoded : [];
                                }
                            @endphp

                            @if(count($extensions) > 0)
                                <div class="form-group">
                                    <label>Allowed Extensions</label>
                                    @foreach($extensions as $extIndex => $ext)
                                        <input type="text" 
                                            class="form-control mb-2" 
                                            value="{{ $ext }}" 
                                            readonly>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>
</div>
@endsection
