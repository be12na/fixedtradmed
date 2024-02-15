<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-container"></div>
        <div class="row" style="--bs-gutter-y: 0.5rem;">
            @if (!empty($dataStructureUpline))
            <div class="col-12">
                <label class="d-block required">{{ $dataStructureUpline->name }}</label>
                <select name="int_upline_id" id="int_upline_id" class="form-select">
                    @php $currentUplineId = optional($data)->int_upline_id; @endphp
                    <option value="0">-- Pilih {{ $dataStructureUpline->name }} --</option>
                    @foreach ($usersUpline as $upline)
                        <option value="{{ $upline->id }}" @optionSelected($upline->id, $currentUplineId)>{{ $upline->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if (($requireBranch === true) && ($requireManagerType === true))
            <div class="col-sm-6">
                <label class="d-block required">Cabang Usaha</label>
                <select name="branch_id" id="branch_id" class="form-select">
                    @php 
                        $currenctBranch = optional($data)->branch;
                        $currentBranchId = $currenctBranch ? $currenctBranch->id : null;
                    @endphp
                    <option value="0">-- Pilih Cabang Usaha --</option>
                    @foreach ($branchList as $branch)
                        <option value="{{ $branch->id }}" @optionSelected($branch->id, $currentBranchId)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6">
                <label class="d-block required">Jenis Menager</label>
                <select name="manager_type" id="manager_type" class="form-select">
                    @php 
                        $currentMgrType = optional($data)->manager_type;
                    @endphp
                    @foreach (USER_BRANCH_MANAGER_TYPES as $typeId => $typeName)
                        <option value="{{ $typeId }}" @optionSelected($typeId, $currentMgrType)>{{ $typeName ?? '--- Pilih Jenis Manager ---' }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-6">
                <label class="d-block required">Username</label>
                <input type="text" class="form-control" name="username" id="username" value="{{ optional($data)->username }}" placeholder="Username" autocomplete="off">
            </div>
            <div class="col-sm-6">
                <label class="d-block required">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="{{ optional($data)->email }}" placeholder="Email" autocomplete="off">
            </div>
            <div class="col-12">
                <label class="d-block required">Nama</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ optional($data)->name }}" placeholder="Nama" autocomplete="off">
            </div>
            <div class="col-sm-6">
                <label class="d-block required">Handphone</label>
                <input type="text" class="form-control" name="phone" id="phone" value="{{ optional($data)->phone }}" placeholder="Handphone" autocomplete="off">
            </div>
            <div class="col-12">
                <div class="row" style="--bs-gutter-y: 0.5rem;">
                    <div class="col-sm-6">
                        <label class="d-block @if(!$isEdit) required @endif">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off">
                    </div>
                    <div class="col-sm-6">
                        <label class="d-block @if(!$isEdit) required @endif">Ketik Ulang Password</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Ketik Ulang Password" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer py-1 px-3">
        <button type="button" class="btn btn-sm btn-primary" onclick="submitForm('#myForm', '#alert-container')">
            <i class="fa-solid fa-save me-1"></i>
            Simpan
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>