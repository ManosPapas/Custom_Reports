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
			<tbody style="display: block; border: 1px solid black; height: 600px; width:350px; overflow-y: scroll"></tbody>
		</table>
	</div>

	<div class="col-sm">
		<table class="table table-striped" id="table-relationships">
            <thead>
                <tr>
                    <td>
                        <span>{{ __('app.from') }}</span>
                        <select class="from_tables">
                            @include('custom_reports.partials.add_join_tables', ['tables' => []])
                        </select>
                        <input type="button" id="add_relationship" value="{{ __('app.add_relationship') }}"/>
                    </td>
                </tr>                    
            </thead>
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll"></tbody>
		</table>
	</div>

	<div class="col-sm">
		<table class="table table-striped" id="where-relationships">
            <thead>
                <tr>
                    <td>
                        <input type="button" id="add_where_clause" value="{{ __('app.add') }}"/>
                        <span>{{ __('app.where') }}</span>
                    </td>
                </tr>                  
            </thead>
			<tbody style="display: block; border: 1px solid black; height: 600px; width:350px; overflow-y: scroll"></tbody>
		</table>
	</div>

	<div class="col-sm">
		<button id="save" class="btn btn-warning">{{ __('app.save') }}</button>
		<button type="submit" id="execute" class="btn btn-success">{{ __('app.execute') }}</button>
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
                    //CustomExport.select_actions(response['action_columns']); //Not working for the moment
                    $(".join_tables option").remove();
                    $('.join_tables').append(response['html_join_tables']);

                    $(".from_tables option").remove();
                    $('.from_tables').append(response['html_join_tables']);

                    CustomExport.fill_where_columns();

                    $(".where_columns option").remove();
                    $('.where_columns').append(response['html_where_columns']);
                },
                error: function()
                { 
                   console.log('Something went wrong with the main process?!');
                }
            });
        }
        else {
        	$("#selected-table-columns tbody>tr").remove();
             $("#where-relationships tbody>tr").remove();
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
                    $(".join_columns_select option").remove();
                    $('.join_columns_select').append(response);                                 
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

    $('#add_relationship').click(function(e)
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

    $('#where-relationships').on('click', '.remove_where_clause', function()
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

    $('#add_where_clause').click(function(e)
    {    
        tables = CustomExport.get_checkboxes_status(".selected-table:input:checkbox")[0];

        if(tables.length > 0) {
            $.ajax({
                url: '/custom-reports/generate-where-clause',
                method: 'GET',
                data: {
                    tables: tables
                },
                success: function(response)
                {
                    $('#where-relationships').append(response);
                },
                error: function()
                { 
                   console.log('Something went wrong!');
                }
            });            
        }
        else {
            $("#where-relationships tbody>tr").remove();
        }
    });

    $('#save').click(function(e)
    {
        // $.ajax({
        //     url: '/custom-reports/create',
        //     method: 'POST',
        //     data: {
                
        //     },
        //     success: function(response)
        //     {
                
        //     },
        //     error: function()
        //     { 
        //        console.log('Something went wrong!');
        //     }
        // });

    });

    $('#execute').click(function(e)
    {
        // take tables, columns, joins, where ->build
        columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];
        from_table = $('.from_tables')[0].value;
        actions = [];
        relationships = [];
        relationship_tables = [];
        relationship_columns = [];
        relationship_operators = [];
        where_columns = [];
        where_operators = [];
        where_input = [];
        all_relationships = $('.relationships');
        all_relationship_tables = $('.join_tables');
        all_relationship_operators = $('.relationships_operators');
        all_relationship_columns = $('.join_columns_select');
        all_where_columns = $('.where_columns');
        all_where_operators = $('.where_operators');
        all_where_input = $('.where_input');

        for (var i = 0; i < columns.length; i++) {
            element = document.getElementById("select_"+columns[i]);
            action = element.options[element.selectedIndex].value;
            actions.push(action);
        }

        if(all_relationships.length > 0) {
            for (var i = 0; i < all_relationships.length; i++) {
                relationships.push(all_relationships[i].value);
                relationship_tables.push(all_relationship_tables[i].value);
                relationship_operators.push(all_relationship_operators[i].value);
            }

            for (var i = 0; i < all_relationship_columns.length; i++) {
                relationship_columns.push(all_relationship_columns[i].value);
            }
        }

        if(all_where_columns.length > 0) {
            for (var i = 0; i < all_where_columns.length; i++) {
                where_columns.push(all_where_columns[i].value);
                where_operators.push(all_where_operators[i].value);
                where_input.push(all_where_input[i].value);
            }
        }
        
        $.ajax({
            url: '/custom-reports/save',
            method: 'POST',
            data: {
                actions: actions,
                select_columns: columns,
                from_table: from_table,
                relationship_tables: relationship_tables,
                relationships: relationships,
                relationship_operators: relationship_operators,
                relationship_columns: relationship_columns,
                where_columns: where_columns,
                where_operators: where_operators,
                where_input: where_input,
                _token: '{{csrf_token()}}'
            },
            success: function(response)
            {
                console.log(response);
            },
            error: function()
            { 
               console.log('Something went wrong!');
            }
        });

    });
});

</script>
@endsection