@extends('header')

@section('content')

	{!! Former::open($url)
            ->addClass('col-lg-10 col-lg-offset-1 warn-on-exit main-form')
			->autocomplete('off')
            ->method($method)
            ->rules([
                'name' => 'required',
				'client_id' => 'required',
            ]) !!}

    @if ($project)
        {!! Former::populate($project) !!}

        @if ($account->consulting_mode)
            {!! Former::populateField('annual_target_salary', intval($project->annual_target_salary) ? $project->annual_target_salary : '') !!}
            {!! Former::populateField('fee_rate', intval($project->fee_rate) ? $project->fee_rate : '') !!}
            {!! Former::populateField('expense_rate', intval($project->expense_rate) ? $project->expense_rate : '') !!}
        @else
    		{!! Former::populateField('task_rate', floatval($project->task_rate) ? Utils::roundSignificant($project->task_rate) : '') !!}
    		{!! Former::populateField('budgeted_hours', floatval($project->budgeted_hours) ? $project->budgeted_hours : '') !!}
        @endif
    @endif

    <span style="display:none">
        {!! Former::text('public_id') !!}
		{!! Former::text('action') !!}
    </span>

	<div class="row">
        <div class="col-lg-10 col-lg-offset-1">

            <div class="panel panel-default">
            @if ($account->consulting_mode)
            <div class="panel-heading">
                <h3 class="panel-title">{!! trans('texts.details') !!}</h3>
            </div>
            @endif
            <div class="panel-body">

				@if ($project)
					{!! Former::plaintext('client_name')
							->value($project->client ? $project->client->present()->link : '') !!}

                    @if ($account->consulting_mode)
                        {!! Former::plaintext('assoc_client_name')
                                ->value($project->assoc_client ? $project->assoc_client->present()->link : '') !!}
                    @endif
				@else
					{!! Former::select('client_id')
							->addOption('', '')
							->label(trans('texts.client'))
							->addGroupClass('client-select') !!}

                    @if ($account->consulting_mode)
                        {!! Former::select('assoc_client_id')
                                ->addOption('', '')
                                ->label(trans('texts.assoc_client'))
                                ->addGroupClass('client-select') !!}
                    @endif
				@endif


                @if ($account->consulting_mode)
                    {!! Former::text('name')->label(trans('texts.project_id')) !!}

                    {!! Former::text('candidate_position') !!}

                    {!! Former::text('annual_target_salary') !!}

                    {!! Former::text('fee_rate') !!}

                    {!! Former::text('expense_rate') !!}
                @else
                    {!! Former::text('name') !!}

    				{!! Former::text('due_date')
    	                        ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
    	                        ->addGroupClass('due_date')
    	                        ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}

    				{!! Former::text('budgeted_hours') !!}

    				{!! Former::text('task_rate')
    						->placeholder($project && $project->client->task_rate ? $project->client->present()->taskRate : $account->present()->taskRate)
    				 		->help('task_rate_help') !!}
                @endif

				@include('partials/custom_fields', ['entityType' => ENTITY_PROJECT])

				{!! Former::textarea('private_notes')->rows(4) !!}

            </div>
            </div>

            @if ($account->consulting_mode)
            <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{!! trans('texts.warranty_details') !!}</h3>
            </div>
            <div class="panel-body">

                {!! Former::text('candidate_name') !!}

                {!! Former::text('signed_at')
                            ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                            ->addGroupClass('signed_at')
                            ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}

                {!! Former::text('start_of_work')
                            ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                            ->addGroupClass('start_of_work')
                            ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}

                {!! Former::text('warranty_period_until')
                            ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                            ->addGroupClass('warranty_period_until')
                            ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}

            </div>
            </div>
            @endif

        </div>
    </div>

	@if(Auth::user()->canCreateOrEdit(ENTITY_PROJECT))
	<center class="buttons">
        {!! Button::normal(trans('texts.cancel'))->large()->asLinkTo(HTMLUtils::previousUrl('/projects'))->appendIcon(Icon::create('remove-circle')) !!}
        {!! Button::success(trans('texts.save'))->submit()->large()->appendIcon(Icon::create('floppy-disk')) !!}
	</center>
	@endif

	{!! Former::close() !!}

    <script>

		var clients = {!! $clients !!};
		var clientMap = {};

		function submitAction(action) {
            $('#action').val(action);
            $('.main-form').submit();
        }

		function onDeleteClick() {
            sweetConfirm(function() {
                submitAction('delete');
            });
        }

        $(function() {
			var $clientSelect = $('select#client_id');
            var $assocClientSelect = $('select#assoc_client_id');
            for (var i=0; i<clients.length; i++) {
                var client = clients[i];
								clientMap[client.public_id] = client;
                var clientName = getClientDisplayName(client);
                if (!clientName) {
                    continue;
                }
                $clientSelect.append(new Option(clientName, client.public_id));
                if ($assocClientSelect.length) {
                    $assocClientSelect.append(new Option(clientName, client.public_id));
                }
            }
			@if ($clientPublicId)
				$clientSelect.val({{ $clientPublicId }});
			@endif

            @if (isset($assocClientPublicId) && $assocClientPublicId)
                $assocClientSelect.val({{ $assocClientPublicId }});
            @endif

			$clientSelect.combobox({highlighter: comboboxHighlighter}).change(function() {
				var client = clientMap[$('#client_id').val()];
				if (client && parseFloat(client.task_rate)) {
					var rate = client.task_rate;
				} else {
					var rate = {{ $account->present()->taskRate ?: 0 }};
				}
				$('#task_rate').attr('placeholder', roundSignificant(rate, true));
			});

            if ($assocClientSelect.length) {
                $assocClientSelect.combobox({highlighter: comboboxHighlighter})
            }

			$('#due_date').datepicker('update', '{{ $project ? Utils::fromSqlDate($project->due_date) : '' }}');

            $('#signed_at').datepicker('update', '{{ $project ? Utils::fromSqlDate($project->signed_at) : '' }}');

            $('#start_of_work').datepicker('update', '{{ $project ? Utils::fromSqlDate($project->start_of_work) : '' }}');

            $('#warranty_period_until').datepicker('update', '{{ $project ? Utils::fromSqlDate($project->warranty_period_until) : '' }}');

			@if ($clientPublicId)
				$('#name').focus();
			@else
				$('.client-select input.form-control').focus();
			@endif
        });

    </script>

@stop
