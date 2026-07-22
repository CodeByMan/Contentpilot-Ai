@csrf
@if(isset($plan)) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="name">Plan name</label><input id="name" name="name" class="form-control" value="{{ old('name',$plan->name ?? '') }}" maxlength="100" required></div>
<div class="col-md-6"><label class="form-label" for="price">Monthly price</label><input id="price" type="number" name="price" class="form-control" value="{{ old('price',$plan->price ?? 0) }}" min="0" max="99999999.99" step="0.01" required></div>
<div class="col-md-3"><label class="form-label" for="monthly_word_limit">Word limit</label><input id="monthly_word_limit" type="number" name="monthly_word_limit" class="form-control" value="{{ old('monthly_word_limit',$plan->monthly_word_limit ?? 0) }}" min="0" max="1000000" required></div>
<div class="col-md-3"><label class="form-label" for="templates">Template limit</label><input id="templates" type="number" name="templates" class="form-control" value="{{ old('templates',$plan->templates ?? 1) }}" min="1" max="500" required></div>
<div class="col-md-3"><label class="form-label" for="image_generation_limit">Image limit</label><input id="image_generation_limit" type="number" name="image_generation_limit" class="form-control" value="{{ old('image_generation_limit',$plan->image_generation_limit ?? 0) }}" min="0" max="10000" required></div>
<div class="col-md-3"><label class="form-label" for="audio_generation_limit">Audio limit</label><input id="audio_generation_limit" type="number" name="audio_generation_limit" class="form-control" value="{{ old('audio_generation_limit',$plan->audio_generation_limit ?? 0) }}" min="0" max="10000" required></div>
<div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check"><input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active',$plan->is_active ?? true))><label for="is_active" class="form-check-label">Active and available for selection</label></div></div>
<div class="col-12"><button type="submit" class="btn btn-primary">{{ isset($plan) ? 'Update Plan' : 'Create Plan' }}</button></div>
</div>
