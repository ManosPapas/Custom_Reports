<tr>
	<td>
		<select class="relationships">
			@foreach($relations as $relation)
				<option value="{{ $relation }}">{{ $relation }}</option>
			@endforeach
		</select>

		<select class="join_tables">
			@include('custom_reports.partials.add_join_tables', ['tables' => $tables])
		</select>

		<select class="join_columns_select">
			@include('custom_reports.partials.add_join_columns', ['columns' => $columns])
		</select>

		<select class="relationships_operators">
			@foreach($operators as $operator)
				<option value="{{ $operator }}">{{ $operator }}</option>
			@endforeach
		</select>

		<select class="join_columns_select">
			@include('custom_reports.partials.add_join_columns', ['columns' => $columns])
		</select>

		<input type="button" class="remove_relationship" value="{{ __('app.remove') }}"/>
	</td>
</tr>
