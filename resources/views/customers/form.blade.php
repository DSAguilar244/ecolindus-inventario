<div class="mb-3">
    <label class="form-label">Tipo</label>
    <input type="text" name="id_type" class="form-control @error('id_type') is-invalid @enderror" value="{{ old('id_type', $customer->id_type ?? '') }}">
    @error('id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">Opcional. Use '04' para empresas si aplica.</div>
</div>

<div class="mb-3">
    <label class="form-label">Identificación</label>
    <input type="text" id="identification" name="identification" aria-describedby="identification_error" aria-required="true" class="form-control @error('identification') is-invalid @enderror" value="{{ old('identification', $customer->identification ?? '') }}" required>
    @error('identification')<div id="identification_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="first_name" id="first_name" aria-describedby="first_name_error" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $customer->first_name ?? '') }}">
    @error('first_name')<div id="first_name_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Apellido</label>
    <input type="text" name="last_name" id="last_name" aria-describedby="last_name_error" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $customer->last_name ?? '') }}">
    @error('last_name')<div id="last_name_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Teléfono</label>
    <input type="text" name="phone" id="phone" aria-describedby="phone_error" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone ?? '') }}">
    @error('phone')<div id="phone_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" id="email" aria-describedby="email_error" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email ?? '') }}">
    @error('email')<div id="email_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Dirección</label>
    <textarea name="address" id="address" aria-describedby="address_error" class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address ?? '') }}</textarea>
    @error('address')<div id="address_error" class="invalid-feedback">{{ $message }}</div>@enderror
</div>
