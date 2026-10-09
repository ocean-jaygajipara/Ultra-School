<div class="col-md-12">
    @php
        $hiddenGroups = ['country', 'state', 'city', 'pincode', 'document-type', 'documents'];
        $groupedPermissions = collect($permissions)
            ->filter(function ($item) use ($hiddenGroups) {
                $grp = strtolower(is_array($item) ? ($item['group'] ?? '') : ($item->group ?? ''));
                return !in_array($grp, $hiddenGroups);
            })
            ->groupBy('group');

        $checkedPermissionIds = [];
        if (isset($userDefualtPermissions)) {
            $checkedPermissionIds = collect($userDefualtPermissions)->pluck('id')->toArray();
        }
    @endphp

    <style>
        .permission-module-card {
            border: 1px solid #e7e7e8;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 1rem;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .permission-module-card:hover {
            border-color: #7367f0;
            box-shadow: 0 4px 12px rgba(115, 103, 240, 0.08);
        }
        .permission-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #ededee;
            padding: 10px 16px;
            border-top-left-radius: 7px;
            border-top-right-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .permission-item-box {
            border: 1px solid #ececee;
            border-radius: 6px;
            padding: 8px 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease-in-out;
            height: 100%;
        }
        .permission-item-box:hover {
            background-color: #f3f2ff;
            border-color: #7367f0;
        }
        .permission-item-box.checked-item {
            background-color: #f4f3ff;
            border-color: #7367f0;
        }
        .permission-item-box .form-check-input {
            cursor: pointer;
            margin-top: 0;
            width: 1.15em;
            height: 1.15em;
        }
        .permission-item-box .form-check-label {
            cursor: pointer;
            margin-left: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #4b4b4b;
            word-break: break-word;
        }
        .permission-badge-action {
            font-size: 0.72rem;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .perm-action-list { background: #e8f4fd; color: #007bff; }
        .perm-action-create { background: #e8fadf; color: #28a745; }
        .perm-action-edit { background: #fff3e6; color: #ff9f43; }
        .perm-action-delete { background: #fde8e8; color: #ea5455; }
        .perm-action-other { background: #f0e8fd; color: #7367f0; }
    </style>

    <!-- Header Actions Bar -->
    <div class="card mb-3 border-0 bg-light shadow-none">
        <div class="card-body p-3">
            <div class="row align-items-center g-2">
                <div class="col-md-6 col-12">
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="ti ti-shield-lock me-1"></i> Direct Permissions (Role Based)
                    </h5>
                </div>
                <div class="col-md-6 col-12 text-md-end text-start">
                    <input type="text" id="permissionSearchInput" class="form-control form-control-sm d-inline-block w-auto mt-2 mt-md-0" placeholder="Search permission..." style="max-width: 200px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped Permission Cards -->
    <div id="permissionModulesContainer">
        @foreach ($groupedPermissions as $group => $perms)
            @php
                $groupSlug = Str::slug($group ?: 'other');
                $groupTitle = ucfirst(str_replace(['-', '_'], ' ', $group ?: 'Other'));
            @endphp
            <div class="permission-module-card group-card" data-module-name="{{ strtolower($groupTitle) }}">
                <div class="permission-card-header">
                    <div class="d-flex align-items-center">
                        <div class="form-check form-check-primary m-0 me-2">
                            <input type="checkbox" class="form-check-input group-checkbox" id="group-{{ $groupSlug }}">
                        </div>
                        <label class="form-check-label fw-bold fs-6 mb-0 cursor-pointer" for="group-{{ $groupSlug }}">
                            {{ $groupTitle }}
                        </label>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-label-primary group-counter-badge me-2">0 / {{ count($perms) }}</span>
                    </div>
                </div>

                <div class="p-3">
                    <div class="row g-2">
                        @foreach ($perms as $i => $perm)
                            @php
                                $permId = is_array($perm) ? $perm['id'] ?? $i : $perm->id ?? $i;
                                $permName = is_array($perm) ? $perm['name'] ?? '' : $perm->name ?? '';
                                $isChecked = in_array($permId, $checkedPermissionIds);

                                // Determine action badge color
                                $actionClass = 'perm-action-other';
                                if (str_ends_with($permName, '-list') || str_ends_with($permName, '_list')) {
                                    $actionClass = 'perm-action-list';
                                } elseif (str_ends_with($permName, '-create') || str_ends_with($permName, '_create')) {
                                    $actionClass = 'perm-action-create';
                                } elseif (str_ends_with($permName, '-edit') || str_ends_with($permName, '_edit')) {
                                    $actionClass = 'perm-action-edit';
                                } elseif (str_ends_with($permName, '-delete') || str_ends_with($permName, '_delete')) {
                                    $actionClass = 'perm-action-delete';
                                }
                            @endphp
                            <div class="col-lg-3 col-md-4 col-sm-6 perm-col" data-perm-name="{{ strtolower($permName) }}">
                                <div class="permission-item-box {{ $isChecked ? 'checked-item' : '' }}">
                                    <div class="form-check form-check-primary m-0 w-100 d-flex align-items-center">
                                        <input type="checkbox" class="form-check-input permission-checkbox"
                                            id="perm-{{ $permId }}" name="directPermission[]" value="{{ $permName }}"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label flex-grow-1" for="perm-{{ $permId }}">
                                            {{ $permName }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @error('directPermission')
        <span class="invalid-feedback d-block mt-2" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<script>
    (function() {
        function updateSingleGroup(card) {
            var total = card.find('.permission-checkbox').length;
            var checked = card.find('.permission-checkbox:checked').length;
            var groupCb = card.find('.group-checkbox');
            var badge = card.find('.group-counter-badge');

            badge.text(checked + ' / ' + total);

            if (total > 0 && checked === total) {
                groupCb.prop('checked', true).prop('indeterminate', false);
                badge.removeClass('bg-label-secondary bg-label-warning').addClass('bg-label-success');
            } else if (checked > 0) {
                groupCb.prop('checked', false).prop('indeterminate', true);
                badge.removeClass('bg-label-secondary bg-label-success').addClass('bg-label-primary');
            } else {
                groupCb.prop('checked', false).prop('indeterminate', false);
                badge.removeClass('bg-label-success bg-label-primary').addClass('bg-label-secondary');
            }
        }

        function updateAllGroups() {
            $('.group-card').each(function() {
                updateSingleGroup($(this));
            });
        }

        // Initialize state
        updateAllGroups();

        // Search filter
        $('#permissionSearchInput').off('keyup change').on('keyup change', function() {
            var val = $.trim($(this).val()).toLowerCase();
            if (!val) {
                $('.group-card').show();
                $('.perm-col').show();
                return;
            }

            $('.group-card').each(function() {
                var groupCard = $(this);
                var groupName = groupCard.attr('data-module-name') || '';
                var hasMatchingPerm = false;

                groupCard.find('.perm-col').each(function() {
                    var permCol = $(this);
                    var permName = permCol.attr('data-perm-name') || '';
                    if (permName.indexOf(val) > -1 || groupName.indexOf(val) > -1) {
                        permCol.show();
                        hasMatchingPerm = true;
                    } else {
                        permCol.hide();
                    }
                });

                if (hasMatchingPerm) {
                    groupCard.show();
                } else {
                    groupCard.hide();
                }
            });
        });
    })();
</script>
