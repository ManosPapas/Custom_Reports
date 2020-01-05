@extends('app')

@section('content')
<div class="row">
    <div class="col-md">
        <a href="{{ route('custom_reports_index') }}" class="btn btn-primary" role="button">{{ __('app.back') }}</a>
    </div>
</div>

<div class="row">
	<div class="col-sm">
	  	<table class="table table-striped" id="reports_table">
            <thead>
                <tr>
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type='checkbox' id="check-all-tables" class='custom-control-input'>
                            <label class="custom-control-label" for="check-all-tables">{{ __('app.check_all') }}</label>
                        </div>

                        <input type="text" id="input_tables" placeholder="Search tables">
                    </td>
                </tr>
            </thead>
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">
				@foreach ($tables as $table)
					<tr>
                        <td>
    						<div class="custom-control custom-checkbox">
    							<input type="checkbox" class="custom-control-input selected-table" id="{{ $table }}">
          						<label class="custom-control-label" for="{{ $table }}">{{ $table }}</label>
          					</div>
					   </td>
                    </tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table class="table table-striped" id="selected-table-columns">
            <thead>
                <tr>
                    <td>
                        <div class="custom-control custom-checkbox">
                            <input type='checkbox' id="check-all-columns" class='custom-control-input'>
                            <label class="custom-control-label" for="check-all-columns">{{ __('app.check_all') }}</label>
                        </div>

                        <input type="text" id="input_columns" placeholder="Search columns">
                    </td>
                </tr>
            </thead>
			<tbody style="display: block; border: 1px solid black; height: 600px; width:350px; overflow-y: scroll">

			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table class="table table-striped" id="table-relationships">
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">
                <thead>
                    <tr>
                        <td>
                            <input type="button" class="add_relationship" value="{{ __('app.add') }}"/>
                            <span>FROM</span>
                            <select class="join_tables">
                                @include('custom_reports.partials.add_join_tables', ['tables' => []])
                            </select>
                        </td>
                    </tr>                    
                </thead>
			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table class="table table-striped" id="where-relationships">
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">
				<tr>
					<td>
						<input type="button" class="where_relationship" value="{{ __('app.add') }}"/>
						<input type="button" class="where_relationship" value="{{ __('app.delete') }}"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<button id="calculate" class="btn btn-primary">{{ __('app.calculate') }}</button>
		<button id="execute" class="btn btn-primary">{{ __('app.execute') }}</button>
	</div>
</div>

<div class="row">
	<table class="table table-striped" id="table-results">
		<tbody style="display: block; border: 1px solid black; height: 300px; width:100%; overflow-y: scroll">

		</tbody>
	</table>
</div>
@endsection

@section('extra_scripts')
@parent
<script type="text/javascript">	
$(document).ready(function(){

    $('.selected-table').click(function(e)
    {
    	checked_tables = CustomExport.get_checkboxes_status(".selected-table:input:checkbox")[0];
        columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];
        action_columns = CustomExport.get_action_columns_status();

    	if(checked_tables.length > 0) {
            $.ajax({
                url: '/custom-reports/generate-table-columns',
                method: 'GET',
                data: {
                	tables: checked_tables,
                    checked_columns: columns,
                    action_columns: action_columns
                },
                success: function(response)
                {
                	$("#selected-table-columns tbody>tr").remove();
                	$('#selected-table-columns').append(response['html_columns']);
                    CustomExport.click_checkboxes(response['checked_columns']);
                    //CustomExport.select_actions(response['action_columns']);
                    $(".join_tables option").remove();
                    $('.join_tables').append(response['html_join_tables']);                                  
                },
                error: function()
                { 
                   console.log('Something went wrong with the main process?!');
                }
            });
        }
        else {
        	$("#selected-table-columns tbody>tr").remove();
        }
    });

    $('#selected-table-columns').on('click', '.tables-columns', function()
    {
        columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];

        if(columns.length > 0) {
            $.ajax({
                url: '/custom-reports/generate-join-columns',
                method: 'GET',
                data: {
                    checked_columns: columns
                },
                success: function(response)
                {
                    $(".join_columns option").remove();
                    $('.join_columns').append(response);                                 
                },
                error: function()
                { 
                   console.log('Something went wrong!');
                }
            });
        }
        else {
            $(".join_columns option").remove();
        }
    });

    $('#calculate').click(function(e){
    	$.ajax({
    		url: '/custom-reports/calculate',
    		method: 'GET',
    		data: {
    			tables_and_columns: CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0]
    		},
    		success: function(response)
            {
        	    $("#table-results tbody>tr").remove();
        	    size = (response.length > 10)? 10 : response.length;        	    
        	    nof_columns = Object.keys(response[0]).length;
        	    columns_ = Object.keys($.each(response[0], function(key, value) {return key;}));
        	    html_ = "<tr>";

        	    for(i=0; i<columns_.length; i++) html_ += "<th>" + columns_[i] + "</th>";        	    
        	    $('#table-results').append(html_ + "</tr>");

            	for(i=0; i<size; i++)
            	{
					item = response[i];
					html_ = "<tr>";					
					values_ = Object.values($.each(response[i], function(key, value) {return value;}));

					for (j=0; j<nof_columns; j++) 
					{
						html_ += "<td>" + values_[j] + "</td>";
					}

					$('#table-results').append(html_ + "</tr>");
            	}
            },
            error: function(errorInfo)
            { 
            	$("#table-results tbody>tr").remove();
            	$('#table-results').append("<tr><th>" + errorInfo + "</th></tr>");
                console.log('Something went wrong with the calculation!');
            }
    	});
    });

    $('.add_relationship').click(function(e)
    {
    	columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];
    	tables = CustomExport.get_checkboxes_status(".selected-table:input:checkbox")[0];

		if(columns.length > 0){        
            $.ajax({
                url: '/custom-reports/generate-table-relationships',
                method: 'GET',
                data: {
                	columns: columns,
                	tables: tables
                },
                success: function(response)
                {
                	$('#table-relationships').append(response);
                },
                error: function()
                { 
                   console.log('Something went wrong! Probably too many columns?');
                }
            });
        }
        else {
        	$("#table-relationships tbody>tr").remove();
        }
	});

    $('#table-relationships').on('click', '.remove_relationship', function()
    {
	    $(this).closest('tr').remove();
	});

    $('#input_tables').on('change', function() 
    {
        CustomExport.search('input_tables', 'reports_table');
    });

    $('#input_columns').on('change', function() 
    {
        CustomExport.search('input_columns', 'selected-table-columns');
    });

    $('#check-all-tables').on('click', function() 
    {
        CustomExport.check_all('check-all-tables', 'selected-table');
    });

    $('#check-all-columns').on('click', function() 
    {
        CustomExport.check_all('check-all-columns', 'tables-columns');
    });

    

});

</script>
@endsection