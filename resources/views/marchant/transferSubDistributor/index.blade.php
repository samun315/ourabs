@extends('master')
@section('title', 'Transfer Balance')
@section('page_css')
    <link href="{{ asset('assets/custom/css/transferSubDistributor/style.css') }}?v={{ filemtime(public_path('assets/custom/css/transferSubDistributor/style.css')) }}"
        rel="stylesheet" type="text/css" />
@endsection
@section('content')
    @can('marchant.transfer.balance.sub-distributor.create')
        <x-toolbar-component title="Transfer Balance List" :breadcrumbs="[
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => 'Transfer Management', 'url' => 'javascript:void(0)'],
            ['label' => 'Transfer Balance List', 'active' => true],
        ]" modalTarget="openTransferBalanceModal"
            actionIcon="fas fa-plus-circle" actionLabel="Create" />
    @endcan
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-4 pb-0">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                        rx="1" transform="rotate(45 17.0365 15.1223)" fill="black"></rect>
                                    <path
                                        d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                        fill="black"></path>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <x-search />
                        </div>
                        <!--end::Search-->
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-3 pb-4">
                    @include('message')
                    <!--begin::Table-->
                    <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle dataTable no-footer w-100"
                                id="kt_table_transfer_balance">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr>
                                        <th>ID</th>
                                        <th>From</th>
                                        <th>To </th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>
                                            <span class="d-none d-md-inline">Created Time</span>
                                            <span class="d-md-none">Date</span>
                                        </th>
                                        <th>
                                            <span class="d-none d-md-inline">Actions</span>
                                            <span class="d-md-none">Act</span>
                                        </th>
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
            {{-- begin:: Balance Request modal --}}
            @include('marchant.transferSubDistributor.modal.addTransferModal')
            {{-- end:: Balance Request modal --}}
            @include('marchant.transferSubDistributor.modal.showTransferModal')
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

    <script src="{{ asset('assets/custom/js/marchant/transferSubDistributor/index.js') }}?v={{ filemtime(public_path('assets/custom/js/marchant/transferSubDistributor/index.js')) }}"></script>

@endsection
