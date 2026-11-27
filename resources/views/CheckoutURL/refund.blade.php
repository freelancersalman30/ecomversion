@extends('backEnd.layouts.master')
@section('title', 'bKash Refund')
@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0 text-white">bKash Refund</h5>
        </div>
        <div class="card-body">
            @if(isset($response))
                <div class="alert alert-info">
                    <pre>{{ json_encode(json_decode($response), JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
            <form action="{{ route('url-refund.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Payment ID</label>
                    <input type="text" name="paymentID" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="text" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">TrxID</label>
                    <input type="text" name="trxID" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger">Process Refund</button>
            </form>
        </div>
    </div>
</div>
@endsection
