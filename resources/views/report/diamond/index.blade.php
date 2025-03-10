@extends('master')
@section('title', 'Total Diamonds')
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
                <div class="card-body py-4">
                    <form id="kt_filter_form_register">
                        <div class="row">

                            <x-search />
                        </div>
                    </form>
                    <!--begin::Table-->
                    <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                                id="kt_total_diamonds_table">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase fw-bolder fs-7 gs-0">
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

    <!-- begin::Page Custom Stylesheets(used by this page) -->
    <script src="{{ asset('assets/custom/js/report/diamond/index.js') }}"
        {{ Sri::html('assets/custom/js/report/diamond/index.js') }}></script>
    <!--end::Page Custom Stylesheets(used by this page)-->

@endsection
