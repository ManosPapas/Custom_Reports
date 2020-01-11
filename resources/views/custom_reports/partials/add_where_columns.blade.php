@foreach($columns as $column)
	<option class="where_columns_option" value="{{ $column }}">{{ $column }}</option>
@endforeach
