@extends('admin.layout.app')
@section('title', 'Form Response Details')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h3>📋 Form Response Details</h3>
            <a href="{{ url('/admin/user') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="section-body">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">

                    {{-- Header Info --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Form Name</h6>
                            <h5 class="fw-bold">{{ $response->form_name }}</h5>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Company</h6>
                            <h5 class="fw-bold">{{ $response->company->name ?? '-' }}</h5>
                        </div>
                        <!-- <div class="col-md-4">
                            <h6 class="text-muted mb-1">User</h6>
                            <h5 class="fw-bold">{{ $response->user->name ?? '-' }}</h5>
                        </div> -->
                    </div>

                    <hr class="my-4">

                    {{-- Form Data --}}
                    @php
                        $data = json_decode($response->responses, true);
                        $formData = $data['form_data'] ?? $data ?? [];
                    @endphp

                    <h5 class="mb-3 text-primary"><i class="fa fa-list"></i> Submitted Form Data</h5>

                    @if (!empty($formData))
                        <div class="row">
                            @foreach ($formData as $key => $value)
                                @php
                                    $label = ucwords(str_replace(['_', '-'], ' ', $key));
                                    $lowerKey = strtolower($key);
                                @endphp

                                {{-- Skip image/file/upload fields --}}
                              @if (in_array($lowerKey, ['company_id', 'user_id', 'image', 'images', 'file', 'files', 'upload', 'attachments']))
									@continue
								@endif


                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-semibold">{{ $label }}</label>

                                    @if(is_array($value))
                                        <pre class="form-control bg-light" style="height: auto; white-space: pre-wrap;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        <div class="form-control bg-light">{{ $value }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted fst-italic">No form data available.</p>
                    @endif

                    {{-- Uploaded Files --}}
                    @php
                        $files = json_decode($response->files, true);
                        $allFiles = [];

                        if (is_array($files)) {
                            foreach ($files as $group) {
                                if (is_array($group)) {
                                    foreach ($group as $file) {
                                        $allFiles[] = $file;
                                    }
                                } else {
                                    $allFiles[] = $group;
                                }
                            }
                        }
                    @endphp

                    @if(!empty($allFiles))
                        <hr class="my-4">
                        <h5 class="mb-3 text-primary"><i class="fa fa-folder-open"></i> Uploaded Files</h5>

                        <div class="row">
                            @foreach($allFiles as $file)
                                @php
                                    $filePath = asset($file);
                                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                @endphp

                                <div class="col-md-4 mb-4 text-center">
                                    <div class="card border-0 shadow-sm rounded-4 h-100">
                                        <div class="card-body p-3">
                                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                <img src="{{ $filePath }}" alt="Image" class="img-fluid rounded mb-3" style="max-height: 200px; object-fit: cover;">
                                            @elseif($extension === 'pdf')
                                                <iframe src="{{ $filePath }}" width="100%" height="200" class="border rounded mb-2"></iframe>
                                            @elseif(in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']))
                                                <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($filePath) }}" width="100%" height="200" class="border rounded mb-2"></iframe>
                                            @else
                                                <i class="fa fa-file fa-3x text-secondary mb-2"></i>
                                                <p class="small text-muted mb-1">File: {{ strtoupper($extension) }}</p>
                                            @endif

                                            <a href="{{ $filePath }}" download class="btn btn-outline-primary btn-sm w-100">
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</div>
@endsection
