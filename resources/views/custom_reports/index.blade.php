@extends('app')

{{-- Another way would be to remove the first table and replace it with a dropdown menu. The result-table would go next to the sql-editor. --}}

@section('content')
<div class="row">
	<div class="col-md"> 
		<div class="table-responsive">
		    <table class="table table-striped table-bordered dataTable" id="reports_table">
		    	{{-- We can use permissions --}}
		    	@if(true) <div id="remove_buttons"></div> @endif

		        <thead>
		            <tr>
		                <td>#</td>
		                {{-- Missing Translations --}}
		                <td>{{ __('app.name') }}</td>
		                <td>{{ __('app.modified_by') }}</td>
		                <td>{{ __('app.last_run') }}</td>
		                <td><a href="{{ route('custom_reports_create') }}" class="btn btn-success" role="button">{{ __('app.add') }}</a></td>
		                <td>{{ __('app.choose') }}</td>
		            </tr>
		        </thead>

		        <tbody>
		        	@for($i = 0; $i < 10; $i++)
		            @foreach($reports as $key => $report)
		            <tr>
		            	<td>{{ $key + 1 }}</td>
		            	<td>{{ $report->name }}</td>
		            	<td>{{ $report->modified_by }}</td>
		            	<td>{{ $report->last_run }}</td>
		            	<td>
		            		<a href="{{ route('custom_reports_edit', $report->id) }}" class="btn btn-warning" role="button" style="width:40px;">{{ __('edit') }}</a>
							<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#{{ $report->id }}exampleModal">{{ __('delete') }}</button>
		            		@include('custom_reports/partials/delete_modal'  , array('id' => $report->id, 'link' => route('custom_reports_delete', $report->id)))							
		            	</td>
		            	<td><input type="radio" value="{{ $report->name }}" name="report" class="chosen-report" style="vertical-align: middle; margin-left: 50%;"></td>
		            </tr>
		            @endforeach		           
					@endfor
		        </tbody>
		    </table>
		</div>
	</div>

	<div class="col-md"> 
		<div contenteditable="true" id="text-statement" style="background:black; width:100%; overflow-y:scroll; height:480px;"></div>
		{{-- Missing translations --}}
		<form method="POST" style="float:left; padding-right:10px;">@csrf
			<button id="update-statement" type="submit" class="btn btn-warning">{{ __('update') }}</button>
		</form>

		<form method="POST">@csrf
			<button id="execute-statement" type="submit" class="btn btn-success">{{ __('execute') }}</button>
		</form>

		<h2><span id="message"><b></b></span></h2>
	</div>
</div>

<hr>

<div class="row">
	<div class="col-md">
		<div class="table-responsive">
		    <table class="table table-striped table-bordered dataTable" id="results_table">
		    	{{-- We can use permissions --}}
		    	@if(true) <div id="remove_buttons"></div> @endif

		        <thead>
		        	<tr>
		        		<td>#</td>
		        	</tr>
		        </thead>

		        <tbody>
		        	<tr>
		        		<td>1</td>
		        	</tr>
		        </tbody>

		    </table>
		</div>
	</div>
</div>
@endsection


@section('extra_scripts')
@parent
<script type="text/javascript">
$(document).ready(function()
{
	$('.chosen-report').click(function(e)
	{
		$.ajax({
		    url: '/custom-reports/preview-results',
		    type: 'GET',
		    data: 
		    {
	    		name: CustomExport.chosen_report('chosen-report')
	    	},
		    success: function(response) 
		    {
		    	CustomExport.fill_results_table(response, 'results_table')
		    },
	        error: function(errorInfo)
	        { 
	        	console.log("Error: " + errorInfo);
	        }
		});
	});

	$('#update-statement').click(function(e)
	{
	    e.preventDefault();
	    $("#message").removeClass();

		$.ajax({
		    url: '/custom-reports/update-sql-statement',
		    type: 'POST',
		    data: 
		    {
	    		name: CustomExport.chosen_report('chosen-report'),
	    		sql_statement: CustomExport.get_text('text-statement'),
	    		_token: '{{csrf_token()}}'
	    	},
		    success: function(response) 
		    {
		    	$('#message').addClass('badge badge-success');
		    	$('#message').text(response);
		    },
	        error: function(response)
	        { 
	        	// Minor bug with the badge color. It doe not change!
	        	$('#message').addClass('badge badge-danger');
		    	$('#message').text(response);
	        }
		});
	});

	$('#execute-statement').click(function(e)
	{
	    e.preventDefault();
	    
		$.ajax({
		    url: '/custom-reports/export',
		    type: 'POST',
		    data: 
		    {
		    	name: CustomExport.chosen_report('chosen-report'),
	    		sql_statement: CustomExport.get_text('text-statement'),
	    		_token: '{{csrf_token()}}'
	    	},
		    success: function(response) 
		    {
		    	// For the moment just show the result in the console. The result should be an excel file.
		    	console.log(response);
		    	$('.chosen-report').click();
		    },
	        error: function(response)
	        { 
	        	console.log("Error: " + response);
	        }
		});
	});

});
</script>
@endsection
