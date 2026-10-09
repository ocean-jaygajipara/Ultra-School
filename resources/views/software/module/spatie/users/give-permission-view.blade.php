<div class="form-group col-md-12">
    <label for="formGroupExampleInput" class="d-block mb-2 font-weight-bold">Select Role base user permission</label>

    @php
        $groupedPermissions = collect($permissions)->groupBy('group');
        $checkedPermissionIds = [];
        if (isset($userDefualtPermissions)) {
            $checkedPermissionIds = collect($userDefualtPermissions)->pluck('id')->toArray();
        }
    @endphp

    @foreach ($groupedPermissions as $group => $perms)
        @php
            $groupSlug = Str::slug($group ?: 'other');
        @endphp
        <div class="border rounded p-3 mb-2 group-card">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input group-checkbox" id="group-{{ $groupSlug }}">
                <label class="custom-control-label font-weight-bold"
                    for="group-{{ $groupSlug }}">{{ ucfirst($group ?: 'Other') }}</label>
            </div>
            <div class="row mt-2">
                @foreach ($perms as $i => $perm)
                    @php
                        $permId = is_array($perm) ? $perm['id'] ?? $i : $perm->id ?? $i;
                        $permName = is_array($perm) ? $perm['name'] ?? '' : $perm->name ?? '';
                    @endphp
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input permission-checkbox"
                                id="perm-{{ $permId }}" name="directPermission[]" value="{{ $permName }}"
                                {{ in_array($permId, $checkedPermissionIds) ? 'checked' : '' }}>

                            <label class="custom-control-label"
                                for="perm-{{ $permId }}">{{ $permName }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @error('directPermission')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<script>
    (function() {
        function updateAllGroupCheckboxes() {
            $('.group-card').each(function() {
                var card = $(this);
                var total = card.find('.permission-checkbox').length;
                var checked = card.find('.permission-checkbox:checked').length;
                var groupCb = card.find('.group-checkbox');

                if (total > 0 && checked === total) {
                    groupCb.prop('checked', true).prop('indeterminate', false);
                } else if (checked > 0) {
                    groupCb.prop('checked', false).prop('indeterminate', true);
                } else {
                    groupCb.prop('checked', false).prop('indeterminate', false);
                }
            });
        }

        // Initialize state
        updateAllGroupCheckboxes();
    })();
</script>
