<div class="row g-gs"><div class="col-lg-4"><div class="card"><div class="card-body"><h2 class="h5">Generation Brief</h2><form id="generate-content-form" action="{{ $generationAction }}" method="POST">@csrf
@foreach($template->inputFields as $field)<div class="form-group mb-3"><label class="form-label" for="field-{{ $field->id }}">{{ $field->title }}</label>@if($field->type === 'textarea')<textarea id="field-{{ $field->id }}" name="{{ $field->key }}" class="form-control" rows="4" maxlength="{{ config('ai.max_input_field_characters') }}" @required($field->is_required)></textarea>@else<input id="field-{{ $field->id }}" name="{{ $field->key }}" class="form-control" maxlength="{{ config('ai.max_input_field_characters') }}" @required($field->is_required)>@endif@if($field->description)<div class="form-note">{{ $field->description }}</div>@endif</div>@endforeach
<div class="form-group mb-3"><label class="form-label" for="ai_model">AI model</label><select id="ai_model" name="ai_model" class="form-select">@foreach(config('ai.content_models') as $model)<option value="{{ $model }}" @selected($model === config('ai.default_content_model'))>{{ $model }}</option>@endforeach</select></div>
<div class="form-group mb-3"><label class="form-label" for="result_length">Maximum result length</label><input id="result_length" type="number" name="result_length" class="form-control" value="200" min="50" max="{{ config('ai.max_output_words') }}" required></div>
<button id="generate-content-button" type="submit" class="btn btn-primary">Generate and Save</button><p id="generation-status" class="small mt-3" role="status"></p></form></div></div></div>
<div class="col-lg-8"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><div><h2 class="h5 mb-0">Generated Output</h2><span class="small text-light"><span id="word-count">0</span> words · <span id="remaining-words">—</span> remaining</span></div><div><button id="copy-output" type="button" class="btn btn-sm btn-secondary" disabled>Copy</button> <button id="download-output" type="button" class="btn btn-sm btn-secondary" disabled>Download</button></div></div><div class="card-body"><pre id="generated-output" class="text-wrap mb-0 generated-output-panel">Your generated content will appear here.</pre></div></div></div></div>
@push('scripts')
<script nonce="{{ $cspNonce }}">
(() => {
    const form = document.getElementById('generate-content-form');
    const button = document.getElementById('generate-content-button');
    const output = document.getElementById('generated-output');
    const status = document.getElementById('generation-status');
    const copy = document.getElementById('copy-output');
    const download = document.getElementById('download-output');
    let generatedText = '';
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); button.disabled = true; status.textContent = 'Generating securely…';
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const data = await response.json().catch(() => ({message:'The server returned an invalid response.'}));
            if (!response.ok || !data.success) throw new Error(data.message || 'Content generation failed.');
            generatedText = String(data.output || ''); output.textContent = generatedText;
            document.getElementById('word-count').textContent = String(data.word_count ?? 0);
            document.getElementById('remaining-words').textContent = String(data.remaining_words ?? '—');
            copy.disabled = false; download.disabled = false; status.textContent = 'Generated content was saved to the document library.';
        } catch (error) { status.textContent = error instanceof Error ? error.message : 'Content generation failed.'; }
        finally { button.disabled = false; }
    });
    copy.addEventListener('click', async () => { await navigator.clipboard.writeText(generatedText); status.textContent = 'Copied to clipboard.'; });
    download.addEventListener('click', () => { const url = URL.createObjectURL(new Blob([generatedText], {type:'text/plain;charset=utf-8'})); const link=document.createElement('a'); link.href=url; link.download='contentpilot-output.txt'; link.click(); URL.revokeObjectURL(url); });
})();
</script>
@endpush
