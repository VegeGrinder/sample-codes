@extends('common.layout.master')

{{-- CSS / JS FILE SOURCE --}}
@include('modules.payment.components.config')

@section('title', _t('PAYMENT'))
@section('pageClass','payment')

@section('maincontent')
{{-- NAVBAR --}}
@include('modules.payment.components.navbar')

{{-- BREADCRUMB --}}
@include('modules.payment.components.breadcrumb', ['breadcrumbTitle' => _t('PAYMENT')])

{{-- MODALS --}}
@include('modules.payment.components.modals')

{{-- CONTENTS --}}
<div class="container main-content">
    {{ Form::open(['class' => 'panel-summary payment-methods']) }}
        {{-- SUMMARY --}}
        <div class="row summary-pills">
            <div class="col-lg-4 col-md-6">
                <div class="pill">
                    {{-- OWE --}}
                    <div class="left-section">
                        <div class="title">
                            <span>
                                {{ _t('YOU OWE', 'UPPERWORDS') }}
                            </span>
                            <span class="badge success">
                                {{ count($documentsAmountOwed) }}
                            </span>
                        </div>
                        <div class="amount">
                            {{ LocaleUtility::getCurrencyValueFormatted(array_sum($documentsAmountOwed)) }}
                        </div>
                    </div>

                    {{-- OVERDUE --}}
                    <div class="right-section">
                        <div class="title">
                            <span class="txt-danger">
                                {{ _t('OVERDUE') }}
                            </span>
                            <span class="badge danger">
                                {{ count($documentsAmountOverdue) }}
                            </span>
                        </div>
                        <div class="amount txt-danger">
                            {{ LocaleUtility::getCurrencyValueFormatted(array_sum($documentsAmountOverdue)) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pill">
                    {{-- CREDIT LIMIT --}}
                    <div class="left-section">
                        <div class="title">
                            {{ _t('COMBINED CREDIT LIMIT', 'UPPERWORDS') }}
                        </div>
                        <div class="amount">
                            {{ app('User')->getCreditLimitFormatted() }}
                        </div>
                    </div>

                    {{-- CREDIT TERM --}}
                    <div class="right-section">
                        <div class="title">
                            {{ _t('CREDIT TERM', 'UPPERWORDS') }}
                        </div>
                        <div class="amount">
                            {{ app('User')->getCreditTerm() }} {{ _t('DAY(S)') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL | PAY FOR --}}
        <div class="panel mt-4 pay-for">
            {{-- PANEL HEADER --}}
            <div class="panel-header" data-toggle="collapse" data-target="#body-pay-for" aria-expanded="false" aria-controls="body-pay-for">
                <div class="panel-title">
                    {{ _t('PAYING FOR', 'UPPERWORDS') }}
                </div>
                <div class="indicator">
                    <i class="icon-arclight icon-arrowdropup"></i>
                </div>
            </div>

            {{-- PANEL BODY --}}
            <div class="panel-body collapse show" id="body-pay-for">
                <div class="panel-content">
                    <div class="row">
                        {{-- LEFT PANEL --}}
                        <div class="col-xl-8">
                            <div class="responsive-table">
                                <table id="table-pay-for" class="table has-filter">
                                    <thead>
                                        <tr>
                                            <th class="checkbox" data-datatable-type=""><input type="checkbox"></th>
                                            <th class="id" data-datatable-type="text">{{ _t('ID', 'UPPER') }}</th>
                                            <th class="duedate" data-datatable-type="date">{{ _t('DUE DATE', 'UPPERWORDS') }}</th>
                                            <th class="status" data-datatable-type="select" data-datatable-tag="status">{{ _t('STATUS') }}</th>
                                            <th class="document" data-datatable-type="">{{ _t('DOCUMENT') }}</th>
                                            <th class="amount" data-datatable-type="">{{ _t('OUTSTANDING') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($documents['invoice'] as $document)
                                        <tr class="{{ $document->_view['status'] == 'Overdue' ? 'overdue' : '' }}" data-target="#modal-invoice-details" data-target-json="{{ $document->_view['_json'] }}">
                                            <td class="checkbox">
                                                <input type="checkbox"
                                                    name="payment[invoice_document_ids][{{ $document->id }}]"
                                                    value="{{ $document->number }}"
                                                    data-document-number="{{ $document->number }}"
                                                    data-document-amount="{{ $document->amount_outstanding }}"
                                                    data-document-is_selected="{{ $document->_is_selected }}">
                                            </td>
                                            <td class="id">{{ $document->number }}</td>
                                            <td class="duedate" data-order="{{ DateTime::createFromFormat('d/m/Y', $document->date_due_formatted)->getTimestamp() }}">{{ $document->date_due_formatted }}</td>
                                            <td class="status" data-datatable-tag="status">
                                                <div class="badge {{ $document->_view['status'] == 'Overdue' ? 'danger' : 'primary' }}">
                                                    {{ _t($document->_view['status']) }}
                                                </div>
                                            </td>
                                            <td class="document">
                                                <span>{{ _t('INVOICE') }}</span>
                                            </td>
                                            <td class="amount" data-order="{{ $document->amount_outstanding }}">{{ $document->amount_outstanding_formatted }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- RIGHT PANEL --}}
                        <div class="col-xl-4 d-none d-xl-block pl-0">
                            <div class="panel-summary">
                                {{-- HEADER --}}
                                <div class="panel-summary-header">
                                    <div class="title">
                                        {{ _t('SELECTED') }}
                                    </div>
                                    <div class="counts">
                                        <span class="amount">
                                            0
                                        </span>
                                        <span class="quantity">
                                            {{ _t('ITEM(S)') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- CONTENT --}}
                                <div class="panel-summary-listing">
                                @foreach ($documents['invoice'] as $document)
                                    <div class="invoice {{ $document->status }}"
                                        style="display: none;"
                                        data-target="#modal-invoice-details"
                                        data-target-json="{{ $document->_view['_json'] }}"
                                        data-document-number-show="{{ $document->number }}">
                                        <div class="inv-info">
                                            <div class="inv-id">
                                                {{ $document->number }} ({{ _t($document->_view['status']) }})
                                            </div>
                                            <div class="inv-amount">
                                                {{ $document->amount_outstanding_formatted }}
                                            </div>
                                        </div>
                                        <div class="inv-date">
                                            {{ _t('DUE') }}: {{ $document->date_due_formatted }}
                                        </div>
                                    </div>
                                @endforeach
                                </div>

                                {{-- FOOTER --}}
                                <div class="panel-summary-footer">
                                    <div class="title">
                                        {{ _t('TOTAL INVOICE', 'UPPERWORDS') }}
                                    </div>
                                    <div class="amount">
                                        {{ LocaleUtility::getCurrencySymbol() }} <span data-document-invoice-amount>0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL | PAY WITH --}}
        <div class="panel mt-4 pay-with">
            {{-- PANEL HEADER --}}
            <div class="panel-header" data-toggle="collapse" data-target="#body-pay-with" aria-expanded="false" aria-controls="body-pay-with">
                <div class="panel-title">
                    {{ _t('PAYING WITH', 'UPPERWORDS') }}
                </div>
                <div class="indicator">
                    <i class="icon-arclight icon-arrowdropup"></i>
                </div>
            </div>

            {{-- PANEL BODY --}}
            <div class="panel-body collapse show" id="body-pay-with">
                <div class="panel-content">
                    <div class="row">
                        {{-- LEFT PANEL --}}
                        <div class="col-xl-8">
                            <div class="responsive-table">
                                <table id="table-pay-with" class="table has-filter">
                                    <thead>
                                        <tr>
                                            <th class="checkbox" data-datatable-type=""><input type="checkbox"></th>
                                            <th class="id" data-datatable-type="text">{{ _t('ID', 'UPPER') }}</th>
                                            <th class="duedate" data-datatable-type="date">{{ _t('DATE') }}</th>
                                            <th class="document" data-datatable-type="">{{ _t('DOCUMENT') }}</th>
                                            <th class="amount" data-datatable-type="">{{ _t('UNAPPLIED') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($documents['credit_note'] as $document)
                                        <tr data-target="#modal-credit_note-details" data-target-json="{{ $document->_view['_json'] }}">
                                            <td class="checkbox">
                                                <input type="checkbox"
                                                    name="payment[credit_note_document_ids][{{ $document->id }}]"
                                                    value="{{ $document->number }}"
                                                    data-document-number="{{ $document->number }}"
                                                    data-document-amount="{{ $document->amount_unapplied }}"
                                                    data-document-is_selected="{{ $document->_is_selected }}">
                                            </td>
                                            <td class="id">{{ $document->number }}</td>
                                            <td class="duedate" data-order="{{ DateTime::createFromFormat('d/m/Y', $document->_view['date'])->getTimestamp() }}">{{ $document->_view['date'] }}</td>
                                            <td class="document">
                                                <span>{{ _t('CREDIT NOTE', 'UPPERWORDS') }}</span>
                                            </td>
                                            <td class="amount" data-order="{{ $document->amount_unapplied }}">{{ $document->amount_unapplied_formatted }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- RIGHT PANEL --}}
                        <div class="col-xl-4 d-none d-xl-block pl-0">
                            <div class="panel-summary">
                                {{-- HEADER --}}
                                <div class="panel-summary-header">
                                    <div class="title">
                                        {{ _t('SELECTED') }}
                                    </div>
                                    <div class="counts">
                                        <span class="amount">
                                            0
                                        </span>
                                        <span class="quantity">
                                            {{ _t('ITEM(S)') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- CONTENT --}}
                                <div class="panel-summary-listing">
                                @foreach ($documents['credit_note'] as $document)
                                    <div class="invoice"
                                        style="display: none;"
                                        data-target="#modal-credit_note-details"
                                        data-target-json="{{ $document->_view['_json'] }}"
                                        data-document-number-show="{{ $document->number }}">
                                        <div class="inv-info">
                                            <div class="inv-id">
                                                {{ $document->number }}
                                            </div>
                                            <div class="inv-amount">
                                                {{ $document->amount_unapplied_formatted }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>

                                {{-- FOOTER --}}
                                <div class="panel-summary-footer">
                                    <div class="title">
                                        {{ _t('TOTAL CREDIT NOTE', 'UPPERWORDS') }}
                                    </div>
                                    <div class="amount">
                                        {{ LocaleUtility::getCurrencySymbol() }} <span data-document-credit_note-amount>0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PANEL | PAYMENT CONFIRMATION --}}
        @if ($paymentMethods)
            <div class="panel mt-4 pay-confirmation">
                {{-- PANEL HEADER --}}
                <div class="panel-header" data-toggle="collapse" data-target="#body-pay-confirmation" aria-expanded="false" aria-controls="body-pay-confirmation">
                    <div class="panel-title">
                        {{ _t('Payment Confirmation', 'UPPERWORDS') }}
                    </div>
                    <div class="indicator">
                        <i class="icon-arclight icon-arrowdropup"></i>
                    </div>
                </div>

                {{-- PANEL BODY --}}
                <div class="panel-body collapse show" id="body-pay-confirmation">
                    <div class="panel-content">
                        <div class="panel-content">
                            <div class="row">
                                <div class="col-xl-8">
                                    {{-- SUMMARY PAY FOR --}}
                                    <div class="summary-pay-for">
                                        {{-- MINI PANEL HEADER - PAY FOR --}}
                                        <div class="panel-header mini" data-toggle="collapse" data-target="#panel-confirmation-pay-for" aria-expanded="false" aria-controls="panel-confirmation-pay-for">
                                            <div class="panel-title">
                                                {{ _t('PAYMENT FOR', 'UPPERWORDS') }}
                                            </div>
                                            <div class="indicator">
                                                <i class="icon-arclight icon-arrowdropup"></i>
                                            </div>
                                        </div>
                                        {{-- MINI PANEL BODY - PAY FOR --}}
                                        <div class="panel-body collapse show" id="panel-confirmation-pay-for">
                                            <div class="responsive-table">
                                                <table id="table-summary-pay-for" class="table has-filter">
                                                    <thead>
                                                        <tr>
                                                            <th class="id" data-datatable-type="text">{{ _t('ID', 'UPPER') }}</th>
                                                            <th class="duedate" data-datatable-type="date">{{ _t('DUE DATE', 'UPPERWORDS') }}</th>
                                                            <th class="status" data-datatable-type="select" data-datatable-tag="status">{{ _t('STATUS') }}</th>
                                                            <th class="document" data-datatable-type="">{{ _t('DOCUMENT') }}</th>
                                                            <th class="amount" data-datatable-type="">{{ _t('OUTSTANDING') }}</th>
                                                            <th class="remove" data-datatable-type=""></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($documents['invoice'] as $document)
                                                        <tr class="{{ $document->status }}"
                                                            style="display: none;"
                                                            data-target="#modal-invoice-details"
                                                            data-target-json="{{ $document->_view['_json'] }}"
                                                            data-document-number-show="{{ $document->number }}">
                                                            <td class="id">{{ $document->number }}</td>
                                                            <td class="duedate" data-order="{{ DateTime::createFromFormat('d/m/Y', $document->date_due_formatted)->getTimestamp() }}">{{ $document->date_due_formatted }}</td>
                                                            <td class="status" data-datatable-tag="status">
                                                                <div class="badge {{ $document->_view['status'] == 'Overdue' ? 'danger' : 'primary' }}">
                                                                    {{ _t($document->_view['status']) }}
                                                                </div>
                                                            </td>
                                                            <td class="document">
                                                                <span>{{ _t('INVOICE') }}</span>
                                                            </td>
                                                            <td class="amount" data-order="{{ $document->amount_outstanding }}">{{ $document->amount_outstanding_formatted }}</td>
                                                            <td class="remove"><i class="icon-arclight icon-deleteforever remove"></i></td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- SUMMARY - PAY WITH  --}}
                                    <div class="summary-pay-with pt-2">
                                        {{-- MINI PANEL HEADER - PAY WITH --}}
                                        <div class="panel-header mini" data-toggle="collapse" data-target="#panel-confirmation-pay-with" aria-expanded="false" aria-controls="panel-confirmation-pay-with">
                                            <div class="panel-title">
                                                {{ _t('PAYMENT WITH', 'UPPERWORDS') }}
                                            </div>
                                            <div class="indicator">
                                                <i class="icon-arclight icon-arrowdropup"></i>
                                            </div>
                                        </div>
                                        {{-- MINI PANEL BODY - PAY WITH --}}
                                        <div class="panel-body collapse show" id="panel-confirmation-pay-with">
                                            <div class="responsive-table">
                                                <table id="table-summary-pay-with" class="table has-filter">
                                                    <thead>
                                                        <tr>
                                                            <th class="id" data-datatable-type="text">{{ _t('ID', 'UPPER') }}</th>
                                                            <th class="duedate" data-datatable-type="date">{{ _t('DATE') }}</th>
                                                            <th class="document" data-datatable-type="">{{ _t('DOCUMENT') }}</th>
                                                            <th class="amount" data-datatable-type="">{{ _t('UNAPPLIED') }}</th>
                                                            <th class="remove" data-datatable-type=""></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($documents['credit_note'] as $document)
                                                        <tr style="display: none;"
                                                            data-target="#modal-credit_note-details"
                                                            data-target-json="{{ $document->_view['_json'] }}"
                                                            data-document-number-show="{{ $document->number }}">
                                                            <td class="id">{{ $document->number }}</td>
                                                            <td class="duedate" data-order="{{ DateTime::createFromFormat('d/m/Y', $document->_view['date'])->getTimestamp() }}">{{ $document->_view['date'] }} </td>
                                                            <td class="document">
                                                                <span>{{ _t('CREDIT NOTE', 'UPPERWORDS') }}</span>
                                                            </td>
                                                            <td class="amount" data-order="{{ $document->amount_unapplied }}">{{ $document->amount_unapplied_formatted }} </td>
                                                            <td class="remove"><i class="icon-arclight icon-deleteforever remove"></i></td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 pl-0">
                                    <div class="panel-summary payment-methods">
                                        {{-- HEADER --}}
                                        <div class="panel-summary-header">
                                            <div class="title">
                                                {{ _t('Payment Method', 'UPPERWORDS') }}
                                            </div>
                                        </div>

                                        @include('common.payment_methods.payment_methods')

                                        {{-- PAYMENT SUMMARY --}}
                                        <div class="payment-summary">
                                            <div class="title">
                                                {{ _t('PAYMENT SUMMARY', 'UPPERWORDS') }}
                                            </div>

                                            <div class="summary-subtotal">
                                                <div>{{ _t('SUBTOTAL') }}</div>
                                                <div class="amount">
                                                    {{ LocaleUtility::getCurrencySymbol() }} <span data-document-payment-amount>0.00</span>
                                                </div>
                                            </div>

                                            <div class="summary-platform-fee">
                                                <div>{{ _t('Platform Fee', 'UPPERWORDS') }}</div>
                                                <div class="amount">
                                                    {{ LocaleUtility::getCurrencySymbol() }} <span data-document-payment-fee-amount>0.00</span>
                                                    <input type="hidden" name="payment[amount_surcharge]" value="0">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- FOOTER --}}
                                        <div class="panel-summary-footer">
                                            <div class="title">
                                                {{ _t('Total Payment', 'UPPERWORDS') }}
                                            </div>
                                            <div class="amount">
                                                {{ LocaleUtility::getCurrencySymbol() }} <span data-document-payment-total-amount>0.00</span>
                                            </div>
                                        </div>

                                        <div class="panel-button">
                                            <button id="payment-form-submit" type="submit" class="btn-arclight rounded success" disabled>
                                                {{ _t('PAY', 'UPPER') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- SUMMARY --}}
        <div class="row summary-float">
            {{-- SELECTED INVOICE --}}
            <div class="col-md-4 pill">
                <div class="title">
                    {{ _t('SELECTED INVOICE', 'UPPERWORDS') }}
                </div>
                <div class="amount">
                    {{ LocaleUtility::getCurrencySymbol() }} <span data-document-invoice-amount>0.00</span>
                </div>
            </div>

            {{-- SELECTED CREDIT NOTE --}}
            <div class="col-md-4 pill">
                <div class="title">
                    {{ _t('SELECTED CREDIT NOTE', 'UPPERWORDS') }}
                </div>
                <div class="amount">
                    {{ LocaleUtility::getCurrencySymbol() }} <span data-document-credit_note-amount>0.00</span>
                </div>
            </div>

            {{-- TOTAL PAYMENT --}}
            <div class="col-md-4 pill">
                <div class="title">
                    {{ _t('TOTAL PAYMENT NEEDED', 'UPPERWORDS') }}
                </div>
                <div class="amount">
                    {{ LocaleUtility::getCurrencySymbol() }} <span data-document-payment-amount>0.00</span>
                </div>
                <div class="btn-expand">
                    <i class="icon-arclight icon-expandless"></i>
                </div>
            </div>
        </div>
    {{ Form::close() }}
