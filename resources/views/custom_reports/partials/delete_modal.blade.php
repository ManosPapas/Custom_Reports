<!-- Modal -->
<div class="modal fade" id="{{ $id }}exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('app.delete_report') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('app.confirm') }} {{ $id }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('app.cancel') }}</button>
                <a href="{{ $link }}" class="btn btn-danger">{{ __('app.delete') }}</a>
            </div>
        </div>
    </div>
</div>