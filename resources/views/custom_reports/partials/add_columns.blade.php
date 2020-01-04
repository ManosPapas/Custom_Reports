@foreach ($responses as $column)	
	<tr>
		<td>
			<div class='custom-control custom-checkbox'>
				<input type='checkbox' class='custom-control-input tables-columns' id='{{ $column }}'>
				<label class='custom-control-label' for='{{ $column }}'>{{ $column }}</label>
			</div>
		</td>
	</tr>	
@endforeach