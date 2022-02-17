@extends('common.layout.master')

{{-- CSS / JS FILE SOURCE --}}
@include('modules.journey_planner.components.config', ['page' => asset('js/modules/journey_planner/telesales.js')])

{{-- MASTER BREADCRUMB --}}
@section('master_breadcrumb')
    <a href="{{ route('journey-planner.dashbord', ['supplierName' => request()->route('supplierName')]) }}" class="navbar-item">
        Journey Planner
    </a>
    <a href="#" class="navbar-item">
        Outlet Management
    </a>
    <a href="#" class="navbar-item current-navbar">
        {{ $customerInfo['company_name'] }}
    </a>
@endsection

@section('pageClass', 'journey-planner outlet-management')

{{-- PAGE TITLE --}}
@section('title', 'Outlet Management')

@section('maincontent')

    {{-- MODAL --}}
    @include('modules.journey_planner.components.modals')

    {{-- NAVBAR --}}
    @include('common.layout.navbar')

    {{-- SIDEBAR --}}
    @include('common.layout.sidebar')

    <div class="main-container">

        {{-- SIDEBAR --}}
        <sidebar>
            <template #user-details>
                <user-info :user-info="{{ json_encode($customerInfo) }}"></user-info>
                <sidebar-info :sidebar-info="{{ json_encode($financialInfo) }}"></sidebar-info>
            </template>

            <template #container>
                {{-- INFO --}}
                <div class="text-medium mb-3">Info</div>
                <contact-time-table></contact-time-table>
                <editable-address></editable-address>
                <editable-remark :user-remark="{{ json_encode($userRemark) }}"></editable-remark>
                <hr>

                {{-- PERSON IN CHARGE --}}
                {{-- <div class="d-flex justify-content-between">
                    <div class="text-medium mb-3">Person In Charge</div>
                    <i class="icon-add cursor-pointer" data-toggle="modal" data-target="#modal-pic"></i>
                </div>

                <div class="editable-field">
                    <div class="sidebar-info">
                        <div class="title">Manager <i class="icon-edit btn-edit"></i></div>
                        <div class="value">
                            @php
                                // DEMO DATA
                                $userdata = [
                                    'u1' => [
                                        'id' => '0',
                                        'info' => [
                                            'code' => 'FA001',
                                            'name' => 'Wu Chun Hei  ',
                                            'image' => '',
                                            'contact' => '+6019168860',
                                            'email' => 'tsuichiho@gmail.com',
                                        ],
                                        'details' => [
                                            'company' => ['title' => 'Company', 'value' => 'Sistem Elektrik Ah Liang Enterprise'],
                                            'job' => ['title' => 'Job Title', 'value' => 'Manager'],
                                            'remark' => ['title' => 'Remark', 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum dui varius aliquam aliquet tellus. '],
                                        ],
                                    ],
                                ];
                            @endphp
                            <user-role :user-data="{{ json_encode($userdata) }}"></user-role>
                        </div>
                    </div>
                    <div class="btn-edit-group">
                        <button class="btn-rounded light btn-cancel-edit">Cancel</button>
                        <button class="btn-rounded success ml-2 btn-save-edit">Save</button>
                    </div>
                </div>

                <div class="editable-field">
                    <div class="sidebar-info">
                        <div class="title">Assistant Manager <i class="icon-edit btn-edit"></i></div>
                        <div class="value">
                            @php
                                // DEMO DATA
                                $userdata = [
                                    'u1' => [
                                        'id' => '1',
                                        'info' => [
                                            'code' => 'FA001',
                                            'name' => 'Wu Yan Yi  ',
                                            'image' => '/assets/graphics/img-product.svg',
                                            'contact' => '+6019168860',
                                            'email' => 'tsuichiho@gmail.com',
                                        ],
                                        'details' => [
                                            'company' => ['title' => 'Company', 'value' => 'Sistem Elektrik Ah Liang Enterprise'],
                                            'job' => ['title' => 'Job Title', 'value' => 'Manager'],
                                            'remark' => ['title' => 'Remark', 'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum dui varius aliquam aliquet tellus. '],
                                        ],
                                    ],
                                ];
                            @endphp
                            <user-role :user-data="{{ json_encode($userdata) }}"></user-role>
                        </div>
                    </div>
                    <div class="btn-edit-group">
                        <button class="btn-rounded light btn-cancel-edit">Cancel</button>
                        <button class="btn-rounded success ml-2 btn-save-edit">Save</button>
                    </div>
                </div> --}}
            </template>
        </sidebar>

        {{-- CONTENTS --}}
        <div class="contents">
            {{-- MENU --}}
            <div class="top-menu">
                <div class="title">
                    {{ $customerInfo['company_name'] }}
                </div>
            </div>

            <div class="frame rounded action-list mb-4">
                <call-log-create customer-id="{{ Route::input('customerId') }}"></call-log-create>

                <div class="action-item">
                    <a href="{{ route('documents.create', ['supplierName' => request()->route('supplierName'), 'customerId' => request()->route('customerId')]) }}">
                        <div class="cursor-pointer">
                            <div class="icon-circle">
                                <i class="icon-sales-order-document"></i>
                            </div>
                            <div class="text-center pt-2">Sales Order</div>
                        </div>
                    </a>
                </div>

                <note-create customer-id="{{ Route::input('customerId') }}"></note-create>
            </div>

            {{-- TAB MENU --}}
            <ul class="nav module-menu pb-4" role="tablist">
                <li class="nav-item">
                    <a class="menu-item active" id="outlet-activity-tab" data-toggle="tab" href="#outlet-activity" role="tab" aria-controls="outlet-activity"
                        aria-selected="true">Activity</a>
                </li>
                <li class="nav-item">
                    <a class="menu-item" id="outlet-document-tab" data-toggle="tab" href="#outlet-document" role="tab" aria-controls="outlet-document"
                        aria-selected="false">Document</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="menu-item" id="outlet-visit-tab" data-toggle="tab" href="#outlet-visit" role="tab" aria-controls="outlet-visit" aria-selected="false">Visit</a>
                </li> --}}
            </ul>

            <div class="tab-content">
                {{-- TAB - OUTLET - ACTIVITY --}}
                <div class="tab-pane fade show active" id="outlet-activity" role="tabpanel" aria-labelledby="outlet-activity-tab">
                    <outlet-activity-tab customer-id="{{ Route::input('customerId') }}" :users="{{ json_encode($users) }}" ref="outletTab"></outlet-activity-tab>
                </div>

                {{-- TAB - OUTLET - DOCUMENT --}}
                <div class="tab-pane fade" id="outlet-document" role="tabpanel" aria-labelledby="outlet-document-tab">


                    <div class="frame rounded">
                        <div class="table-content">
                            <bo-datatable ref="bo_table" title="All Documents" :columns="{{ json_encode($columns) }}" get-records-url="{{ $recordsUrl }}"
                                getRecordsUrl="{{ $recordsUrl }}">
                            </bo-datatable>
                        </div>
                    </div>
                </div>

                {{-- TAB - OUTLET - VISIT --}}\
                <div class="tab-pane fade" id="outlet-visit" role="tabpanel" aria-labelledby="outlet-visit-tab">
                    <journey-table></journey-table>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST - SUCCESS --}}
    <bo-toast ref="toast"></bo-toast>

    {{-- PREFERRED CONTACT TIME --}}
    <editable-contact-timetable customer-id="{{ Route::input('customerId') }}"></editable-contact-timetable>
@endsection

@push('script')
    @include('common.js-datatable')
    <script>
        $('.editable-field').on('click', '.btn-edit', function() {
            $(this).closest('.editable-field').addClass('edit');
        });

        $('.btn-edit-group').on('click', 'button', function() {
            $(this).closest('.editable-field').removeClass('edit');
        });

        $('.dropdown-menu input, .dropdown-menu select').on('click', function(event) {
            event.stopPropagation();
        });
    </script>
@endpush
