{{-- <span>{{ __('from') }}</span> --}}

<tr>
	<td>
		<select>
			@foreach($tables as $table)
				<option value="{{ $table }}">{{ $table }}</option>
			@endforeach
		</select>

		<select>
			@foreach($relations as $relation)
				<option value="{{ $relation }}">{{ $relation }}</option>
			@endforeach
		</select>

		<select>
			@foreach($columns as $column)
				<option value="{{ $column }}">{{ $column }}</option>
			@endforeach
		</select>

		<select>
			@foreach($operators as $operator)
				<option value="{{ $operator }}">{{ $operator }}</option>
			@endforeach
		</select>

		<select>
			@foreach($columns as $column)
				<option value="{{ $column }}">{{ $column }}</option>
			@endforeach
		</select>
	</td>
</tr>
