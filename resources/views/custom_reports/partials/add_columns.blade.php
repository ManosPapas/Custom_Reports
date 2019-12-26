@foreach ($responses as $response)	
	<tr>
		<td>
			<div class='custom-control custom-checkbox'>
				<input type='checkbox' class='custom-control-input tables-columns' id='{{ $response }}'>
				<label class='custom-control-label' for='{{ $response }}'>{{ $response }}</label>
			</div>
		</td>
	</tr>	
@endforeach