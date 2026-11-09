@php
    $isEdit = $mahasiswa->exists;
    $action = $isEdit
        ? route('admin.mahasiswa.update', $mahasiswa)
        : route('admin.mahasiswa.store');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="nrp">NRP <span class="text-danger">*</span></label>
                <input type="text"
                       id="nrp"
                       name="nrp"
                       value="{{ old('nrp', $mahasiswa->nrp) }}"
                       class="form-control @error('nrp') is-invalid @enderror"
                       placeholder="5025241097"
                       maxlength="10"
                       pattern="\d{10}"
                       required>
                @error('nrp')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text"
                       id="nama"
                       name="nama"
                       value="{{ old('nama', $mahasiswa->nama) }}"
                       class="form-control @error('nama') is-invalid @enderror"
                       maxlength="120"
                       required>
                @error('nama')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email Kampus <span class="text-danger">*</span></label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $mahasiswa->email) }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="nama@mhs.its.ac.id"
                       maxlength="150"
                       required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="departemen_id">Departemen <span class="text-danger">*</span></label>
                <select id="departemen_id"
                        name="departemen_id"
                        class="form-control @error('departemen_id') is-invalid @enderror"
                        required>
                    <option value="">— Pilih Departemen —</option>
                    @foreach ($departemens as $departemen)
                        <option value="{{ $departemen->id }}"
                            @selected((int) old('departemen_id', $mahasiswa->departemen_id) === $departemen->id)>
                            {{ $departemen->label }}
                        </option>
                    @endforeach
                </select>
                @error('departemen_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="angkatan">Angkatan <span class="text-danger">*</span></label>
                <input type="number"
                       id="angkatan"
                       name="angkatan"
                       value="{{ old('angkatan', $mahasiswa->angkatan) }}"
                       class="form-control @error('angkatan') is-invalid @enderror"
                       min="2000"
                       max="{{ (int) date('Y') + 1 }}"
                       required>
                @error('angkatan')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                <select id="jenis_kelamin"
                        name="jenis_kelamin"
                        class="form-control @error('jenis_kelamin') is-invalid @enderror"
                        required>
                    @foreach (\App\Models\Mahasiswa::JENIS_KELAMIN as $kode => $label)
                        <option value="{{ $kode }}"
                            @selected(old('jenis_kelamin', $mahasiswa->jenis_kelamin) === $kode)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('jenis_kelamin')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="ipk">IPK <span class="text-danger">*</span></label>
                <input type="number"
                       id="ipk"
                       name="ipk"
                       step="0.01"
                       min="0"
                       max="4"
                       value="{{ old('ipk', $mahasiswa->ipk) }}"
                       class="form-control @error('ipk') is-invalid @enderror"
                       required>
                @error('ipk')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="is_active">Status <span class="text-danger">*</span></label>
                <select id="is_active"
                        name="is_active"
                        class="form-control @error('is_active') is-invalid @enderror"
                        required>
                    <option value="1" @selected((string) old('is_active', (int) $mahasiswa->is_active) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', (int) $mahasiswa->is_active) === '0')>Cuti / Non-Aktif</option>
                </select>
                @error('is_active')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Mahasiswa' }}
        </button>
    </div>
</form>
