@extends('backEnd.layouts.master')
@section('title', 'bKash Refund Status')
@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 text-white">bKash Refund Status</h5>
        </div>
        <div class="card-body">
            @if(isset($response))
                <div class="alert alert-info">
                    <pre>{{ json_encode(json_decode($response), JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
            <form action="{{ route('url-refund-status.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Payment ID</label>
                    <input type="text" name="paymentID" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">TrxID</label>
                    <input type="text" name="trxID" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Check Status</button>
            </form>
        </div>
    </div>
</div>
@endsection
