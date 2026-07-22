@csrf
@if(isset($editingTemplate)) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="title">Title</label><input id="title" name="title" class="form-control" value="{{ old('title',$editingTemplate->title ?? '') }}" maxlength="160" required></div>
<div class="col-md-6"><label class="form-label" for="category">Category</label><input id="category" name="category" class="form-control" value="{{ old('category',$editingTemplate->category ?? '') }}" maxlength="100" required></div>
<div class="col-md-8"><label class="form-label" for="description">Description</label><textarea id="description" name="description" class="form-control" rows="3" maxlength="2000" required>{{ old('description',$editingTemplate->description ?? '') }}</textarea></div>
@php
    $iconOptions = [
        'cp-icon cp-icon-document' => 'Document',
        'cp-icon cp-icon-spark' => 'Creative spark',
        'cp-icon cp-icon-templates' => 'Template layout',
        'cp-icon cp-icon-chat' => 'Message',
        'cp-icon cp-icon-image' => 'Image',
        'cp-icon cp-icon-audio' => 'Audio',
        'cp-icon cp-icon-billing' => 'Billing',
        'cp-icon cp-icon-plan' => 'Plan',
    ];
    $rawSelectedIcon = (string) old('icon', $editingTemplate->icon ?? 'cp-icon cp-icon-document');
    $selectedIcon = collect(array_keys($iconOptions))->first(
        fn (string $option): bool => str_contains($rawSelectedIcon, last(explode(' ', $option))),
    ) ?? 'cp-icon cp-icon-document';
@endphp
<div class="col-md-4"><label class="form-label" for="icon">Template icon</label><select id="icon" name="icon" class="form-select" required>@foreach($iconOptions as $value => $label)<option value="{{ $value }}" @selected($selectedIcon === $value)>{{ $label }}</option>@endforeach</select><div class="form-note">Choose the icon that best represents this template.</div></div>
<div class="col-12"><label class="form-label" for="prompt">Prompt template</label><textarea id="prompt" name="prompt" class="form-control font-monospace" rows="6" maxlength="6000" required>{{ old('prompt',$editingTemplate->prompt ?? '') }}</textarea><div class="form-note">Reference inputs with their machine keys, for example <code>{topic}</code>. Use <code>{result_length}</code> for requested length.</div></div>
<div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check"><input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active',$editingTemplate->is_active ?? true))><label class="form-check-label" for="is_active">Active</label></div></div>
<div class="col-12"><div class="d-flex justify-content-between align-items-center"><div><h2 class="h5 mb-1">Input fields</h2><p class="small text-light mb-0">Add one to ten controlled inputs.</p></div><button type="button" class="btn btn-sm btn-secondary" id="add-input-field">Add Field</button></div></div>
<div class="col-12" id="input-fields">
@php
    $existingFields = old('input_fields');
    if ($existingFields === null) {
        $existingFields = isset($editingTemplate)
            ? $editingTemplate->inputFields->map(fn($field) => ['key'=>$field->key,'title'=>$field->title,'description'=>$field->description,'type'=>$field->type,'is_required'=>$field->is_required])->all()
            : [['key'=>'topic','title'=>'Topic','description'=>'Describe the primary topic.','type'=>'textarea','is_required'=>true]];
    }
@endphp
@foreach($existingFields as $index => $field)
<div class="card mb-3 input-field-row"><div class="card-body"><div class="row g-3">
<div class="col-md-3"><label class="form-label">Machine key</label><input name="input_fields[{{ $index }}][key]" class="form-control field-key" value="{{ $field['key'] ?? '' }}" pattern="[a-z][a-z0-9_]*" maxlength="100" required></div>
<div class="col-md-3"><label class="form-label">Label</label><input name="input_fields[{{ $index }}][title]" class="form-control" value="{{ $field['title'] ?? '' }}" maxlength="160" required></div>
<div class="col-md-3"><label class="form-label">Type</label><select name="input_fields[{{ $index }}][type]" class="form-select" required><option value="text" @selected(($field['type'] ?? '') === 'text')>Single line</option><option value="textarea" @selected(($field['type'] ?? 'textarea') === 'textarea')>Multiple lines</option></select></div>
<div class="col-md-3 d-flex align-items-end gap-3"><input type="hidden" name="input_fields[{{ $index }}][is_required]" value="0"><div class="form-check"><input id="required-{{ $index }}" type="checkbox" name="input_fields[{{ $index }}][is_required]" value="1" class="form-check-input" @checked($field['is_required'] ?? true)><label for="required-{{ $index }}" class="form-check-label">Required</label></div><button type="button" class="btn btn-sm btn-danger remove-input-field">Remove</button></div>
<div class="col-12"><label class="form-label">Help text</label><input name="input_fields[{{ $index }}][description]" class="form-control" value="{{ $field['description'] ?? '' }}" maxlength="1000"></div>
</div></div></div>
@endforeach
</div>
<div class="col-12"><button type="submit" class="btn btn-primary">{{ isset($editingTemplate) ? 'Update Template' : 'Create Template' }}</button></div>
</div>
<template id="input-field-template"><div class="card mb-3 input-field-row"><div class="card-body"><div class="row g-3"><div class="col-md-3"><label class="form-label">Machine key</label><input name="input_fields[__INDEX__][key]" class="form-control field-key" pattern="[a-z][a-z0-9_]*" maxlength="100" required></div><div class="col-md-3"><label class="form-label">Label</label><input name="input_fields[__INDEX__][title]" class="form-control" maxlength="160" required></div><div class="col-md-3"><label class="form-label">Type</label><select name="input_fields[__INDEX__][type]" class="form-select"><option value="text">Single line</option><option value="textarea" selected>Multiple lines</option></select></div><div class="col-md-3 d-flex align-items-end gap-3"><input type="hidden" name="input_fields[__INDEX__][is_required]" value="0"><div class="form-check"><input id="required-__INDEX__" type="checkbox" name="input_fields[__INDEX__][is_required]" value="1" class="form-check-input" checked><label for="required-__INDEX__" class="form-check-label">Required</label></div><button type="button" class="btn btn-sm btn-danger remove-input-field">Remove</button></div><div class="col-12"><label class="form-label">Help text</label><input name="input_fields[__INDEX__][description]" class="form-control" maxlength="1000"></div></div></div></div></template>
@push('scripts')
<script nonce="{{ $cspNonce }}">
(() => {
    const container = document.getElementById('input-fields');
    const addButton = document.getElementById('add-input-field');
    const template = document.getElementById('input-field-template');
    let nextIndex = container.querySelectorAll('.input-field-row').length;
    const bindRemove = (button) => button.addEventListener('click', () => {
        if (container.querySelectorAll('.input-field-row').length > 1) button.closest('.input-field-row').remove();
    });
    container.querySelectorAll('.remove-input-field').forEach(bindRemove);
    addButton.addEventListener('click', () => {
        if (container.querySelectorAll('.input-field-row').length >= 10) return;
        const wrapper = document.createElement('div');
        wrapper.append(template.content.cloneNode(true));
        const row = wrapper.firstElementChild;
        row.querySelectorAll('[name],[id],[for]').forEach((element) => {
            ['name','id','for'].forEach((attribute) => {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', String(nextIndex)));
            });
        });
        bindRemove(row.querySelector('.remove-input-field'));
        container.append(row);
        nextIndex++;
    });
})();
</script>
@endpush
