@extends('admin.layout.app')
@section('title', 'User Form Responses')

@section('content')
<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>User Form Responses</h4>
                        </div>

                        <div class="card-body table-striped table-bordered table-responsive">
                            <table class="table" id="table_id_events">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Form Name</th>
                                        <th>Company Name</th>
                                        <th>User Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($responses as $response)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $response->form_name ?? '-' }}</td>
                                            <td>{{ $response->company->name ?? '-' }}</td>
                                            <td>{{ $response->user->name ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('form_responses.show', $response->id) }}" 
                                                   class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fa fa-eye"></i> 
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- /.card-body -->
                    </div> <!-- /.card -->
                </div> <!-- /.col -->
            </div> <!-- /.row -->
        </div> <!-- /.section-body -->
    </section>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#table_id_events').DataTable();
    });
</script>
@endsection
