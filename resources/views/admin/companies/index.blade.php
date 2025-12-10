@extends('admin.layout.app')
@section('title', 'Companies Forms List')

@section('content')
<div class="main-content" style="min-height: 562px;">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Users</h4>
                        </div>
						
                        <div class="card-body table-striped table-bordered table-responsive">
							<a href="{{ route('forms.create') }}" class="btn btn-primary">Create</a>
                            <table class="table" id="table_id_events">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Company Name</th>
                                        <th>Form Name</th>
                                        <th>Form Type</th>
                                        <th>View Form Details</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($forms as $form )
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
										<td>{{ $form->company->name ?? '-' }}</td>
										<td>{{ $form->form_name ?? '-' }}</td>
										 <td>{{ $form->form_step_type ?? '-' }}</td>
                                       	<td>
											<a href="{{ route('admin.companies.show', $form->form_no) }}" class="btn btn-primary btn-sm">
												<i class="fa fa-eye"></i>
											</a>
									</td>
                                        <td style="vertical-align: middle;">
                                            <div class="d-flex align-items-center" style="gap: 6px;">
                                                @if (Auth::guard('admin')->check() ||
                                                ($sideMenuPermissions->has('Users') && $sideMenuPermissions['Users']->contains('edit')))
                                                @endif

                                                @if (Auth::guard('admin')->check() ||
                                                ($sideMenuPermissions->has('Users') && $sideMenuPermissions['Users']->contains('delete')))
                                                <form id="delete-form-{{ $form->id }}"
                                                    action="{{ route('admin.form-fields.destroy', $form->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                                <button class="show_confirm btn p-2"
                                                    style="background-color: #cb84fe;"
                                                    data-form="delete-form-{{ $form->id }}" type="button">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
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

<!-- Deactivation Modal -->
<div class="modal fade" id="deactivationModal" tabindex="-1" role="dialog" aria-labelledby="deactivationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deactivation Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="deactivationForm">
                    @csrf
                    <input type="hidden" name="user_id" id="deactivatingUserId">
                    <div class="form-group">
                        <label>Reason for deactivation:</label>
                        <textarea class="form-control" id="deactivationReason" name="reason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDeactivation">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

<script>
   $(document).ready(function() {
    $('#table_id_events').DataTable();

    let currentToggle = null;
    let currentUserId = null;

    // ✅ Event delegation for dynamically loaded rows
    $(document).on('change', '.toggle-status', function() {
        let status = $(this).is(':checked') ? 1 : 0;
        currentToggle = $(this);
        currentUserId = $(this).data('id');

        if (status === 0) {
            $('#deactivatingUserId').val(currentUserId);
            $('#deactivationModal').modal('show');
        } else {
            updateUserStatus(currentUserId, 1);
        }
    });

    $('#confirmDeactivation').click(function() {
        let reason = $('#deactivationReason').val();
        if (reason.trim() === '') {
            toastr.error('Please provide a deactivation reason');
            setTimeout(() => location.reload(), 800);
            return;
        }

        $('#deactivationModal').modal('hide');
        $('#deactivationReason').val('');
        updateUserStatus(currentUserId, 0, reason);
    });

    $('#deactivationModal').on('hidden.bs.modal', function() {
        if ($('#deactivationReason').val().trim() === '') {
            setTimeout(() => location.reload(), 500);
        }
    });

    function updateUserStatus(userId, status, reason = null) {
        let $descriptionSpan = currentToggle.siblings('.custom-switch-description');
        $.ajax({
            url: "{{ route('user.toggle-status') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                id: userId,
                status: status,
                reason: reason
            },
            success: function(res) {
                if (res.success) {
                    $descriptionSpan.text(res.new_status);
                    toastr.success(res.message);
                    location.reload();
                } else {
                    currentToggle.prop('checked', !status);
                    toastr.error(res.message);
                }
            },
            error: function() {
                currentToggle.prop('checked', !status);
                toastr.error('Error updating status');
            }
        });
    }

        //deleting alert

       $(document).on('click', '.show_confirm', function(e) {
                e.preventDefault();
                let formId = $(this).data('form');
                let form = document.getElementById(formId);

                Swal.fire({
                    title: 'Are you sure you want to delete this record?',
                    text: "If you delete this Company Form record, it will be gone forever.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.action,
                            method: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Recored deleted successfully.',
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(() => location.reload());
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete the record.',
                                    'error');
                            }
                        });
                    }
                });
            });
    });
</script>
@endsection