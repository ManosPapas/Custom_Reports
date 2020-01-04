@foreach($tables as $table)
	<option class="join-tables" value="{{ $table }}">{{ $table }}</option>
@endforeach
