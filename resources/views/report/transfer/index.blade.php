@extends('master')
@section('title', 'Transfer Report')
@section('page_css')
    <link href="{{ asset('assets/custom/css/report/transfer/style.css') }}?v={{ filemtime(public_path('assets/custom/css/report/transfer/style.css')) }}"
        rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <x-toolbar-component title="Transfer Report list" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Manage Report', 'url' => 'javascript:void(0)'],
        ['label' => 'Transfer Report', 'url' => route('report.transfer.index')],
        ['label' => 'Transfer Report List', 'active' => true],
    ]" />
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body pt-3 pb-4">
                    <form id="kt_filter_form_register">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <input class="form-control form-control-solid" placeholder="Pick date range" id="kt_start_to_end_date"/>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--begin::Table-->
                    <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dataTable no-footer w-100"
                                id="kt_table_transfer_report">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Total Amount</th>
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

    <script src="{{ asset('assets/custom/js/report/transfer/index.js') }}?v={{ filemtime(public_path('assets/custom/js/report/transfer/index.js')) }}"></script>

@endsection