</div>
@endsection

@push('script')
@include('common.js-utilities')
@include('common.js-modals')
@include('common.js-datatable')
@include('common.payment_methods.js-payment_methods')
@include('modules.payment_method.grabfinance')
@include('modules.payment_method.rms')
@include('modules.payment_method.rms_credit_card')
@include('modules.payment_method.rms_fpx')
@include('modules.payment_method.duitnow')
<script>
    $(document).ready(function() {
        setTimeout(
            function()
            {
                @if ($userPaymentData['method'])
                    $('[data-payment-method="{{ $userPaymentData['method'] }}"]').click();
                @endif

                $('[data-document-is_selected="1"]').click().change();
                $('#payment-amount').val("{{ $userPaymentData['amount'] }}").change();

                @if (! $userPaymentData['is_editable'])
                    $('#table-pay-for .checkbox input[type="checkbox"], #table-pay-with .checkbox input[type="checkbox"]').prop('disabled', true);
                    $('[data-document-number-show] td.remove i').remove();
                    $('#payment-amount').prop('readonly', true);
                @endif
            },
            25
        );

        $('#table-pay-for').DataTable({
            'fixedHeader': true,
            'paging': false,
            'bInfo': false,
            'order': [
                [2, 'asc'],
                [1, 'asc']
            ],
            'columnDefs': [{
                'orderable': false,
                'targets': [0, 4]
            }]
        });

        $('#table-pay-with').DataTable({
            'fixedHeader': true,
            'paging': false,
            'bInfo': false,
            'order': [
                [2, 'asc'],
                [1, 'asc']
            ],
            'columnDefs': [{
                'orderable': false,
                'targets': [0, 3]
            }]
        });

        $('#table-summary-pay-for').DataTable({
            'fixedHeader': true,
            'paging': false,
            'bInfo': false,
            'columnDefs': [{
                'orderable': false,
                'targets': [3, 5]
            }]
        });

        $('#table-summary-pay-with').DataTable({
            'fixedHeader': true,
            'paging': false,
            'bInfo': false,
            'columnDefs': [{
                'orderable': false,
                'targets': [2, 4]
            }]
        });

        $('.btn-expand').click(function()
        {
            if ($('.summary-float').hasClass('expand')) {
                $('.summary-float').removeClass('expand');
            }
            else {
                $('.summary-float').addClass('expand');
            }
        });

        // Hide or show textarea based on document notes availability
        $('.modal-remarks textarea').text() == '' ? $('.modal-remarks').hide() : $('.modal-remarks').show();
    });
