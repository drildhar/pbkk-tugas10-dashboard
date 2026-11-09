@php
    $isEdit = $dosen->exists;
    $action = $isEdit ? route('admin.dosen.update', $dosen) : route('admin.dosen.store');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nidn">NIDN <span class="text-danger">*</span></label>
                <input type="text" id="nidn" name="nidn" value="{{ old('nidn', $dosen->nidn) }}"
                       class="form-control @error('nidn') is-invalid @enderror"
                       maxlength="10" pattern="\d{10}" placeholder="0012345678" required>
                @error('nidn')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="nama">Nama Lengkap &amp; Gelar <span class="text-danger">*</span></label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $dosen->nama) }}"
                       class="form-control @error('nama') is-invalid @enderror" maxlength="120" required>
                @error('nama')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $dosen->email) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       maxlength="150" placeholder="nama@its.ac.id" required>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="departemen_id">Departemen <span class="text-danger">*</span></label>
                <select id="departemen_id" name="departemen_id"
                        class="form-control @error('departemen_id') is-invalid @enderror" required>
                    <option value="">— Pilih —</option>
                    @foreach ($departemens as $departemen)
                        <option value="{{ $departemen->id }}"
                            @selected((int) old('departemen_id', $dosen->departemen_id) === $departemen->id)>
                            {{ $departemen->label }}
                        </option>
                    @endforeach
                </select>
                @error('departemen_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="jabatan">Jabatan <span class="text-danger">*</span></label>
                <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $dosen->jabatan) }}"
                       class="form-control @error('jabatan') is-invalid @enderror" maxlength="60" required>
                @error('jabatan')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select id="is_active" name="is_active" class="form-control" required>
                    <option value="1" @selected((string) old('is_active', (int) $dosen->is_active) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', (int) $dosen->is_active) === '0')>Non-Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('admin.dosen.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Dosen' }}
        </button>
    </div>
</form>
