@extends('master')
@section('title', 'User List')
<style nonce="{{ $cspNonce }}">
    .passwordDiv {
        position: relative;
    }

    #error-message {
        color: red;
        font-size: 14px;
        display: none;
    }

    .togglePasswordBtn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
    }
</style>
@section('content')
    <x-toolbar-component title="User List" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'User Management', 'url' => 'javascript:void(0)'],
        ['label' => 'Access Control', 'url' => 'javascript:void(0)'],
        ['label' => 'User List', 'active' => true],
    ]" modalTarget="openUserModal" actionIcon="fas fa-plus-circle"
        actionLabel="Add New" />
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                        <!--begin::Search-->
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="d-flex flex-column mb-5 fv-row">
                                            <input type="text" class="form-control form-control-solid" name="search"
                                                id="search" placeholder="Search">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column mb-5 fv-row">
                                            <select name="role_id" data-control="select2" data-placeholder="Type"
                                                data-allow-clear="true" id="kt_search_role_id"
                                                class="form-select form-select-solid role_id">
                                                <option value=""></option>
                                                @foreach ($userRoles as $userRole)
                                                    <option value="{{ $userRole?->role_id }}">
                                                        {{ $userRole?->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!--end::Search-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                                id="kt_table_users">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase fw-bolder fs-7 gs-0">
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Role</th>
                                        <th>Active</th>
                                        <th class="text-center">Action</th>
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
            {{-- begin:: User modal --}}
            @include('user.modal.addUserModal')
            {{-- end:: User modal --}}

            {{-- begin:: User passowrd change modal --}}
            @include('user.modal.changeUserPasswordModal')
            {{-- end:: User passowrd change modal --}}

        </div>
        <!--end::Container-->
    </div>
@endsection

@section('page_script')

    <!-- begin::Page Custom Stylesheets(used by this page) -->
    <script src="{{ asset('assets/custom/js/user/index.js') }}"
        {{ Sri::html('assets/custom/js/user/index.js') }}></script>
    <!--end::Page Custom Stylesheets(used by this page)-->

@endsection
