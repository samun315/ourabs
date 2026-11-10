@extends('master')
@section('title', 'Add End User Balance')
@section('page_css')
    <link href="{{ asset('assets/custom/css/endUserBalanceRequest/style.css') }}"
        {{ Sri::html('assets/custom/css/endUserBalanceRequest/style.css') }} rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <x-toolbar-component title="Add End User Balance" :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Order Management', 'url' => 'javascript:void(0)'],
        ['label' => 'User Balance Request', 'url' => 'javascript:void(0)'],
        ['label' => 'Add End User Balance', 'active' => true],
    ]"
        actionUrl="{{ route('marchant.sub-distributor.balance.request.index') }}" actionIcon="fas fa-table"
        actionLabel="Balance list" />
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body py-4">
                    @include('message')
                    <form method="POST" action="{{ route('marchant.sub-distributor.balance.request.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <input type="text" hidden name="current_balance" id="kt_current_balance" value="{{$account?->current_balance}}">

                        <div class="row">
                            <div class="col-md-6 fv-row mb-5">
                                <label class="required fs-5 fw-bold mb-2">IMO Id</label>
                                <input type="text" name="imo_id" id="kt_imo_id"
                                    class="form-control form-control-solid imo_id bg-gradient @error('imo_id') is-invalid @enderror"
                                    placeholder="Enter IMO Id" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '')" pattern="[0-9]*" inputmode="numeric" required />
                                @error('imo_id')
                                    <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- <div class="col-md-6 fv-row mb-5">
                                <label class="fs-5 fw-bold mb-2">Phone Number</label>
                                <input type="text" name="phone_number" id="kt_phone_number"
                                    class="form-control form-control-solid phone_number bg-gradient @error('phone_number') is-invalid @enderror"
                                    placeholder="Enter Phone Number" autocomplete="off" />
                                @error('phone_number')
                                    <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div> --}}
                            <div class="col-md-12">
                                <label class="required fs-5 fw-bold mb-2">Diamonds</label>
                                <div class="row">
                                    <div class="col-md-6 fv-row">
                                        <input type="number" class="form-control form-control-solid @error('amount') is-invalid @enderror"
                                            name="amount" id="diamond_amount" min="10" max="40000" required
                                            placeholder="Enter Amount (Min 10, Max 40000)">
                                        @error('amount')
                                            <span class="text-danger mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    {{-- @foreach ($diamonds as $diamond)
                                        <div class="col-md-6 fv-row">
                                            <label class="diamond-option text-left"
                                                for="diamond_amount_{{ $diamond->diamond_amount_id }}">
                                                <input type="radio" class="amount" name="amount"
                                                    value="{{ $diamond->amount }}"
                                                    id="diamond_amount_{{ $diamond->diamond_amount_id }}" hidden>
                                                <div class="diamond-box">
                                                    <span class="diamond-text">{{ strtoupper($diamond->amount) }}
                                                        Diamond</span>
                                                    <span class="checkmark">&#10004;</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach --}}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9 fv-row mb-5"></div>
                                <div class="col-md-3 fv-row mb-5 d-grid">
                                    <button type="submit" class="btn btn-primary me-5">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </form>

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
    <script src="{{ asset('assets/custom/js/marchant/endUserBalanceRequest/index.js') }}?v={{ filemtime(public_path('assets/custom/js/marchant/endUserBalanceRequest/index.js')) }}"
        {{ Sri::html('assets/custom/js/marchant/endUserBalanceRequest/index.js') }}></script>
    <!--end::Page Custom Stylesheets(used by this page)-->

@endsection
