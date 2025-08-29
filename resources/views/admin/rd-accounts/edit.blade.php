@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit RD Account</h5>
                    <a href="{{ route('admin.rd-accounts.show', $rdAccount) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.rd-accounts.update', $rdAccount) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="account_number" name="account_number"
                                    maxlength="12" value="{{ old('account_number', $rdAccount->account_number) }}" required>
                                @error('account_number')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select class="form-control" id="customer_id" name="customer_id" required>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id', $rdAccount->customer_id) == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="monthly_amount" class="form-label">Monthly Amount</label>
                                <input type="number" step="0.01" class="form-control" id="monthly_amount"
                                    name="monthly_amount" value="{{ old('monthly_amount', $rdAccount->monthly_amount) }}"
                                    required>
                                @error('monthly_amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="start_date" class="form-label">Opening Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ old('start_date', $rdAccount->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="duration_months" class="form-label">Duration (Months)</label>
                                <input type="number" class="form-control" id="duration_months" name="duration_months"
                                    value="{{ old('duration_months', $rdAccount->duration_months) }}" required>
                                @error('duration_months')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                <input type="number" step="0.01" class="form-control" id="interest_rate"
                                    name="interest_rate" value="{{ old('interest_rate', $rdAccount->interest_rate) }}"
                                    required>
                                @error('interest_rate')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active"
                                        {{ old('status', $rdAccount->status) == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="matured"
                                        {{ old('status', $rdAccount->status) == 'matured' ? 'selected' : '' }}>Matured
                                    </option>
                                    <option value="closed"
                                        {{ old('status', $rdAccount->status) == 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_joint_account"
                                        name="is_joint_account" value="1"
                                        {{ old('is_joint_account', $rdAccount->is_joint_account) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_joint_account">Joint Account</label>
                                </div>
                            </div>

                            <div class="mb-3 {{ old('is_joint_account', $rdAccount->is_joint_account) ? '' : 'd-none' }}"
                                id="joint_holder_name_container">
                                <label for="joint_holder_name" class="form-label">Joint Holder Name</label>
                                <input type="text" class="form-control" id="joint_holder_name" name="joint_holder_name"
                                    value="{{ old('joint_holder_name', $rdAccount->joint_holder_name) }}">
                                @error('joint_holder_name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">Notes</label>
                                <textarea class="form-control" id="note" name="note" rows="3">{{ old('note', $rdAccount->note) }}</textarea>
                                @error('note')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const isJointAccount = document.getElementById('is_joint_account');
                const jointHolderContainer = document.getElementById('joint_holder_name_container');
                const jointHolderInput = document.getElementById('joint_holder_name');

                isJointAccount.addEventListener('change', function() {
                    if (this.checked) {
                        jointHolderContainer.classList.remove('d-none');
                        jointHolderInput.required = true;
                    } else {
                        jointHolderContainer.classList.add('d-none');
                        jointHolderInput.required = false;
                    }
                });
            });
        </script>
    @endpush
@endsection
