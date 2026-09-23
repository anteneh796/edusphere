@props(['for' => null])

@error($for)
    <div class="form-error"><x-icon name="alert-circle" class="icon-sm" />{{ $message }}</div>
@enderror