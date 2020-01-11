<tr>
	<td>
		<select class="where_columns">
			@include('custom_reports.partials.add_where_columns', ['columns' => $columns])
		</select>

		<select class="where_operators">
			@foreach($operators as $operator)
				<option value="{{ $operator }}">{{ $operator }}</option>
			@endforeach
		</select>

		<input type="text" class="where_input">

		<input type="button" class="remove_where_clause" value="{{ __('app.remove') }}"/>
	</td>
</tr>