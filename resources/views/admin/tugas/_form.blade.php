@php
    $isEdit = $tugas->exists;
    $action = $isEdit ? route('admin.tugas.update', $tugas) : route('admin.tugas.store');
    $nilaiDeadline = old('deadline', $tugas->deadline?->format('Y-m-d\TH:i'));
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="mahasiswa_id">Mahasiswa <span class="text-danger">*</span></label>
                <select id="mahasiswa_id" name="mahasiswa_id"
                        class="form-control @error('mahasiswa_id') is-invalid @enderror" required>
                    <option value="">— Pilih Mahasiswa —</option>
                    @foreach ($mahasiswas as $mahasiswa)
                        <option value="{{ $mahasiswa->id }}"
                            @selected((int) old('mahasiswa_id', $tugas->mahasiswa_id) === $mahasiswa->id)>
                            {{ $mahasiswa->nrp }} — {{ $mahasiswa->nama }}
                        </option>
                    @endforeach
                </select>
                @error('mahasiswa_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="dosen_id">Dosen Pengampu (opsional)</label>
                <select id="dosen_id" name="dosen_id"
                        class="form-control @error('dosen_id') is-invalid @enderror">
                    <option value="">— Belum ditentukan —</option>
                    @foreach ($dosens as $dosen)
                        <option value="{{ $dosen->id }}"
                            @selected((int) old('dosen_id', $tugas->dosen_id) === $dosen->id)>
                            {{ $dosen->nama }}
                        </option>
                    @endforeach
                </select>
                @error('dosen_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="judul">Judul Tugas <span class="text-danger">*</span></label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $tugas->judul) }}"
                       class="form-control @error('judul') is-invalid @enderror" maxlength="180" required>
                @error('judul')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="mata_kuliah">Mata Kuliah <span class="text-danger">*</span></label>
                <input type="text" id="mata_kuliah" name="mata_kuliah" value="{{ old('mata_kuliah', $tugas->mata_kuliah) }}"
                       class="form-control @error('mata_kuliah') is-invalid @enderror" maxlength="120" required>
                @error('mata_kuliah')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="status">Status <span class="text-danger">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach (\App\Models\Tugas::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $tugas->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="prioritas">Prioritas <span class="text-danger">*</span></label>
                <select id="prioritas" name="prioritas" class="form-control" required>
                    @foreach (\App\Models\Tugas::PRIORITAS as $key => $label)
                        <option value="{{ $key }}" @selected(old('prioritas', $tugas->prioritas) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="deadline">Deadline <span class="text-danger">*</span></label>
                <input type="datetime-local" id="deadline" name="deadline" value="{{ $nilaiDeadline }}"
                       class="form-control @error('deadline') is-invalid @enderror" required>
                @error('deadline')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3"
                          class="form-control @error('deskripsi') is-invalid @enderror"
                          maxlength="2000">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                @error('deskripsi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('admin.tugas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tugas' }}
        </button>
    </div>
</form>
