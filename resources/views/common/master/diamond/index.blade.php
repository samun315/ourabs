@extends('master')

@section('title', 'Diamond List')
@section('content')

    <!--begin::Toolbar -->
    <x-toolbar-component title="Diamond List" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Wallet Management', 'url' => 'javascript:void(0)'],
        ['label' => 'Diamond', 'url' => route('common.master.diamond.index')],
        ['label' => 'Diamond List', 'active' => true],
    ]" modalTarget="openDiamondCreateModal"
        actionIcon="fas fa-plus-circle" actionLabel="ADD" />
    <!--end::Toolbar -->

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">

                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <x-search />
                </div>
                <!--end::Header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    @include('message')

                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="diamond-table">
                            <!--begin::Table head-->
                            <thead>
                                <!--begin::Table row-->
                                <tr class="text-start text-muted text-uppercase fw-bolder fs-7 gs-0">
                                    <th>#</th>
                                    <th>Amount</th>
                                    <th>Active</th>
                                    <th>Action</th>
                                </tr>
                                <!--end::Table row-->
                            </thead>
                            <!--end::Table head-->
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            {{-- begin:: Diamond modal --}}
            @include('common.master.diamond.modal.addDiamondModal')
            {{-- end:: Diamond modal --}}
        </div>
        <!--end::Container-->
    </div>

@endsection

@section('page_script')

    <!-- begin::Page Custom Stylesheets(used by this page) -->
    <script src="{{ asset('assets/custom/js/common/master/diamond/index.js') }}"
        {{ Sri::html('assets/custom/js/common/master/diamond/index.js') }}></script>
    <!--end::Page Custom Stylesheets(used by this page)-->

@endsection
