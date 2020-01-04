@extends('app')

@section('content')
<div class="row">
	<div class="col-sm">
	  	<table>
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">
				@foreach ($tables as $table)
					<tr><td>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input selected-table" id="{{ $table }}">
      						<label class="custom-control-label" for="{{ $table }}">{{ $table }}</label>
      					</div>
					</td><tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table id="selected-table-columns">
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">

			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table id="table-relationships">
			<tbody style="display: block; border: 1px solid black; height: 600px; width:250px; overflow-y: scroll">
				<tr>
					<td>
						<input type="button" class="add_relationship" value="{{ __('app.add') }}"/>
						<input type="button" class="remove_relationship" value="{{ __('app.remove') }}"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="col-sm">
		<table id="where-relationships">
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
	<table id="table-results">
		<tbody style="display: block; border: 1px solid black; height: 300px; width:100%; overflow-y: scroll">

		</tbody>
	</table>
</div>
@endsection

@section('extra_scripts')
@parent
<script type="text/javascript">	
$(document).ready(function(){
    $('.custom-control-input').click(function(e){
    	checked_tables = CustomExport.get_checkboxes_status()[0];

    	if(checked_tables.length > 0)
            $.ajax({
                url: '/custom-reports/generate-table-columns',
                method: 'GET',
                data: {
                	tables: checked_tables
                },
                success: function(response)
                {
                	$("#selected-table-columns tr").remove();
                	$('#selected-table-columns').append(response);
                },
                error: function()
                { 
                   console.log('Something went wrong!');
                }
            });
        else 
        	$("#selected-table-columns tr").remove();
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
        	    $("#table-results tr").remove();
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
            	$("#table-results tr").remove();
            	$('#table-results').append("<tr><th>" + errorInfo + "</th></tr>");
                console.log('Something went wrong with the calculation!');
            }
    	});
    });

    $('.add_relationship').click(function(e)
    {
    	columns = CustomExport.get_checkboxes_status(".tables-columns:input:checkbox")[0];
    	tables = CustomExport.get_checkboxes_status(".selected-table:input:checkbox")[0]

		if(columns.length > 0)
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
                   console.log('Something went wrong!');
                }
            });
        else
        	$("#table-relationships tr").remove();
	});

	$('.remove_relationship').click(function(e){
	    $('#table-relationships tr').last().remove();
	});

});

</script>
@endsection