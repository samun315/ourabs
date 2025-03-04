@extends('master')
@section('title', 'Transaction History')
@section('content')
    <x-toolbar-component title="Transaction History list" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Manage Report', 'url' => 'javascript:void(0)'],
        ['label' => 'Transaction History', 'url' => route('report.transaction.history.index')],
        ['label' => 'Transaction History list', 'active' => true],
    ]" />
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body py-4">
                    <form id="kt_filter_form_register">
                        <div class="row">

                            <div class="col-md-3">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <select name="type" data-control="select2" data-placeholder="Type"
                                        data-allow-clear="true" id="kt_type" class="form-select form-select-solid type">
                                        <option value=""></option>
                                        @foreach ($transactionTypes as $transactionType)
                                            <option value="{{ $transactionType }}">
                                                {{ $transactionType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                                id="kt_table_transaction_history">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase fw-bolder fs-7 gs-0">
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Before Balance</th>
                                        <th>After Balance</th>
                                        <th>Date Time</th>
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

    <!-- begin::Page Custom Stylesheets(used by this page) -->
    <script src="{{ asset('assets/custom/js/report/transactionHistory/index.js') }}"
        {{ Sri::html('assets/custom/js/report/transactionHistory/index.js') }}></script>
    <!--end::Page Custom Stylesheets(used by this page)-->

@endsection
