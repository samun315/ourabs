@extends('master')
@section('title', 'Total Diamonds')
@section('page_css')
    <link href="{{ asset('assets/custom/css/report/diamond/style.css') }}?v={{ filemtime(public_path('assets/custom/css/report/diamond/style.css')) }}"
        rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <x-toolbar-component title="Total Diamond list" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Manage Report', 'url' => 'javascript:void(0)'],
        ['label' => 'Total Diamonds', 'url' => route('report.total.diamond.index')],
        ['label' => 'Total Diamond list', 'active' => true],
    ]" />
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body pt-3 pb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex flex-column mb-5 fv-row">
                                <input type="text" class="form-control form-control-solid" name="search" id="search"
                                    placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex flex-column mb-5 fv-row">
                                <select name="role_id" data-control="select2" data-placeholder="Type"
                                    data-allow-clear="true" id="kt_role_id" class="form-select form-select-solid role_id">
                                    <option value=""></option>
                                    @foreach ($userRoles as $userRole)
                                        <option value="{{ $userRole?->role_id }}">
                                            {{ $userRole?->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!--begin::Table-->
                    <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dataTable no-footer w-100"
                                id="kt_total_diamonds_table">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>User Role</th>
                                        <th>Diamond Amount</th>
                                    </tr>
                                    <!--end::Table row-->
                                </thead>
                                <!--end::Table head-->
                            </table>
                        </div>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Container-->
    </div>
@endsection

@section('page_script')

    <script src="{{ asset('assets/custom/js/marchant/endUserBalanceRequest/export-libs.js') }}?v={{ filemtime(public_path('assets/custom/js/marchant/endUserBalanceRequest/export-libs.js')) }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/export/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/export/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/export/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/custom/js/marchant/endUserBalanceRequest/export-libs-init.js') }}?v={{ filemtime(public_path('assets/custom/js/marchant/endUserBalanceRequest/export-libs-init.js')) }}"></script>

    <script src="{{ asset('assets/custom/js/report/diamond/index.js') }}?v={{ filemtime(public_path('assets/custom/js/report/diamond/index.js')) }}"></script>

@endsection
