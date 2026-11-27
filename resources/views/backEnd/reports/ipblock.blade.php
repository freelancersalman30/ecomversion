@extends('backEnd.layouts.master')
@section('title', 'IP Blacklist & Block Manage')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <button type="button" class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#addIpModal">
                        <i class="fe-shield"></i> Block New IP
                    </button>
                </div>
                <h4 class="page-title">🛡️ IP Blacklist & Block Manage</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted font-14 mb-3">
                        Blacklisted IP addresses are prevented from placing orders or spamming your store.
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover nowrap w-100 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:5%">SL</th>
                                    <th style="width:25%">Blocked IP Address</th>
                                    <th style="width:40%">Reason / Note</th>
                                    <th style="width:15%">Date Added</th>
                                    <th style="width:15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $key => $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge bg-danger fs-6">{{ $value->ip_no }}</span>
                                    </td>
                                    <td>{{ $value->reason ?? 'Spam / Fraud Protection' }}</td>
                                    <td>{{ $value->created_at ? $value->created_at->format('d M, Y h:i A') : 'N/A' }}</td>
                                    <td>
                                        <div class="button-list">
                                            <button type="button" class="btn btn-xs btn-primary waves-effect waves-light"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editIpModal{{ $value->id }}"
                                                    title="Edit">
                                                <i class="fe-edit-1"></i>
                                            </button>

                                            <form method="post" action="{{ route('customers.ipblock.destroy') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" value="{{ $value->id }}" name="id">
                                                <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm"
                                                        onclick="return confirm('Are you sure you want to unblock this IP?')"
                                                        title="Unblock / Delete">
                                                    <i class="fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editIpModal{{ $value->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title text-white">Edit Blocked IP</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('customers.ipblock.update') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $value->id }}">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">IP Address <span class="text-danger">*</span></label>
                                                                <input type="text" name="ip_no" class="form-control" value="{{ $value->ip_no }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason / Note</label>
                                                                <textarea name="reason" class="form-control" rows="3">{{ $value->reason }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update IP</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fe-shield-off font-24 mb-2 d-block"></i>
                                        No IP addresses currently blocked.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addIpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">🛡️ Block New IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customers.ipblock.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">IP Address <span class="text-danger">*</span></label>
                        <input type="text" name="ip_no" class="form-control" placeholder="e.g. 103.145.78.2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason / Note</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Why is this IP being blocked? (e.g. Fake Orders, Spamming)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Block IP</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