</script>
<script>
    $(document).ready(function()
    {
        var documentInvoiceAmount    = 0;
        var documentCreditNoteAmount = 0;
        var documentPaymentFeeAmount = 0;

        $('#table-pay-for td > input[type="checkbox"]').change(function()
        {
            $(this).prop('checked')
                ? $('[data-document-number-show="' + $(this).attr('data-document-number') + '"]').show()
                : $('[data-document-number-show="' + $(this).attr('data-document-number') + '"]').hide()

            documentInvoiceAmount = 0;

            $('#table-pay-for td > input:checked').each(function()
            {
                documentInvoiceAmount += parseFloat($(this).attr('data-document-amount'));
            });

            $('[data-document-invoice-amount]').text(numberUtility().getCurrencyFormatted(documentInvoiceAmount));
            $('#body-pay-for div.panel-summary span.amount').text($('#table-pay-for input:checked[data-document-number]').length);

            updateDocumentPaymentAmount();
        });

        $('#table-pay-with td > input[type="checkbox"]').change(function()
        {
            $(this).prop('checked')
                ? $('[data-document-number-show="' + $(this).attr('data-document-number') + '"]').show()
                : $('[data-document-number-show="' + $(this).attr('data-document-number') + '"]').hide()

            documentCreditNoteAmount = 0;

            $('#table-pay-with td > input:checked').each(function()
            {
                documentCreditNoteAmount += parseFloat($(this).attr('data-document-amount'));
            });

            $('[data-document-credit_note-amount]').text(numberUtility().getCurrencyFormatted(documentCreditNoteAmount));
            $('#body-pay-with div.panel-summary span.amount').text($('#table-pay-with input:checked[data-document-number]').length);

            updateDocumentPaymentAmount();
        });

        $('[data-document-number-show] td.remove').click(function()
        {
            $('input[type="checkbox"][data-document-number="' + $(this).closest('tr').attr('data-document-number-show') + '"]').click();
        });

        var modalSelectors = [
            '#table-pay-for tbody tr td:not(.checkbox)',
            '#table-pay-with tbody tr td:not(.checkbox)',
            '#table-summary-pay-for tr td:not(.remove)',
            '#table-summary-pay-with tr td:not(.remove)',
            'div.panel-summary-listing > div'
        ];

        $(modalSelectors.join(',')).on('click', function()
        {
            $($(this).closest('[data-target]').attr('data-target')).modal();
        });

        // Payment methods
        $('#body-pay-confirmation .payment-method').click(function()
        {
            $('input[name="payment\\[method\\]"]').val($(this).attr('data-payment-method')).change();

            $('#body-pay-confirmation .payment-method').removeClass('active');

            $(this).addClass('active');

            if ($(this).attr('data-payment-method-credit_notes'))
            {
                $('#body-pay-with').find('input[type="checkbox"]').prop('disabled', false);
            }
            else
            {
                var documentPaymentAmount = $('#payment-amount').val(); // Save

                $('#body-pay-with').find('input[type="checkbox"]')
                    .prop('disabled', true)
                    .prop('checked', false).change();

                $('#payment-amount').val(documentPaymentAmount).change(); // Reload
            }

            updateDocumentPaymentAmount(false);
        });

        $('#payment-amount').on('input', function()
        {
            $('#payment-amount').change();
        });

        $('#body-pay-confirmation .payment-method').first().click();

        // Payment submission
        $('form').submit(function(submitEvent)
        {
            submitEvent.preventDefault();

            if ($('input[name="payment[method]"]').val() == 'grabfinance' && (! checkGfinCookieExists() || getGfinCookie() != userId))
            {
                $('#modal-login').modal('show');

                return;
            }

            $('body').toggleClass('loading');

            var paymentData = {};

            $(this).serializeArray().forEach(function(formField)
            {
                paymentData[formField.name] = formField.value;
            });

            $.ajax(
            {
                'type': 'POST',
                'url': "{{ RouteUtility::getRoute('payment.create') }}",
                'data': paymentData,
                'dataType': 'json',
                'success': function(paymentResponse)
                {
                    $('body').toggleClass('loading');

                    if (paymentResponse._url)
                    {
                        $('body').toggleClass('loading');

                        window.location = paymentResponse._url;
                    }
                    else
                    {
                        $('body').trigger('payment-method.request.' + $('[name="payment\\[method\\]"]').val(), [paymentResponse.documentId]);
                    }
                }
            });
        });

        // Magic Link
        var userId = {{ app('User')->getId() }};

        $('#btn-Login').click(function()
        {
            let login_form_inputs = $('#modal-login form input');

            if (login_form_inputs[0].value == '' || login_form_inputs[1].value == '')
            {
                alert('Please input your email and password');

                return;
            }

            $.ajax(
            {
                'type': 'POST',
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                'url': '{{ RouteUtility::getRoute('payment.grabfinance.login') }}',
                'data': {
                    'user': {
                        'email_address': login_form_inputs[0].value,
                        'password': login_form_inputs[1].value
                    }
                },
                'dataType': 'json',
                'success': function(response)
                {
                    if (response.status)
                    {
                        $('#modal-login').modal('hide');
                        alert('Login is successful!');

                        setCookie(response.userId, 1);
                    }
                    else
                    {
                        alert('Incorrect email or password!');
                    }
                }
            });
        });

        $('#btn-magic-link').click(function()
        {
            $.ajax(
            {
                'type': 'POST',
                'headers': {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                'url': '{{ RouteUtility::getRoute('payment.grabfinance.send_magic_link') }}',
                'dataType': 'json',
                'success': function(response)
                {
                    $('#modal-check-mail').modal('show');
                    $('#modal-login').modal('hide');

                    window.Echo.channel(response.channel_name)
                        .listen('ActivateMagicLink', function(e)
                        {
                            alert('Login from magic link is successful!');
                            Echo.leaveChannel(response.channel_name);

                            setCookie(response.userId, 1);
                        });
                }
            });
        });

        function updateDocumentPaymentAmount(toUpdateDocumentPaymentAmountInput = true)
        {
            var documentPaymentAmount = Math.max(documentInvoiceAmount - documentCreditNoteAmount, 0);

            $('[data-document-payment-amount]').text(numberUtility().getCurrencyFormatted(documentPaymentAmount));
            $('[data-document-payment-total-amount]').text(numberUtility().getCurrencyFormatted(documentPaymentAmount + documentPaymentFeeAmount));

            if (toUpdateDocumentPaymentAmountInput || ! $('#payment-amount').val())
            {
                $('#payment-amount').val(documentPaymentAmount.toFixed(2)).change();
            }

            $('#payment-form-submit').prop('disabled', ! parseFloat($('#payment-amount').val()));
        }

        function checkGfinCookieExists()
        {
            let name = 'gfin_login_cookie=';
            let cookie_name = document.cookie.split(';');

            for(let i = 0; i < cookie_name.length; i++)
            {
                let c = cookie_name[i];

                while (c.charAt(0) == ' ')
                {
                    c = c.substring(1);
                }

                if (c.indexOf(name) == 0)
                {
                    return true;
                }
            }

            return false;
        }

        function setCookie(userId, days)
        {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));

            let expires = "expires=" + d.toUTCString();
            document.cookie = `gfin_login_cookie=${userId};${expires};path=/`;
        }

        function getGfinCookie()
        {
            let name = 'gfin_login_cookie=';
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');

            for (let i = 0; i < ca.length; i++)
            {
                let c = ca[i];

                while (c.charAt(0) == ' ')
                {
                    c = c.substring(1);
                }

                if (c.indexOf(name) == 0)
                {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }
    });
</script>
@endpush
