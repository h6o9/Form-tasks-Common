@extends('admin.layout.app')
@section('title', 'Create Dynamic Form')

@section('content')
<div class="main-content">
    <h3 class="mb-4">Create Dynamic Form</h3>

    <form id="dynamicForm" action="{{ route('forms.store') }}" method="POST">
        @csrf

        {{-- Form Name --}}
        <div class="form-group mb-3">
            <label>Form Name <span class="text-danger">*</span></label>
            <input type="text" name="form_name" class="form-control" placeholder="Enter form name" required>
        </div>

        {{-- Company --}}
        <div class="form-group mb-3">
            <label>Company <span class="text-danger">*</span></label>
            <select name="company_id" class="form-control" required>
                <option value="">-- Select Company --</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Form Type --}}
        <div class="form-group mb-3">
            <label>Form Type <span class="text-danger">*</span></label>
            <select name="form_type" id="form_type" class="form-control" required>
                <option value="">-- Select Form Type --</option>
                <option value="single">Single Form</option>
                <option value="multi">Multi Form</option>
            </select>
            <p id="form_type_message" class="text-danger mt-2"></p>
        </div>

        <hr>

        {{-- Dynamic Fields --}}
        <div id="fields_container"></div>

        <button type="button" id="add_field" class="btn btn-success mb-3">+ Add Field</button><br>
        <button type="submit" class="btn btn-primary">Save Form</button>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    let fieldIndex = 0;

    // Handle Form Type Change
    $('#form_type').on('change', function () {
        const type = $(this).val();
        const msgBox = $('#form_type_message');

        if (type === 'single') {
            msgBox.text('You selected Single Form — "Steps" field will not appear.');
            $('.steps-group').remove(); // hide all step fields
        } else if (type === 'multi') {
            msgBox.text('You selected Multi Form — You must define at least one step.');
            // Add steps to existing fields
            $('.field-block').each(function () {
                if ($(this).find('.steps-group').length === 0) {
                    const index = $(this).data('index');
                    $(this).find('.dynamic-inputs').append(`
                        <div class="form-group mt-2 steps-group">
                            <label>Steps <span class="text-danger">*</span></label>
                            <input type="text" name="fields[${index}][steps]" class="form-control steps-input" placeholder="Enter steps">
                            <small class="text-danger d-none steps-error">⚠️ Please enter steps for at least one field.</small>
                            <small class="text-muted">Define steps to decide after which input the next step begins.</small>
                        </div>
                    `);
                }
            });
        } else {
            msgBox.text('');
            $('.steps-group').remove();
        }
    });

    // Add new field
    $('#add_field').click(function () {
        const formType = $('#form_type').val();
        if (!formType) {
            $('#form_type_message').text('Please select a Form Type before adding a field.');
            return;
        }

        let fieldHtml = `
        <div class="card mb-3 p-3 field-block" data-index="${fieldIndex}">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Field ${fieldIndex + 1}</h5>
                <button type="button" class="btn btn-danger btn-sm remove-field">X</button>
            </div>

            <div class="form-group mt-2">
                <label>Field Type <span class="text-danger">*</span></label>
                <select name="fields[${fieldIndex}][field_type]" class="form-control field-type" required>
                    <option value="">-- Select Field Type --</option>
                    @foreach($fields as $field)
                        <option value="{{ $field->field_type }}">{{ ucfirst($field->field_type) }}</option>
                    @endforeach
                    <option value="checkbox">Checkbox</option>
                    <option value="dropdown">Dropdown</option>
                </select>
            </div>
            <div class="dynamic-inputs mt-2"></div>
        </div>`;
        $('#fields_container').append(fieldHtml);
        fieldIndex++;
    });

    // Remove Field
    $(document).on('click', '.remove-field', function () {
        $(this).closest('.field-block').remove();
    });

    // Handle Field Type Change
    $(document).on('change', '.field-type', function () {
        const type = $(this).val().trim().toLowerCase();
        const container = $(this).closest('.field-block').find('.dynamic-inputs');
        const index = $(this).closest('.field-block').data('index');
        container.empty();

        let baseInputs = `
            <div class="form-group mt-2">
                <label>Label <span class="text-danger">*</span></label>
                <input type="text" name="fields[${index}][label]" class="form-control" required>
            </div>
            <div class="form-group mt-2">
                <label>Placeholder <span class="text-danger">*</span></label>
                <input type="text" name="fields[${index}][placeholder]" class="form-control" required>
            </div>
        `;

        if (type === 'file' || type === 'image') {
            baseInputs += `
                <div class="form-group">
                    <label>Parameter</label>
                    <input type="text" name="parameter[]" class="form-control parameter-input" placeholder="Enter parameter" required>
                    <small class="text-warning d-block mt-1 parameter-warning">
                        ⚠️ Invalid parameter. Use "file" for documents, "images" for single image, or "images[]" for multiple images.
                    </small>
                    <small class="text-danger d-none parameter-error">⚠️ Invalid parameter value.</small>
                </div>
            `;
        } else {
            baseInputs += `
                <div class="form-group">
                    <label>Parameter</label>
                    <input type="text" name="parameter[]" class="form-control parameter-input" placeholder="Enter parameter" required>
                </div>
            `;
        }

        if (type === 'file') {
            baseInputs += `
                <div class="form-group mt-2">
                    <label>Allowed File Extensions <span class="text-danger">*</span></label>
                    <input type="text" name="fields[${index}][extensions]" class="form-control" placeholder="e.g. jpg, png, pdf" required>
                    <small class="text-muted">Separate multiple extensions with commas.</small>
                </div>
            `;
        }

        if (type === 'dropdown' || type === 'checkbox') {
            baseInputs += `
                <div class="form-group mt-3 option-group">
                    <label>${type.charAt(0).toUpperCase() + type.slice(1)} Options</label>
                    <div class="dropdown-options"></div>
                    <button type="button" class="btn btn-sm btn-success add-option mt-2">+ Add Option</button>
                    <small class="text-danger d-none option-error">⚠️ Please add at least one option.</small>
                </div>
            `;
        }

        if ($('#form_type').val() === 'multi') {
            baseInputs += `
                <div class="form-group mt-2 steps-group">
                    <label>Steps <span class="text-danger">*</span></label>
                    <input type="text" name="fields[${index}][steps]" class="form-control steps-input" placeholder="Enter steps">
                    <small class="text-danger d-none steps-error">⚠️ Please enter steps for at least one field.</small>
                    <small class="text-muted">Define steps to decide after which input the next step begins.</small>
                </div>
            `;
        }

        container.append(baseInputs);
    });

    // Add Option
    $(document).on('click', '.add-option', function () {
        const blockIndex = $(this).closest('.field-block').data('index');
        $(this).siblings('.dropdown-options').append(`
            <div class="d-flex align-items-center mt-2 option-item">
                <input type="text" name="fields[${blockIndex}][options][]" class="form-control me-2 option-input" placeholder="Enter option">
                <button type="button" class="btn btn-danger btn-sm remove-option">X</button>
            </div>
        `);
    });

    // Remove Option
    $(document).on('click', '.remove-option', function () {
        $(this).closest('.option-item').remove();
    });

    // Submit Validation
    $('form').on('submit', function (e) {
        let valid = true;
        const formType = $('#form_type').val();

        $('.is-invalid').removeClass('is-invalid');
        $('.parameter-error, .option-error, .steps-error').addClass('d-none');

        $('.field-block').each(function () {
            const type = $(this).find('.field-type').val();
            const parameterInput = $(this).find('.parameter-input');

            // Validate parameter
            if (type === 'file' || type === 'image') {
                const paramValue = parameterInput.val().trim();
                const validParams = ['file', 'images', 'images[]'];
                if (!validParams.includes(paramValue)) {
                    parameterInput.addClass('is-invalid');
                    parameterInput.siblings('.parameter-error').removeClass('d-none');
                    valid = false;
                }
            }

            // Dropdown/Checkbox must have at least 1 option
            if (type === 'dropdown' || type === 'checkbox') {
                const optionInputs = $(this).find('.option-input');
                let hasValue = false;
                optionInputs.each(function () {
                    if ($(this).val().trim() !== '') hasValue = true;
                });
                if (!hasValue) {
                    $(this).find('.option-error').removeClass('d-none');
                    valid = false;
                }
            }
        });

        // Steps validation for Multi Form
        if (formType === 'multi') {
            let hasAtLeastOneStep = false;
            $('.steps-input').each(function () {
                if ($(this).val().trim() !== '') hasAtLeastOneStep = true;
            });

            if (!hasAtLeastOneStep) {
                $('.steps-error').first().removeClass('d-none');
                valid = false;
            }
        }

        if (!valid) e.preventDefault();
    });
});
</script>
@endsection
