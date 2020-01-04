@foreach ($columns as $column)	
	<tr>
		<td>
			<div class='custom-control custom-checkbox'>
				<input type='checkbox' class='custom-control-input tables-columns' id='{{ $column }}'>

				<select class="action-column" name="actions[]" id='select_{{ $column }}'>
					@foreach($actions as $action)
						<option id='{{ $column }}-{{ $action }}' value="{{ $action }}">{{ $action }}</option>
					@endforeach
				</select>

				<label class='custom-control-label' for='{{ $column }}'>{{ $column }}</label>
			</div>
		</td>
	</tr>	
@endforeach
